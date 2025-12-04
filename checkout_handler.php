<?php
session_start();
require_once 'db/config.php';

// 1. Basic Security: Only allow POST requests and check for cart
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['cart'])) {
    header('Location: shop.php');
    exit;
}

// 2. Retrieve and sanitize form data
$first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
$last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
$phone_number = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$province = filter_input(INPUT_POST, 'province', FILTER_SANITIZE_STRING);
$city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
$address_line = filter_input(INPUT_POST, 'address_line', FILTER_SANITIZE_STRING);
$postal_code = filter_input(INPUT_POST, 'postal_code', FILTER_SANITIZE_STRING);

// 3. Server-side validation (Email is now optional)
if (!$first_name || !$last_name || !$phone_number || !$province || !$city || !$address_line || !$postal_code) {
    $_SESSION['error_message'] = 'لطفاً تمام فیلدهای آدرس به جز ایمیل را تکمیل کنید.';
    header('Location: checkout.php');
    exit;
}

$pdo = db();
$pdo->beginTransaction();

try {
    // 4. Prepare order data
    $billing_name = trim($first_name . ' ' . $last_name);
    $cart_items = $_SESSION['cart'];
    $total_amount = array_reduce($cart_items, function ($sum, $item) {
        return $sum + ($item['price'] * $item['quantity']);
    }, 0);
    $products_data_json = json_encode($cart_items, JSON_UNESCAPED_UNICODE);

    // 5. Insert the order into the database using the correct, updated column names
    $stmt = $pdo->prepare(
        "INSERT INTO orders (user_id, billing_name, billing_email, billing_phone, billing_province, billing_city, billing_address, billing_postal_code, total_amount, items_json, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $status = 'Processing'; // Default status
    $final_email = ($email !== false && $email !== '') ? $email : null; 

    $stmt->execute([
        $user_id,
        $billing_name,
        $final_email,
        $phone_number,
        $province,
        $city,
        $address_line,
        $postal_code,
        $total_amount,
        $products_data_json,
        $status
    ]);
    
    $order_id = $pdo->lastInsertId();
    $tracking_id = uniqid('ATMH-');

    $update_stmt = $pdo->prepare("UPDATE orders SET tracking_id = ? WHERE id = ?");
    $update_stmt->execute([$tracking_id, $order_id]);
    
    // 6. If user is logged in, save the new address for future use
    if ($user_id) {
        $stmt_check_addr = $pdo->prepare("SELECT COUNT(*) FROM user_addresses WHERE user_id = ? AND address_line = ? AND postal_code = ?");
        $stmt_check_addr->execute([$user_id, $address_line, $postal_code]);
        $address_exists = $stmt_check_addr->fetchColumn();

        if ($address_exists == 0) {
            $stmt_save_addr = $pdo->prepare(
                "INSERT INTO user_addresses (user_id, first_name, last_name, phone_number, province, city, address_line, postal_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt_save_addr->execute([
                $user_id, $first_name, $last_name, $phone_number, $province, $city, $address_line, $postal_code
            ]);
        }
    }

    // 7. Commit transaction
    $pdo->commit();

    // 8. Clear the cart and redirect with a success message including tracking ID
    unset($_SESSION['cart']);
    $_SESSION['success_message'] = "سفارش شما با موفقیت ثبت شد! کد پیگیری شما: <strong>" . htmlspecialchars($tracking_id) . "</strong>";
    // As I don't have SMS capability, I am displaying the tracking code here.
    // You can later integrate an SMS service and send the tracking ID to $phone_number.
    
    header('Location: index.php');
    exit;

} catch (Exception $e) {
    // 9. If anything fails, rollback and redirect with an error
    $pdo->rollBack();
    error_log("Order Creation Failed: " . $e->getMessage()); // Log error for admin
    $_SESSION['error_message'] = 'خطایی در ثبت سفارش رخ داد. لطفاً دوباره تلاش کنید.';
    header('Location: checkout.php');
    exit;
}
