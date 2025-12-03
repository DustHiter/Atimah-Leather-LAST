<?php
session_start();
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../db/config.php';

// New header - includes nav, head, and opening body/main tags
require_once __DIR__ . '/header.php';

$dashboard_error = null;
$total_products = 0;
$total_orders = 0;
$recent_orders = [];

try {
    $pdo = db();

    $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $recent_orders = $pdo->query("SELECT id, customer_name, total_amount, `status`, created_at FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $dashboard_error = "<strong>خطا در بارگذاری اطلاعات داشبورد:</strong> " . $e->getMessage();
    $dashboard_error .= "<br><br>این خطا معمولاً به دلیل قدیمی بودن ساختار دیتابیس رخ می‌دهد. لطفاً برای به‌روزرسانی به <a href='../migrate.php' class='alert-link'>صفحه مایگریشن</a> بروید.";
}

$flash_message = $_SESSION['flash_message'] ?? null;
if ($flash_message) {
    unset($_SESSION['flash_message']);
}

// Function to map status to a badge class
function get_status_badge($status) {
    switch (strtolower($status)) {
        case 'processing':
            return 'bg-processing';
        case 'shipped':
            return 'bg-shipped';
        case 'cancelled':
            return 'bg-cancelled';
        default:
            return 'bg-pending';
    }
}
?>

<h1 class="h2 mb-4">داشبورد</h1>

<?php if ($dashboard_error): ?>
    <div class="alert alert-danger"><?php echo $dashboard_error; ?></div>
<?php else: ?>

    <!-- Stat Cards -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="stat-card">
                <div class="icon-container">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="stat-info">
                    <p>کل محصولات</p>
                    <h3><?php echo htmlspecialchars($total_products); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="stat-card">
                <div class="icon-container">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="stat-info">
                    <p>کل سفارشات</p>
                    <h3><?php echo htmlspecialchars($total_orders); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card shadow-sm card-table" style="background-color: var(--admin-surface); border-color: var(--admin-border);">
         <div class="card-header">
            <h5 class="mb-0">آخرین سفارشات</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle">
                    <thead>
                        <tr>
                            <th>شماره سفارش</th>
                            <th>نام مشتری</th>
                            <th>مبلغ کل</th>
                            <th>وضعیت</th>
                            <th>تاریخ</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_orders)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">هیچ سفارشی یافت نشد.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo number_format($order['total_amount']); ?> تومان</td>
                                    <td><span class="badge <?php echo get_status_badge($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                                    <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                                    <td class="text-end">
                                        <a href="orders.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-info">مشاهده</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Page-specific scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Flash message handling (if any)
        <?php if ($flash_message): ?>
        Swal.fire({
            title: '<?php echo $flash_message["type"] === "success" ? "موفق" : "خطا"; ?>',
            html: '<?php echo addslashes($flash_message["message"]); ?>',
            icon: '<?php echo $flash_message["type"]; ?>',
            confirmButtonText: 'باشه',
            background: 'var(--admin-surface)',
            color: 'var(--admin-text-primary)'
        });
        <?php endif; ?>
    });
</script>

<?php
// New footer
require_once __DIR__ . '/footer.php';
?>
