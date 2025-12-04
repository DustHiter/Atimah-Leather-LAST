<?php
session_start();
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../db/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'update_order_status') {
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

    $allowed_statuses = ['Processing', 'Shipped', 'Delivered', 'Cancelled'];

    if ($order_id && $status && in_array($status, $allowed_statuses)) {
        try {
            $pdo = db();
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $order_id]);

            $_SESSION['success_message'] = "وضعیت سفارش #{$order_id} با موفقیت به '{$status}' تغییر یافت.";
        } catch (PDOException $e) {
            error_log("Order status update failed: " . $e->getMessage());
            $_SESSION['error_message'] = "خطایی در به‌روزرسانی وضعیت سفارش رخ داد.";
        }
    } else {
        $_SESSION['error_message'] = "اطلاعات نامعتبر برای به‌روزرسانی وضعیت.";
    }
}

header('Location: orders.php');
exit;
?>