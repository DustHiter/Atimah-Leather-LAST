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

if ($action === 'add_user') {
    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $password = $_POST['password'] ?? '';
    $is_admin = filter_input(INPUT_POST, 'is_admin', FILTER_VALIDATE_INT) ? 1 : 0;

    if ($first_name && $last_name && $email && !empty($password)) {
        try {
            $pdo = db();
            
            // Check if user already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $_SESSION['error_message'] = "کاربری با این ایمیل از قبل وجود دارد.";
                header('Location: users.php');
                exit;
            }

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, phone, password, is_admin, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$first_name, $last_name, $email, $phone, $hashed_password, $is_admin]);

            $_SESSION['success_message'] = "کاربر جدید با موفقیت اضافه شد.";
        } catch (PDOException $e) {
            error_log("Add user failed: " . $e->getMessage());
            $_SESSION['error_message'] = "خطایی در افزودن کاربر جدید رخ داد.";
        }
    } else {
        $_SESSION['error_message'] = "اطلاعات وارد شده نامعتبر است. لطفاً تمام فیلدهای ستاره‌دار را پر کنید.";
    }
    header('Location: users.php');
    exit;
}

header('Location: orders.php');
exit;
?>