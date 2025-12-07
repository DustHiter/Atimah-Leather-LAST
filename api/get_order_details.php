<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/apache2/flatlogic_error.log');

require_once '../db/config.php';
require_once '../includes/jdf.php';

// Function to send JSON error response
function send_error($message) {
    echo json_encode(['error' => $message]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_error('Invalid request method.');
}

$input_data = json_decode(file_get_contents('php://input'), true);

if (!isset($input_data['tracking_id']) || empty($input_data['tracking_id'])) {
    send_error('شناسه رهگیری مشخص نشده است.');
}

$tracking_id = $input_data['tracking_id'];

try {
    $db = db();
    
    // 1. Fetch the order by tracking_id
    $stmt = $db->prepare(
        "SELECT id, billing_name, billing_email, billing_address, billing_city, billing_province, billing_postal_code, total_amount, items_json, created_at, status 
         FROM orders 
         WHERE tracking_id = :tracking_id"
    );
    $stmt->bindParam(':tracking_id', $tracking_id, PDO::PARAM_STR);
    $stmt->execute();
    
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        send_error('سفارشی با این کد رهگیری یافت نشد.');
    }

    // 2. Decode items JSON and fetch product details
    $items_from_db = json_decode($order['items_json'], true);
    $products_response = [];
    $product_ids = [];

    if (is_array($items_from_db)) {
        foreach ($items_from_db as $item) {
            if (isset($item['product_id'])) {
                $product_ids[] = $item['product_id'];
            }
        }
    }

    if (!empty($product_ids)) {
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        // Price is taken from items_json, not the products table, which is correct.
        // The selected color is also in items_json.
        $stmt_products = $db->prepare("SELECT id, name, image_url FROM products WHERE id IN ($placeholders)");
        $stmt_products->execute($product_ids);
        $products_data = $stmt_products->fetchAll(PDO::FETCH_ASSOC);
        $products_by_id = [];
        foreach ($products_data as $product) {
            $products_by_id[$product['id']] = $product;
        }

        foreach ($items_from_db as $item) {
            $product_id = $item['product_id'];
            if (isset($products_by_id[$product_id])) {
                $product = $products_by_id[$product_id];
                $products_response[] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => number_format($item['price']) . ' تومان',
                    'image_url' => $product['image_url'],
                    'quantity' => $item['quantity'],
                    'color' => $item['color'] ?? null // Add the selected color from the order
                ];
            }
        }
    }


    // 3. Format the response
    $status_map = [
        'pending' => 'در انتظار پرداخت',
        'processing' => 'در حال پردازش',
        'shipped' => 'ارسال شده',
        'completed' => 'تکمیل شده',
        'delivered' => 'تحویل شده', // Add mapping for Delivered
        'cancelled' => 'لغو شده',
        'refunded' => 'مسترد شده'
    ];
    $status_persian = $status_map[strtolower($order['status'])] ?? $order['status'];

    // Robust date formatting to prevent errors
    try {
        // Create DateTime object to reliably parse the date from DB
        $date = new DateTime($order['created_at']);
        $timestamp = $date->getTimestamp();
        // Format the timestamp into Jalali date
        $order_date_jalali = jdate('Y/m/d ساعت H:i', $timestamp);
    } catch (Exception $e) {
        // If parsing fails, log the error and return a safe value
        error_log("Jalali date conversion failed for order ID {$order['id']}: " . $e->getMessage());
        $order_date_jalali = 'تاریخ نامعتبر';
    }

    $order_response = [
        'id' => $order['id'],
        'order_date' => $order_date_jalali,
        'total_amount' => number_format($order['total_amount']) . ' تومان',
        'discount_amount' => '0 تومان',
        'status' => $order['status'], // Pass original status to JS for logic
        'status_persian' => $status_persian, // Pass Persian status for display
        'shipping_name' => $order['billing_name'],
        'shipping_address' => trim(implode(', ', array_filter([$order['billing_province'], $order['billing_city'], $order['billing_address']]))),
        'shipping_postal_code' => $order['billing_postal_code']
    ];

    // Final JSON structure
    $response = [
        'success' => true,
        'order' => $order_response,
        'products' => $products_response
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("API Error in get_order_details.php: " . $e->getMessage());
    send_error('خطای سرور: مشکل در ارتباط با پایگاه داده.');
} catch (Exception $e) {
    error_log("API Error in get_order_details.php: " . $e->getMessage());
    send_error('خطای سرور: یک مشکل پیش بینی نشده رخ داد.');
}

?>