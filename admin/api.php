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

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);
