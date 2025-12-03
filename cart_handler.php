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
            // Check if product exists and get details
            try {
                $pdo = db();
                $stmt = $pdo->prepare("SELECT name, price, image_url FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($product) {
                    // If item already in cart, update quantity
                    if (isset($_SESSION['cart'][$cart_item_id])) {
                        $_SESSION['cart'][$cart_item_id]['quantity'] += $quantity;
                    } else {
                        // Otherwise, add new item
                        $_SESSION['cart'][$cart_item_id] = [
                            'product_id' => $product_id,
                            'name' => $product['name'],
                            'price' => $product['price'],
                            'image_url' => $product['image_url'],
                            'quantity' => $quantity,
                            'color' => $color
                        ];
                    }
                }
            } catch (PDOException $e) {
                // Log error, maybe set a session error message to display in cart
                error_log("Cart Add Error: " . $e->getMessage());
            }
        }
        break;

    case 'update':
        if ($quantity > 0) {
            if (isset($_SESSION['cart'][$cart_item_id])) {
                $_SESSION['cart'][$cart_item_id]['quantity'] = $quantity;
            }
        } else {
            // If quantity is 0 or less, remove the item
            unset($_SESSION['cart'][$cart_item_id]);
        }
        break;

    case 'remove':
        if (isset($_SESSION['cart'][$cart_item_id])) {
            unset($_SESSION['cart'][$cart_item_id]);
        }
        break;
}

// Redirect back to the cart page to show changes
header('Location: cart.php');
exit;
