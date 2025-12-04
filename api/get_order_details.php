<?php
header('Content-Type: application/json');
require_once '../db/config.php';
require_once '../includes/jdf.php';

$response = ['success' => false, 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $tracking_id = $data['tracking_id'] ?? '';
    $phone = $data['phone'] ?? '';

    if (empty($tracking_id) || empty($phone)) {
        $response['message'] = 'کد رهگیری و شماره تلفن الزامی است.';
        echo json_encode($response);
        exit;
    }

    try {
        $db = db();
        $stmt = $db->prepare(
            "SELECT o.*, CONCAT(u.first_name, ' ', u.last_name) AS full_name, u.email 
             FROM orders o
             JOIN users u ON o.user_id = u.id
             WHERE o.tracking_id = :tracking_id AND o.billing_phone = :phone"
        );
        $stmt->bindParam(':tracking_id', $tracking_id);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            $order_id = $order['id'];
            $products_stmt = $db->prepare(
                "SELECT p.name, p.price, p.image, oi.quantity, oi.color
                 FROM order_items oi
                 JOIN products p ON oi.product_id = p.id
                 WHERE oi.order_id = :order_id"
            );
            $products_stmt->bindParam(':order_id', $order_id);
            $products_stmt->execute();
            $products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format creation date
            $order['created_at_jalali'] = jdate('Y/m/d H:i', strtotime($order['created_at']));
            
            $response['success'] = true;
            $response['message'] = 'سفارش یافت شد.';
            $response['order'] = $order;
            $response['products'] = $products;
        } else {
            $response['message'] = 'سفارشی با این مشخصات یافت نشد.';
        }
    } catch (PDOException $e) {
        error_log("Order tracking PDO error: " . $e->getMessage());
        $response['message'] = 'خطا در برقراری ارتباط با سرور.';
    }

    echo json_encode($response);
} else {
    echo json_encode($response);
}
?>