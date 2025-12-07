<?php

session_start();
require_once 'db/config.php';
require_once 'includes/jdf.php'; // For Jalali date conversion


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$pdo = db();

// Handle form submissions for account page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_details') {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($first_name) || empty($last_name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['profile_message'] = 'لطفاً تمام فیلدها را به درستی پر کنید.';
            $_SESSION['profile_message_type'] = 'danger';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
                $stmt->execute([$first_name, $last_name, $email, $user_id]);
                $_SESSION['profile_message'] = 'اطلاعات شما با موفقیت به‌روزرسانی شد.';
                $_SESSION['profile_message_type'] = 'success';
            } catch (PDOException $e) {
                 // Check for duplicate email error
                if ($e->errorInfo[1] == 1062) {
                    $_SESSION['profile_message'] = 'این ایمیل قبلاً ثبت شده است. لطفاً ایمیل دیگری را امتحان کنید.';
                } else {
                    $_SESSION['profile_message'] = 'خطا در به‌روزرسانی اطلاعات.';
                }
                $_SESSION['profile_message_type'] = 'danger';
            }
        }
        header('Location: profile.php?page=account');
        exit;
    } elseif ($_POST['action'] === 'update_password') {
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (strlen($new_password) < 8) {
            $_SESSION['profile_message'] = 'رمز عبور جدید باید حداقل ۸ کاراکتر باشد.';
            $_SESSION['profile_message_type'] = 'danger';
        } elseif ($new_password !== $confirm_password) {
            $_SESSION['profile_message'] = 'رمزهای عبور جدید با هم مطابقت ندارند.';
            $_SESSION['profile_message_type'] = 'danger';
        } elseif (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashed_password, $user_id])) {
                $_SESSION['profile_message'] = 'رمز عبور شما با موفقیت تغییر کرد.';
                $_SESSION['profile_message_type'] = 'success';
            } else {
                $_SESSION['profile_message'] = 'خطا در تغییر رمز عبور.';
                $_SESSION['profile_message_type'] = 'danger';
            }
        }
        header('Location: profile.php?page=account');
        exit;
    } elseif ($_POST['action'] === 'add_address') {
        $province = trim($_POST['province'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $address_line = trim($_POST['address_line'] ?? '');
        $postal_code = trim($_POST['postal_code'] ?? '');
        $is_default = isset($_POST['is_default']);

        if (empty($province) || empty($city) || empty($address_line) || empty($postal_code)) {
            $_SESSION['profile_message'] = 'لطفاً تمام فیلدهای آدرس را پر کنید.';
            $_SESSION['profile_message_type'] = 'danger';
        } else {
            $pdo->beginTransaction();
            try {
                if ($is_default) {
                    $stmt = $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                }
                $stmt = $pdo->prepare("INSERT INTO user_addresses (user_id, province, city, address_line, postal_code, is_default) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $province, $city, $address_line, $postal_code, $is_default ? 1 : 0]);
                $pdo->commit();
                $_SESSION['profile_message'] = 'آدرس جدید با موفقیت اضافه شد.';
                $_SESSION['profile_message_type'] = 'success';
            } catch (PDOException $e) {
                $pdo->rollBack();
                $_SESSION['profile_message'] = 'خطا در افزودن آدرس.';
                $_SESSION['profile_message_type'] = 'danger';
            }
        }
        header('Location: profile.php?page=addresses');
        exit;
    } elseif ($_POST['action'] === 'delete_address') {
        $address_id = $_POST['address_id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$address_id, $user_id])) {
            $_SESSION['profile_message'] = 'آدرس با موفقیت حذف شد.';
            $_SESSION['profile_message_type'] = 'success';
        } else {
            $_SESSION['profile_message'] = 'خطا در حذف آدرس.';
            $_SESSION['profile_message_type'] = 'danger';
        }
        header('Location: profile.php?page=addresses');
        exit;
    } elseif ($_POST['action'] === 'set_default_address') {
        $address_id = $_POST['address_id'] ?? 0;
        $pdo->beginTransaction();
        try {
            $stmt1 = $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
            $stmt1->execute([$user_id]);
            $stmt2 = $pdo->prepare("UPDATE user_addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
            $stmt2->execute([$address_id, $user_id]);
            $pdo->commit();
            $_SESSION['profile_message'] = 'آدرس پیش‌فرض با موفقیت تغییر کرد.';
            $_SESSION['profile_message_type'] = 'success';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['profile_message'] = 'خطا در تغییر آدرس پیش‌فرض.';
            $_SESSION['profile_message_type'] = 'danger';
        }
        header('Location: profile.php?page=addresses');
        exit;
    }
}

// Retrieve flash message
if (isset($_SESSION['profile_message'])) {
    $flash_message = $_SESSION['profile_message'];
    $flash_message_type = $_SESSION['profile_message_type'];
    unset($_SESSION['profile_message']);
    unset($_SESSION['profile_message_type']);
}

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user addresses
$stmt_addresses = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
$stmt_addresses->execute([$user_id]);
$addresses = $stmt_addresses->fetchAll(PDO::FETCH_ASSOC);

// Fetch user orders with items
$stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt_orders->execute([$user_id]);
$orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);

// Calculate total purchase amount from COMPLETED orders
$total_purchase_amount = 0;
foreach ($orders as $order) {
    if (strtolower($order['status']) === 'completed') {
        $total_purchase_amount += $order['total_amount'];
    }
}


$page_title = 'حساب کاربری';
require_once 'includes/header.php';
?>

<?php
// Simple router to determine the current page
$page = $_GET['page'] ?? 'dashboard';
?>

<main>
    <section class="section-padding">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-down">
                <h1 class="section-title">حساب کاربری من</h1>
            </div>
            <div class="row g-4">

                <!-- Off-canvas Toggle Button (Visible on Mobile) -->
                <div class="d-lg-none col-12">
                    <button class="btn btn-primary w-100" type="button" data-bs-toggle="offcanvas" data-bs-target="#profileOffcanvas" aria-controls="profileOffcanvas">
                        <i class="ri-menu-line me-2"></i>
                        منوی حساب کاربری
                    </button>
                </div>

                <!-- Profile Sidebar -->
                <div class="col-lg-3">
                    <div class="offcanvas-lg offcanvas-end bg-dark" tabindex="-1" id="profileOffcanvas" aria-labelledby="profileOffcanvasLabel">
                        <div class="offcanvas-header border-bottom">
                            <h5 class="offcanvas-title" id="profileOffcanvasLabel">منوی کاربری</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close" data-bs-target="#profileOffcanvas"></button>
                        </div>
                        <div class="offcanvas-body">
                            <div class="card card-body text-center mb-4" data-aos="fade-up">
                                <div class="user-avatar mb-3">
                                    <i class="ri-user-fill display-4"></i>
                                </div>
                                <h5><?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></h5>
                                <p class="text-muted mb-0"><?= htmlspecialchars($user['email'] ?? ''); ?></p>
                            </div>
                            <div class="card card-body p-2" data-aos="fade-up" data-aos-delay="100">
                                <ul class="nav flex-column profile-nav">
                                    <li class="nav-item">
                                        <a class="nav-link <?= ($page === 'dashboard') ? 'active' : '' ?>" href="profile.php?page=dashboard">
                                            <i class="ri-dashboard-line me-2"></i>
                                            داشبورد
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?= ($page === 'orders') ? 'active' : '' ?>" href="profile.php?page=orders">
                                            <i class="ri-shopping-bag-3-line me-2"></i>
                                            سفارشات من
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?= ($page === 'addresses') ? 'active' : '' ?>" href="profile.php?page=addresses">
                                            <i class="ri-map-pin-line me-2"></i>
                                            آدرس‌های من
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?= ($page === 'account') ? 'active' : '' ?>" href="profile.php?page=account">
                                            <i class="ri-user-settings-line me-2"></i>
                                            جزئیات حساب
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="logout.php">
                                            <i class="ri-logout-box-r-line me-2"></i>
                                            خروج از حساب
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Content -->
                <div class="col-lg-9" data-aos="fade-left">
                    <?php if (isset($flash_message)): ?>
                        <div class="alert alert-<?= $flash_message_type; ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($flash_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($page === 'dashboard'): ?>
                        <div class="card card-body p-4 p-md-5 text-center text-md-start">
                            <h3 class="dashboard-title">سلام، <?= htmlspecialchars($user['first_name'] ?? 'کاربر'); ?> عزیز!</h3>
                            <p>به پنل کاربری خود خوش آمدید. از اینجا می‌توانید آخرین سفارشات خود را مشاهده کرده، اطلاعات حساب خود را مدیریت کنید و آدرس‌های خود را به‌روزرسانی نمایید.</p>
                        </div>
                        
                        <div class="row g-4 mt-4">
                            <div class="col-md-6">
                                <div class="card card-body summary-card">
                                    <i class="ri-shopping-cart-2-line"></i>
                                    <div class="summary-card-info">
                                        <span>تعداد کل سفارشات</span>
                                        <strong><?= count($orders); ?></strong>
                                    </div>
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="card card-body summary-card">
                                    <i class="ri-wallet-3-line"></i>
                                    <div class="summary-card-info">
                                        <span>مجموع خرید شما</span>
                                        <strong><?= number_format($total_purchase_amount); ?> تومان</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($page === 'orders'): ?>
                        <div class="card">
                            <div class="card-header">
                                <h4 class="m-0">تاریخچه سفارشات</h4>
                            </div>
                            <div class="card-body">
                                <?php if (empty($orders)): ?>
                                    <div class="alert alert-secondary text-center">شما هنوز هیچ سفارشی ثبت نکرده‌اید.</div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-dark table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>شماره سفارش</th>
                                                    <th>تاریخ</th>
                                                    <th>وضعیت</th>
                                                    <th>مبلغ کل</th>
                                                    <th class="text-end">عملیات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($orders as $order): ?>
                                                     <?php
                                                        $status_map = [
                                                            'pending' => 'در انتظار پرداخت',
                                                            'processing' => 'در حال پردازش',
                                                            'shipped' => 'ارسال شده',
                                                            'completed' => 'تکمیل شده',
                                                            'cancelled' => 'لغو شده',
                                                        ];
                                                        $order_status_lower = strtolower($order['status']);
                                                        $status_label = $status_map[$order_status_lower] ?? htmlspecialchars($order['status']);
                                                        $status_class = 'status-' . htmlspecialchars($order_status_lower);
                                                    ?>
                                                    <tr>
                                                        <td><strong>#<?= $order['id']; ?></strong></td>
                                                        <td><?= jdate('d F Y', strtotime($order['created_at'])); ?></td>
                                                        <td><span class="order-status <?= $status_class; ?>"><?= $status_label; ?></span></td>
                                                        <td><?= number_format($order['total_amount']); ?> تومان</td>
                                                        <td class="text-end">
                                                            <button type="button" class="btn btn-sm btn-outline-primary view-order-btn" data-tracking-id="<?= htmlspecialchars($order['tracking_id']); ?>">نمایش جزئیات</button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php elseif ($page === 'addresses'): ?>
                         <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="m-0">آدرس‌های من</h4>
                                <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#add-address-form" aria-expanded="false" aria-controls="add-address-form">
                                    <i class="ri-add-line me-1"></i> افزودن آدرس جدید
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="collapse p-3 border rounded bg-dark mb-4" id="add-address-form">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="add_address">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="add_province" class="form-label">استان</label>
                                                <input type="text" class="form-control" id="add_province" name="province" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_city" class="form-label">شهر</label>
                                                <input type="text" class="form-control" id="add_city" name="city" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_postal_code" class="form-label">کد پستی</label>
                                                <input type="text" class="form-control" id="add_postal_code" name="postal_code" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="new_address" class="form-label">آدرس کامل</label>
                                            <textarea class="form-control" id="new_address" name="address_line" rows="2" required placeholder="خیابان، کوچه، پلاک، واحد"></textarea>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="is_default" id="is_default">
                                            <label class="form-check-label" for="is_default">
                                                انتخاب به عنوان آدرس پیش‌فرض
                                            </label>
                                        </div>
                                        <button type="submit" class="btn btn-success">ذخیره آدرس</button>
                                    </form>
                                </div>

                                <?php if (empty($addresses)): ?>
                                    <div class="alert alert-secondary text-center">شما هنوز هیچ آدرسی ثبت نکرده‌اید.</div>
                                <?php else: ?>
                                    <div class="address-list">
                                        <?php foreach ($addresses as $address): ?>
                                            <div class="card card-body mb-3 address-item">
                                                <div class="address-content">
                                                    <p><?= htmlspecialchars(implode(', ', array_filter([$address['province'], $address['city'], $address['address_line'], $address['postal_code']]))) ?></p>
                                                    <?php if ($address['is_default']): ?>
                                                        <span class="badge bg-primary">پیش‌فرض</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="address-actions">
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="delete_address">
                                                        <input type="hidden" name="address_id" value="<?= $address['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('آیا از حذف این آدرس مطمئن هستید؟');">حذف</button>
                                                    </form>
                                                    <?php if (!$address['is_default']): ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="set_default_address">
                                                            <input type="hidden" name="address_id" value="<?= $address['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary">انتخاب به عنوان پیش‌فرض</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php elseif ($page === 'account'): ?>
                        <div class="card">
                            <div class="card-header"><h4 class="m-0">جزئیات حساب</h4></div>
                            <div class="card-body">
                                <form id="account-details-form" method="POST">
                                    <input type="hidden" name="action" value="update_details">
                                    <p class="fs-6 text-muted">اطلاعات شخصی خود را اینجا ویرایش کنید.</p>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="first_name" class="form-label">نام</label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="last_name" class="form-label">نام خانوادگی</label>
                                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">آدرس ایمیل</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? ''); ?>" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                                </form>
                            </div>
                        </div>
                        <div class="card mt-4">
                            <div class="card-header"><h4 class="m-0">تغییر رمز عبور</h4></div>
                            <div class="card-body">
                                <form id="password-form" method="POST">
                                     <input type="hidden" name="action" value="update_password">
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">رمز عبور جدید</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">تکرار رمز عبور جدید</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">تغییر رمز عبور</button>
                                </form>
                            </div>
                        </div>

                    <?php else: ?>
                         <div class="alert alert-danger">صفحه مورد نظر یافت نشد.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>


<!-- The Modal for Order Details -->
<div id="order-modal" class="order-modal">
    <div class="order-modal-content">
        <span class="order-modal-close-btn">&times;</span>
        <div id="modal-body">
            <!-- Order details will be injected here by JavaScript -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('order-modal');
    const modalBody = document.getElementById('modal-body');
    const closeBtn = document.querySelector('.order-modal-close-btn');

    // Function to open modal and fetch order details
    async function openOrderModal(trackingId) {
        modalBody.innerHTML = `<div class="text-center p-5"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>`;
        modal.style.display = 'block';

        try {
            const response = await fetch('api/get_order_details.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tracking_id: trackingId }),
            });

            if (!response.ok) throw new Error(`Server error: ${response.status}`);
            
            const data = await response.json();

            if (data.success) {
                displayOrderDetails(data.order, data.products);
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            modalBody.innerHTML = `<div class="alert alert-danger"><strong>خطا در ارتباط با سرور:</strong><br>${error.message}</div>`;
        }
    }

    // Function to display fetched order details in the modal
    function displayOrderDetails(order, products) {
        let productsHtml = `
            <div class="detail-box" style="grid-column: 1 / -1;">
                <h3>محصولات سفارش</h3>
                <div class="table-responsive">
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>محصول</th>
                                <th>تعداد</th>
                                <th>رنگ</th>
                                <th>قیمت واحد</th>
                                <th>قیمت کل</th>
                            </tr>
                        </thead>
                        <tbody>`;
        products.forEach(p => {
            productsHtml += `
                <tr>
                    <td data-label="محصول"><img src="${p.image_url}" alt="${p.name}" class="me-2">${p.name}</td>
                    <td data-label="تعداد">${p.quantity}</td>
                    <td data-label="رنگ"><span class="cart-item-color-swatch" style="background-color: ${p.color || 'transparent'}"></span></td>
                    <td data-label="قیمت واحد">${parseInt(p.price).toLocaleString()} تومان</td>
                    <td data-label="قیمت کل">${(p.quantity * p.price).toLocaleString()} تومان</td>
                </tr>`;
        });
        productsHtml += `</tbody></table></div></div>`;

        const fullAddress = [order.billing_province, order.billing_city, order.billing_address, order.billing_postal_code].filter(Boolean).join(', ');

        modalBody.innerHTML = `
            <div class="order-modal-header">
                <h2>جزئیات سفارش</h2>
                <p class="text-muted">کد رهگیری: ${order.tracking_id}</p>
            </div>
            <div class="order-details-grid">
                <div class="detail-box">
                    <h3>اطلاعات خریدار</h3>
                    <p><strong>نام:</strong> ${order.full_name}</p>
                    <p><strong>ایمیل:</strong> ${order.billing_email}</p>
                    <p><strong>تلفن:</strong> ${order.billing_phone}</p>
                </div>
                <div class="detail-box">
                    <h3>اطلاعات سفارش</h3>
                    <p><strong>وضعیت:</strong> <span class="order-status status-${order.status}">${order.status_jalali}</span></p>
                    <p><strong>تاریخ ثبت:</strong> ${order.created_at_jalali}</p>
                    <p><strong>آدرس:</strong> ${fullAddress}</p>
                </div>
                ${productsHtml}
            </div>
            <div class="summary-totals mt-4 text-center">
                 <div class="grand-total">
                    <span class="label">جمع کل: </span>
                    <span class="value">${parseInt(order.total_amount).toLocaleString()} تومان</span>
                </div>
            </div>`;
    }

    // Add event listeners to all "View Details" buttons
    const viewButtons = document.querySelectorAll('.view-order-btn');
    viewButtons.forEach(button => {
        button.addEventListener('click', function () {
            const trackingId = this.getAttribute('data-tracking-id');
            if (trackingId) {
                openOrderModal(trackingId);
            }
        });
    });

    // Close modal logic
    closeBtn.onclick = () => modal.style.display = 'none';
    window.onclick = (event) => {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    };
});
</script>

<?php
require_once 'includes/footer.php';
?>