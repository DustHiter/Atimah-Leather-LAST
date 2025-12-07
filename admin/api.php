<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db/config.php';
require_once __DIR__ . '/auth_handler.php';

// Start the session to check for admin status
if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// IMPORTANT: Close the session immediately after use to prevent locking.
// This allows other concurrent requests from the same user to be processed.
session_write_close();

$action = $_GET['action'] ?? '';
$pdo = db();

if ($action === 'get_sales_data') {
    require_once __DIR__ . '/../includes/jdf.php';

    $cache_file = __DIR__ . '/cache/sales_chart.json';
    $cache_lifetime = 3600; // 1 hour

    // Clear PHP's stat cache to ensure we get the most up-to-date file status
    clearstatcache();

    if (file_exists($cache_file) && is_readable($cache_file) && (time() - filemtime($cache_file) < $cache_lifetime)) {
        $cached_data = file_get_contents($cache_file);
        // Verify that the cache content is a valid JSON
        if ($cached_data && json_decode($cached_data) !== null) {
            header('X-Cache: HIT');
            echo $cached_data;
            exit;
        }
    }

    // CACHE MISS: Regenerate the data
    try {
        $stmt = $pdo->prepare("
            SELECT 
                YEAR(created_at) as year, 
                MONTH(created_at) as month, 
                SUM(total_amount) as total_sales
            FROM orders
            WHERE status = 'Delivered'
            GROUP BY year, month
            ORDER BY year ASC, month ASC
        ");
        $stmt->execute();
        $sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $labels = [];
        $data = [];
        foreach ($sales_data as $row) {
            $jalali_date = gregorian_to_jalali($row['year'], $row['month'], 1);
            $labels[] = $jalali_date[0] . '-' . str_pad($jalali_date[1], 2, '0', STR_PAD_LEFT);
            $data[] = (float)$row['total_sales'];
        }
        
        $response_data = json_encode(['labels' => $labels, 'data' => $data]);

        // Atomic Write Operation
        $cache_dir = dirname($cache_file);
        if (!is_dir($cache_dir)) {
            mkdir($cache_dir, 0755, true);
        }
        $temp_file = $cache_file . '.' . uniqid() . '.tmp';
        if (file_put_contents($temp_file, $response_data) !== false) {
            // If rename fails, the old (possibly stale) cache will be used, which is acceptable.
            // The temp file will be cleaned up on subsequent runs or by a cron job.
            rename($temp_file, $cache_file);
        }

        header('X-Cache: MISS');
        echo $response_data;

    } catch (PDOException $e) {
        http_response_code(500);
        error_log("FATAL: DB Exception during sales data generation: " . $e->getMessage());
        echo json_encode(['error' => 'Database error while fetching sales data.']);
    }
    exit;
}

if ($action === 'get_stats') {
    try {
        // Optimized: Fetch all stats in a single query
        $query = "
            SELECT
                (SELECT SUM(total_amount) FROM orders WHERE status = 'Delivered') as total_sales,
                (SELECT COUNT(*) FROM orders WHERE status = 'Shipped') as shipped_orders,
                (SELECT COUNT(*) FROM orders WHERE status = 'Cancelled') as cancelled_orders,
                (SELECT COUNT(*) FROM orders WHERE status = 'Processing') as processing_orders,
                (SELECT COUNT(*) FROM users) as total_users,
                (SELECT COUNT(*) FROM page_views) as total_views,
                (SELECT COUNT(*) FROM page_views WHERE YEAR(view_timestamp) = YEAR(CURDATE()) AND MONTH(view_timestamp) = MONTH(CURDATE())) as this_month_views,
                (SELECT COUNT(*) FROM page_views WHERE YEAR(view_timestamp) = YEAR(CURDATE() - INTERVAL 1 MONTH) AND MONTH(view_timestamp) = MONTH(CURDATE() - INTERVAL 1 MONTH)) as last_month_views
        ";
        
        $stmt = $pdo->query($query);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        $this_month_views = (int)($stats['this_month_views'] ?? 0);
        $last_month_views = (int)($stats['last_month_views'] ?? 0);

        $percentage_change = 0;
        if ($last_month_views > 0) {
            $percentage_change = (($this_month_views - $last_month_views) / $last_month_views) * 100;
        } elseif ($this_month_views > 0) {
            $percentage_change = 100;
        }

        echo json_encode([
            'total_sales' => (float)($stats['total_sales'] ?? 0),
            'shipped_orders' => (int)($stats['shipped_orders'] ?? 0),
            'cancelled_orders' => (int)($stats['cancelled_orders'] ?? 0),
            'processing_orders' => (int)($stats['processing_orders'] ?? 0),
            'total_users' => (int)($stats['total_users'] ?? 0),
            'total_page_views' => [
                'count' => (int)($stats['total_views'] ?? 0),
                'percentage_change' => round($percentage_change, 2)
            ],
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        error_log("API Error (get_stats): " . $e->getMessage());
        echo json_encode(['error' => 'Database error while fetching stats.']);
    }
    exit;
}

if ($action === 'get_reports_data') {
    try {
        // 1. General Stats
        $stats_query = "
            SELECT
                (SELECT SUM(total_amount) FROM orders WHERE status = 'Delivered') as total_revenue,
                (SELECT COUNT(*) FROM orders) as total_orders,
                (SELECT COUNT(*) FROM users WHERE is_admin = 0) as total_users,
                (SELECT COUNT(*) FROM products) as total_products
        ";
        $stats_stmt = $pdo->query($stats_query);
        $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Recent Orders
        $recent_orders_query = "
            SELECT o.id, o.total_amount, o.status, COALESCE(CONCAT(u.first_name, ' ', u.last_name), o.billing_name) AS customer_display_name
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
            LIMIT 5
        ";
        $recent_orders_stmt = $pdo->query($recent_orders_query);
        $recent_orders = $recent_orders_stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Top Selling Products (Calculated in PHP)
        $orders_for_products_query = "SELECT items_json FROM orders WHERE status = 'Delivered'";
        $orders_for_products_stmt = $pdo->query($orders_for_products_query);
        $all_orders_items = $orders_for_products_stmt->fetchAll(PDO::FETCH_ASSOC);

        $product_sales = [];
        foreach ($all_orders_items as $order_items) {
            $items = json_decode($order_items['items_json'], true);
            if (is_array($items)) {
                foreach ($items as $item) {
                    if (isset($item['name']) && isset($item['quantity'])) {
                        $product_name = $item['name'];
                        $quantity = (int)$item['quantity'];
                        if (!isset($product_sales[$product_name])) {
                            $product_sales[$product_name] = 0;
                        }
                        $product_sales[$product_name] += $quantity;
                    }
                }
            }
        }

        arsort($product_sales);
        $top_products = [];
        $count = 0;
        foreach ($product_sales as $name => $total_sold) {
            $top_products[] = ['name' => $name, 'total_sold' => $total_sold];
            $count++;
            if ($count >= 5) break;
        }

        echo json_encode([
            'stats' => [
                'total_revenue' => (float)($stats['total_revenue'] ?? 0),
                'total_orders' => (int)($stats['total_orders'] ?? 0),
                'total_users' => (int)($stats['total_users'] ?? 0),
                'total_products' => (int)($stats['total_products'] ?? 0),
            ],
            'recent_orders' => $recent_orders,
            'top_products' => $top_products
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        error_log("API Error (get_reports_data): " . $e->getMessage());
        echo json_encode(['error' => 'Database error while fetching report data.']);
    }
    exit;
}

if ($action === 'get_monthly_sales') {
    require_once __DIR__ . '/../includes/jdf.php';
    try {
        $stmt = $pdo->prepare("
            SELECT 
                YEAR(created_at) as year, 
                MONTH(created_at) as month, 
                SUM(total_amount) as total_sales
            FROM orders
            WHERE status = 'Delivered'
            GROUP BY year, month
            ORDER BY year ASC, month ASC
        ");
        $stmt->execute();
        $sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $labels = [];
        $values = [];
        $jalali_months = [
            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد',
            4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
            7 => 'مهر', 8 => 'آبان', 9 => 'آذر',
            10 => 'دی', 11 => 'بهمن', 12 => 'اسفند'
        ];

        foreach ($sales_data as $row) {
            $jalali_date = gregorian_to_jalali($row['year'], $row['month'], 1);
            $labels[] = $jalali_months[(int)$jalali_date[1]] . ' ' . $jalali_date[0];
            $values[] = (float)$row['total_sales'];
        }
        
        echo json_encode(['labels' => $labels, 'values' => $values]);

    } catch (PDOException $e) {
        http_response_code(500);
        error_log("API Error (get_monthly_sales): " . $e->getMessage());
        echo json_encode(['error' => 'Database error while fetching monthly sales.']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);
