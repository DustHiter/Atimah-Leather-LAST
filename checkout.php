<?php
session_start();
require_once 'db/config.php';
require_once 'includes/header.php';

// 1. Check if cart exists and is not empty. If not, redirect to shop.
if (empty($_SESSION['cart'])) {
    header('Location: shop.php');
    exit();
}

$cart_items = $_SESSION['cart'];
$product_details = [];
$total_price = 0;

// 2. Process cart items directly from the session
foreach ($cart_items as $cart_item_id => $item) {
    // Ensure item has required data
    if (!isset($item['price'], $item['quantity'], $item['name'], $item['image_url'])) {
        // Skip malformed items
        continue;
    }

    $item_total = $item['price'] * $item['quantity'];
    $total_price += $item_total;

    // Store details for display
    $product_details[] = [
        'id' => $item['product_id'],
        'name' => $item['name'],
        'price' => $item['price'],
        'image_url' => $item['image_url'],
        'quantity' => $item['quantity'],
        'color' => $item['color'] ?? '', // Handle case where color might not be set
        'total' => $item_total
    ];
}


// 3. If after all checks, product_details is empty (e.g. invalid items in cart), redirect.
if (empty($product_details)) {
    // Clear the invalid cart and redirect
    unset($_SESSION['cart']);
    header('Location: shop.php');
    exit();
}

// 4. Fetch user data if logged in
$user_id = $_SESSION['user_id'] ?? null;
$user = [];
$address = [];

if ($user_id) {
    try {
        $pdo = db();
        $user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $user_stmt->execute([$user_id]);
        $user = $user_stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $address_stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $address_stmt->execute([$user_id]);
        $address = $address_stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        error_log("Checkout user fetch error: " . $e->getMessage());
        // Do not block the page, guest checkout is still possible
    }
}


$shipping_cost = 50000;
$grand_total = $total_price + $shipping_cost;

?>

<main class="checkout-page-wrapper">
    <section class="section-padding">
        <div class="container">
            <div class="text-center" data-aos="fade-down">
                <h1 class="section-title">تکمیل سفارش و پرداخت</h1>
                <p class="text-muted fs-5">اطلاعات خود را برای ارسال سفارش وارد کنید.</p>
            </div>

            <div class="row g-5 mt-5">
                <!-- Billing Details Column -->
                <div class="col-lg-8" data-aos="fade-right">
                    <h3 class="mb-4">جزئیات صورتحساب</h3>

                    <?php
                    if (!empty($_SESSION['checkout_errors'])) {
                        echo '<div class="alert alert-danger"><ul>';
                        foreach ($_SESSION['checkout_errors'] as $error) {
                            echo '<li>' . htmlspecialchars($error) . '</li>';
                        }
                        echo '</ul></div>';
                        // Unset the session variable so it doesn't show again on refresh
                        unset($_SESSION['checkout_errors']);
                    }
                    ?>
                    
                    <form id="checkout-form" action="checkout_handler.php" method="POST">

                        <div class="card mb-4">
                            <div class="card-header">اطلاعات تماس</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="firstName" class="form-label">نام</label>
                                        <input type="text" class="form-control" id="firstName" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="lastName" class="form-label">نام خانوادگی</label>
                                        <input type="text" class="form-control" id="lastName" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">ایمیل (اختیاری)</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">تلفن همراه</label>
                                        <input type="tel" class="form-control" id="phone" name="phone_number" value="<?= htmlspecialchars($address['phone_number'] ?? $user['phone_number'] ?? '') ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">آدرس جهت ارسال</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="address" class="form-label">آدرس</label>
                                    <input type="text" class="form-control" id="address" name="address_line" placeholder="خیابان اصلی، کوچه فرعی، پلاک ۱۲۳" value="<?= htmlspecialchars($address['address_line'] ?? '') ?>" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="city" class="form-label">شهر</label>
                                        <input type="text" class="form-control" id="city" name="city" value="<?= htmlspecialchars($address['city'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="state" class="form-label">استان</label>
                                        <input type="text" class="form-control" id="state" name="province" value="<?= htmlspecialchars($address['province'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="zip" class="form-label">کد پستی</label>
                                        <input type="text" class="form-control" id="zip" name="postal_code" value="<?= htmlspecialchars($address['postal_code'] ?? '') ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>

                <!-- Order Summary Column -->
                <div class="col-lg-4" data-aos="fade-left">
                    <div class="card card-body position-sticky" style="top: 2rem;">
                        <h3 class="card-title">خلاصه سفارش شما</h3>
                        <ul class="summary-item-list">
                            <?php foreach ($product_details as $item) : ?>
                                <li>
                                    <span class="product-name"><?= htmlspecialchars($item['name']) ?> <span class="text-muted">(x<?= $item['quantity'] ?>)</span></span>
                                    <span class="product-total">T <?= number_format($item['total']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="summary-totals">
                            <div class="total-row">
                                <span class="label">جمع کل</span>
                                <span class="value">T <?= number_format($total_price) ?></span>
                            </div>
                            <div class="total-row">
                                <span class="label">هزینه ارسال</span>
                                <span class="value">T <?= number_format($shipping_cost) ?></span>
                            </div>
                            <div class="total-row grand-total mt-3">
                                <span class="label">مبلغ قابل پرداخت</span>
                                <span class="value">T <?= number_format($grand_total) ?></span>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" form="checkout-form" class="btn btn-primary btn-lg">
                                <i class="ri-secure-payment-line me-2"></i>
                                پرداخت و ثبت نهایی سفارش
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>
