<?php
session_start();
require_once 'db/config.php';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get POST data
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
$color = filter_input(INPUT_POST, 'product_color', FILTER_SANITIZE_STRING);
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);

if (!$action || !$product_id) {
    header('Location: shop.php');
    exit;
}

// Generate a unique ID for the cart item based on product ID and color
$cart_item_id = $product_id . ($color ? '_' . str_replace('#', '', $color) : '');

switch ($action) {
    case 'add':
        if ($quantity > 0) {
            try {
                $pdo = db();
                // Fetch product details including colors
                $stmt = $pdo->prepare("SELECT name, price, image_url, colors FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($product) {
                    // --- START COLOR VALIDATION ---
                    $available_colors = [];
                    if (!empty($product['colors'])) {
                        $colors_raw = explode(',', $product['colors']);
                        foreach ($colors_raw as $c) {
                            $trimmed_c = trim($c);
                            if ($trimmed_c) $available_colors[] = $trimmed_c;
                        }
                    }

                    if (count($available_colors) > 1 && empty($color)) {
                        // For multi-color products, a color must be selected.
                        $_SESSION['flash_message'] = [
                            'type' => 'warning',
                            'message' => 'برای افزودن این محصول، انتخاب یکی از رنگ‌ها الزامی است.'
                        ];
                        header('Location: product.php?id=' . $product_id);
                        exit;
                    }
                    // --- END COLOR VALIDATION ---

                    // If item is already in the cart (same product ID and color), just update the quantity.
                    if (isset($_SESSION['cart'][$cart_item_id])) {
                        $_SESSION['cart'][$cart_item_id]['quantity'] += $quantity;
                    } else {
                        // Otherwise, add the new item to the cart.
                        $_SESSION['cart'][$cart_item_id] = [
                            'product_id' => $product_id,
                            'name' => $product['name'],
                            'price' => $product['price'],
                            'image_url' => $product['image_url'],
                            'quantity' => $quantity,
                            'color' => $color
                        ];
                    }
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'message' => 'محصول با موفقیت به سبد خرید اضافه شد!'
                    ];
                } else {
                     $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'محصول یافت نشد.'];
                }
            } catch (PDOException $e) {
                error_log("Cart Add Error: " . $e->getMessage());
                $_SESSION['flash_message'] = [
                    'type' => 'error',
                    'message' => 'مشکلی در افزودن محصول به سبد خرید رخ داد.'
                ];
            }
        }
        // Redirect back to the previous page (likely the product page) to show the flash message.
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'shop.php'));
        exit;

    case 'update':
        if ($quantity > 0) {
            if (isset($_SESSION['cart'][$cart_item_id])) {
                $_SESSION['cart'][$cart_item_id]['quantity'] = $quantity;
            }
        } else {
            // If quantity is 0 or less, remove the item.
            unset($_SESSION['cart'][$cart_item_id]);
        }
        break;

    case 'remove':
        if (isset($_SESSION['cart'][$cart_item_id])) {
            unset($_SESSION['cart'][$cart_item_id]);
        }
        break;
}

// For 'update' and 'remove' actions, redirect to the cart page to show changes.
header('Location: cart.php');
exit;
