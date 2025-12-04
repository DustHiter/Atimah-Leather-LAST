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

$page_title = 'حساب کاربری';
require_once 'includes/header.php';
?>

<style>
    body {
        background-color: #f4f7f6;
    }
    .profile-container {
        display: flex;
        gap: 30px;
    }
    .profile-sidebar {
        flex: 0 0 280px;
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 4px 25px rgba(0,0,0,0.05);
        padding: 20px;
        align-self: flex-start;
    }
    .profile-content {
        flex: 1;
    }
    .user-card {
        text-align: center;
        padding: 20px 10px;
        border-bottom: 1px solid #eee;
        margin-bottom: 20px;
    }
    .user-card .user-avatar {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background-color: var(--bs-primary);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin: 0 auto 15px auto;
        font-weight: bold;
    }
    .user-card h5 {
        font-weight: 600;
        margin-bottom: 5px;
    }
    .user-card p {
        color: #888;
        font-size: 0.9rem;
    }
    .profile-nav .nav-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 15px;
        border-radius: 10px;
        color: #555;
        font-weight: 500;
        transition: all 0.3s ease;
        margin-bottom: 5px;
    }
    .profile-nav .nav-link i {
        font-size: 1.3rem;
        color: #888;
        transition: all 0.3s ease;
    }
    .profile-nav .nav-link.active,
    .profile-nav .nav-link:hover {
        background-color: var(--bs-primary-light);
        color: var(--bs-primary);
    }
    .profile-nav .nav-link.active i,
    .profile-nav .nav-link:hover i {
        color: var(--bs-primary);
    }

    .tab-pane h3 {
        font-weight: 700;
        margin-bottom: 25px;
        color: #333;
    }

    /* Order Accordion Styles */
    .order-accordion .accordion-item {
        border: none;
        border-radius: 15px;
        margin-bottom: 20px;
        box-shadow: 0 4px 25px rgba(0,0,0,0.05);
        background-color: #fff;
    }
    .order-accordion .accordion-button {
        border-radius: 15px !important;
        background-color: #fff;
        box-shadow: none;
        padding: 20px;
    }
     .order-accordion .accordion-button:not(.collapsed) {
        border-bottom: 1px solid #eee;
    }
    .order-header {
        display: flex;
        justify-content: space-between;
        width: 100%;
        align-items: center;
    }
    .order-header-item {
        flex: 1;
        text-align: right;
        font-size: 0.9rem;
    }
     .order-header-item:first-child { text-align: right; }
    .order-header-item span {
        display: block;
        font-size: 0.8rem;
        color: #888;
    }
     .order-header-item strong {
        font-weight: 600;
        color: #333;
        font-size: 1rem;
    }
    .order-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: 500;
        color: #fff;
        font-size: 0.8rem;
    }
    .order-details-table {
        margin-top: 15px;
    }
    .order-details-table img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 10px;
    }
    .product-color-swatch {
        display: inline-block;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 1px solid #eee;
        vertical-align: middle;
    }

</style>

<div class="container my-5">
    <div class="profile-container">
        <!-- Profile Sidebar -->
        <div class="profile-sidebar">
            <div class="user-card">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                </div>
                <h5><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h5>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <ul class="nav flex-column profile-nav" id="profileTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="orders-tab" data-bs-toggle="tab" href="#orders" role="tab" aria-controls="orders" aria-selected="true">
                        <i class="ri-shopping-bag-3-line"></i>
                        سفارشات من
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="addresses-tab" data-bs-toggle="tab" href="#addresses" role="tab" aria-controls="addresses" aria-selected="false">
                        <i class="ri-map-pin-line"></i>
                        آدرس‌های من
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="ri-logout-box-r-line"></i>
                        خروج از حساب
                    </a>
                </li>
            </ul>
        </div>

        <!-- Profile Content -->
        <div class="profile-content">
            <div class="tab-content" id="profileTabContent">
                <!-- Orders Tab -->
                <div class="tab-pane fade show active" id="orders" role="tabpanel" aria-labelledby="orders-tab">
                    <h3>تاریخچه سفارشات</h3>
                    <?php if (empty($orders)): ?>
                        <div class="alert alert-light text-center">شما هنوز هیچ سفارشی ثبت نکرده‌اید.</div>
                    <?php else: ?>
                        <div class="accordion order-accordion" id="ordersAccordion">
                            <?php foreach ($orders as $index => $order): ?>
                                <?php
                                    $items = json_decode($order['items_json'], true);
                                    $status_map = [
                                        'pending' => ['label' => 'در انتظار پرداخت', 'color' => '#ffc107'],
                                        'processing' => ['label' => 'در حال پردازش', 'color' => '#0dcaf0'],
                                        'shipped' => ['label' => 'ارسال شده', 'color' => '#0d6efd'],
                                        'completed' => ['label' => 'تکمیل شده', 'color' => '#198754'],
                                        'cancelled' => ['label' => 'لغو شده', 'color' => '#dc3545'],
                                    ];
                                    $status_info = $status_map[$order['status']] ?? ['label' => htmlspecialchars($order['status']), 'color' => '#6c757d'];
                                ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading<?php echo $order['id']; ?>">
                                        <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $order['id']; ?>" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $order['id']; ?>">
                                            <div class="order-header">
                                                <div class="order-header-item">
                                                    <span>شماره سفارش</span>
                                                    <strong>#<?php echo $order['id']; ?></strong>
                                                </div>
                                                <div class="order-header-item">
                                                    <span>تاریخ ثبت</span>
                                                    <strong><?php echo jdate('d F Y', strtotime($order['created_at'])); ?></strong>
                                                </div>
                                                <div class="order-header-item">
                                                    <span>مبلغ کل</span>
                                                    <strong><?php echo number_format($order['total_amount']); ?> تومان</strong>
                                                </div>
                                                 <div class="order-header-item text-start">
                                                    <span class="order-status" style="background-color: <?php echo $status_info['color']; ?>;">
                                                        <?php echo $status_info['label']; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $order['id']; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $order['id']; ?>" data-bs-parent="#ordersAccordion">
                                        <div class="accordion-body">
                                            <h6>جزئیات سفارش</h6>
                                            <?php if (!empty($order['tracking_id'])): ?>
                                                <p><strong>کد رهگیری:</strong> <?php echo htmlspecialchars($order['tracking_id']); ?></p>
                                            <?php endif; ?>

                                            <table class="table order-details-table">
                                                <tbody>
                                                <?php foreach ($items as $item): ?>
                                                    <tr>
                                                        <td><img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>"></td>
                                                        <td>
                                                            <?php echo htmlspecialchars($item['name']); ?>
                                                            <?php if (!empty($item['color'])): ?>
                                                                <br>
                                                                <small>
                                                                    رنگ: 
                                                                    <span class="product-color-swatch" style="background-color: <?php echo htmlspecialchars($item['color']); ?>" title="<?php echo htmlspecialchars($item['color']); ?>"></span>
                                                                </small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo $item['quantity']; ?> عدد</td>
                                                        <td class="text-start"><?php echo number_format($item['price']); ?> تومان</td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Addresses Tab -->
                <div class="tab-pane fade" id="addresses" role="tabpanel" aria-labelledby="addresses-tab">
                     <h3>آدرس‌های من</h3>
                     <!-- Address management will be implemented here -->
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>

