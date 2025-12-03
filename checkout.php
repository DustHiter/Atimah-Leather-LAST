<?php
session_start();
require_once 'db/config.php';

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: shop.php');
    exit;
}

$cart_items = $_SESSION['cart'];
$total_price = array_reduce($cart_items, function ($sum, $item) {
    return $sum + ($item['price'] * $item['quantity']);
}, 0);

// User and address data
$logged_in_user = null;
$user_addresses = [];
$is_logged_in = isset($_SESSION['user_id']);

if ($is_logged_in) {
    try {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $logged_in_user = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $user_addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // In a real app, log this error
        die("Error fetching user data.");
    }
}

$page_title = 'تکمیل سفارش';
require_once 'includes/header.php';
?>

<div class="container my-5 bg-dark text-light">
    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['error_message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        unset($_SESSION['error_message']);
    }
    ?>
    <div class="text-center mb-5">
        <h1 class="fw-bold">تکمیل فرآیند خرید</h1>
        <p class="text-muted">فقط یک قدم دیگر تا نهایی شدن سفارش شما باقیست.</p>
    </div>

    <form action="checkout_handler.php" method="POST">
        <div class="row g-5">
            <!-- Shipping Details -->
            <div class="col-lg-7">
                <h3 class="mb-4">اطلاعات ارسال</h3>

                <?php if ($is_logged_in && !empty($user_addresses)): ?>
                    <div class="mb-4">
                        <label for="saved_address" class="form-label">انتخاب آدرس</label>
                        <select class="form-select bg-dark text-light" id="saved_address">
                            <option value="">یک آدرس انتخاب کنید یا فرم زیر را پر کنید...</option>
                            <?php foreach ($user_addresses as $addr): ?>
                                <option value='<?php echo json_encode($addr, JSON_HEX_APOS | JSON_HEX_QUOT); ?>'>
                                    <?php echo htmlspecialchars($addr['province'] . '، ' . $addr['city'] . '، ' . $addr['address_line']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="card bg-dark border-secondary shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">نام</label>
                                <input type="text" class="form-control bg-dark text-light" id="first_name" name="first_name" value="<?php echo htmlspecialchars($logged_in_user['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">نام خانوادگی</label>
                                <input type="text" class="form-control bg-dark text-light" id="last_name" name="last_name" value="<?php echo htmlspecialchars($logged_in_user['last_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone_number" class="form-label">تلفن همراه</label>
                                <input type="tel" class="form-control bg-dark text-light" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($logged_in_user['phone_number'] ?? ''); ?>" required>
                                <?php if (!$is_logged_in): ?>
                                    <div class="form-text text-info fw-bold">توجه: فقط با شماره تلفن همراه میتوان سفارش را رهگیری کرد.</div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="province" class="form-label">استان</label>
                                <input type="text" class="form-control bg-dark text-light" id="province" name="province" required>
                            </div>
                            <div class="col-md-6">
                                <label for="city" class="form-label">شهر</label>
                                <input type="text" class="form-control bg-dark text-light" id="city" name="city" required>
                            </div>
                             <div class="col-md-6">
                                <label for="address_line" class="form-label">آدرس دقیق</label>
                                <textarea class="form-control bg-dark text-light" id="address_line" name="address_line" rows="2" required></textarea>
                            </div>
                            <div class="col-md-5">
                                <label for="postal_code" class="form-label">کد پستی</label>
                                <input type="text" class="form-control bg-dark text-light" id="postal_code" name="postal_code" required>
                            </div>
                            <div class="col-md-7">
                                <label for="email" class="form-label">ایمیل (اختیاری)</label>
                                <input type="email" class="form-control bg-dark text-light" id="email" name="email" value="<?php echo htmlspecialchars($logged_in_user['email'] ?? ''); ?>">
                            </div>
                        </div>
                         <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                با <a href="#">قوانین و مقررات</a> سایت موافقم.
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-5">
                 <div class="card bg-dark border-secondary shadow-sm rounded-4 sticky-top" style="top: 100px;">
                    <div class="card-body p-4">
                        <h4 class="card-title fw-bold mb-4">خلاصه سفارش</h4>
                        <ul class="list-group list-group-flush mb-4">
                            <?php foreach($cart_items as $item): ?>
                            <li class="list-group-item bg-dark text-light d-flex justify-content-between align-items-center px-0">
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" width="60" class="rounded-3 me-3" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <div>
                                        <?php echo htmlspecialchars($item['name']); ?>
                                        <small class="d-block text-muted">تعداد: <?php echo $item['quantity']; ?></small>
                                    </div>
                                </div>
                                <span class="fw-bold"><?php echo number_format($item['price'] * $item['quantity']); ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">جمع کل</span>
                            <span><?php echo number_format($total_price); ?> تومان</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">هزینه ارسال</span>
                            <span class="text-success">رایگان</span>
                        </div>
                        <hr class="border-secondary">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="h5 fw-bold">مبلغ نهایی</span>
                            <span class="h5 fw-bolder text-primary"><?php echo number_format($total_price); ?> تومان</span>
                        </div>
                        <div class="d-grid">
                             <button type="submit" class="btn btn-primary btn-lg">ثبت نهایی سفارش</button>
                        </div>
                         <div class="text-center mt-3">
                            <small class="text-muted"><i class="ri-lock-line me-1"></i> پرداخت امن و مطمئن</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const savedAddressSelect = document.getElementById('saved_address');
    if (savedAddressSelect) {
        savedAddressSelect.addEventListener('change', function() {
            if (this.value) {
                try {
                    const address = JSON.parse(this.value);
                    document.getElementById('province').value = address.province || '';
                    document.getElementById('city').value = address.city || '';
                    document.getElementById('address_line').value = address.address_line || '';
                    document.getElementById('postal_code').value = address.postal_code || '';
                } catch (e) {
                    console.error("Failed to parse address JSON:", e);
                }
            } else {
                // Clear fields if no address is selected
                document.getElementById('province').value = '';
                document.getElementById('city').value = '';
                document.getElementById('address_line').value = '';
                document.getElementById('postal_code').value = '';
            }
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>