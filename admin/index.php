<?php
session_start();
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../db/config.php';

$page_title = 'داشبورد';
require_once __DIR__ . '/header.php';

$dashboard_error = null;
$total_products = 0;
$total_orders = 0;
$recent_orders = [];

try {
    $pdo = db();
    $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    
    $recent_orders_query = "
        SELECT 
            o.id, 
            COALESCE(CONCAT(u.first_name, ' ', u.last_name), o.billing_name) AS customer_name, 
            o.total_amount, 
            o.status, 
            o.created_at
        FROM orders AS o 
        LEFT JOIN users AS u ON o.user_id = u.id 
        ORDER BY o.created_at DESC 
        LIMIT 5
    ";
    $recent_orders = $pdo->query($recent_orders_query)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $dashboard_error = "<strong>خطا در بارگذاری اطلاعات:</strong> " . $e->getMessage();
}

$flash_message = $_SESSION['flash_message'] ?? null;
if ($flash_message) {
    unset($_SESSION['flash_message']);
}

function get_status_badge_class($status) {
    switch (strtolower($status)) {
        case 'processing': return 'status-processing';
        case 'shipped': return 'status-shipped';
        case 'delivered': return 'status-delivered';
        case 'cancelled': return 'status-cancelled';
        default: return 'status-pending';
    }
}
?>



<?php if ($flash_message): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        title: '<?php echo $flash_message["type"] === "success" ? "موفق" : "خطا"; ?>',
        html: '<?php echo addslashes($flash_message["message"]); ?>',
        icon: '<?php echo $flash_message["type"]; ?>',
        confirmButtonText: 'باشه',
        background: 'var(--admin-surface)',
        color: 'var(--admin-text)'
    });
});
</script>
<?php endif; ?>

<?php if ($dashboard_error): ?>
    <div class="card"><div class="card-body" style="color: var(--admin-danger);"><?php echo $dashboard_error; ?></div></div>
<?php else: ?>

    <div class="stat-cards-grid">
        <div class="stat-card">
            <div class="icon bg-primary"><i class="fas fa-box"></i></div>
            <div class="stat-info">
                <p>کل محصولات</p>
                <h3><?php echo htmlspecialchars($total_products); ?></h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon bg-warning"><i class="fas fa-receipt"></i></div>
            <div class="stat-info">
                <p>کل سفارشات</p>
                <h3><?php echo htmlspecialchars($total_orders); ?></h3>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">آخرین سفارشات</div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>شماره سفارش</th>
                        <th>نام مشتری</th>
                        <th>مبلغ کل</th>
                        <th>وضعیت</th>
                        <th>تاریخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_orders)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 2rem;">هیچ سفارشی یافت نشد.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td><?php echo number_format($order['total_amount']); ?> تومان</td>
                                <td><span class="status-badge <?php echo get_status_badge_class($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                                <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
