<?php
session_start();
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../db/config.php';
require_once __DIR__ . '/header.php';

try {
    $pdo = db();
    $query = "SELECT o.*, COALESCE(CONCAT(u.first_name, ' ', u.last_name), o.billing_name) AS customer_display_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC";
    $stmt = $pdo->query($query);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "خطا در دریافت اطلاعات سفارشات: " . $e->getMessage();
    $orders = [];
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
$statuses = ['Processing', 'Shipped', 'Delivered', 'Cancelled'];
?>

<style>
/* Same status badges from index.php */
.status-badge { padding: 0.3em 0.6em; border-radius: 6px; font-size: 0.8rem; font-weight: 600; color: #fff; }
.status-processing { background-color: var(--admin-info); }
.status-shipped { background-color: var(--admin-warning); }
.status-delivered { background-color: var(--admin-success); }
.status-cancelled { background-color: var(--admin-danger); }
.status-pending { background-color: var(--admin-text-muted); }

/* Custom Modal Styles */
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; display: none; align-items: center; justify-content: center; }
.modal-container { background: var(--admin-surface); border: 1px solid var(--admin-border); border-radius: 12px; width: 90%; max-width: 800px; max-height: 90vh; display: flex; flex-direction: column; }
.modal-header { padding: 1rem 1.5rem; border-bottom: 1px solid var(--admin-border); display: flex; justify-content: space-between; align-items: center; }
.modal-body { padding: 1.5rem; overflow-y: auto; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--admin-border); text-align: left; }
.modal-close { background: none; border: none; font-size: 1.5rem; color: var(--admin-text-muted); cursor: pointer; }
.modal-overlay.active { display: flex; }
.items-list img { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; }
</style>

<div class="admin-header">
    <h1>مدیریت سفارشات</h1>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const style = getComputedStyle(document.body);
    Swal.fire({
        title: '<?php echo $_SESSION["flash_message"]["type"] === "success" ? "موفق" : "خطا"; ?>',
        html: '<?php echo addslashes($_SESSION["flash_message"]["message"]); ?>',
        icon: '<?php echo $_SESSION["flash_message"]["type"]; ?>',
        confirmButtonText: 'باشه',
        background: style.getPropertyValue('--admin-surface'),
        color: style.getPropertyValue('--admin-text')
    });
});
</script>
<?php unset($_SESSION['flash_message']); endif; ?>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr><th>شماره</th><th>نام مشتری</th><th>مبلغ کل</th><th>وضعیت</th><th>تاریخ</th><th style="text-align: left;">عملیات</th></tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="6" style="text-align: center; padding: 2rem;">هیچ سفارشی یافت نشد.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['customer_display_name']); ?></td>
                            <td><?php echo number_format($order['total_amount']); ?> تومان</td>
                            <td><span class="status-badge <?php echo get_status_badge_class($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                            <td><?php echo date("Y-m-d", strtotime($order['created_at'])); ?></td>
                            <td style="text-align: left;">
                                <button class="btn btn-sm view-order-btn" data-order-id="<?php echo $order['id']; ?>" style="background-color: var(--admin-info); color: white;"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php foreach ($orders as $order): ?>
<div id="modal-<?php echo $order['id']; ?>" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h5>جزئیات سفارش #<?php echo $order['id']; ?></h5>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <h6>اطلاعات مشتری</h6>
            <p><strong>نام:</strong> <?php echo htmlspecialchars($order['customer_display_name']); ?><br>
            <strong>آدرس:</strong> <?php echo htmlspecialchars($order['billing_address'] . ", " . $order['billing_city'] . ", " . $order['billing_province']); ?><br>
            <strong>تلفن:</strong> <?php echo htmlspecialchars($order['billing_phone']); ?></p>
            <hr style="border-color: var(--admin-border);">
            <h6>محصولات</h6>
            <table class="table items-list">
                <thead>
                    <tr>
                        <th colspan="2">محصول</th>
                        <th>رنگ</th>
                        <th>تعداد</th>
                        <th class="text-start">قیمت واحد</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $items = json_decode($order['items_json'], true); ?>
                    <?php foreach($items as $item): ?>
                    <tr style="vertical-align: middle;">
                        <td style="width: 60px;"><img src="../<?php echo htmlspecialchars($item['image_url']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;"></td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td style="width: 60px;">
                            <?php if (!empty($item['color'])): ?>
                                <span style="display: inline-block; width: 22px; height: 22px; border-radius: 50%; background-color: <?php echo htmlspecialchars($item['color']); ?>; border: 1px solid var(--admin-border); box-shadow: 0 1px 3px rgba(0,0,0,0.1);" title="<?php echo htmlspecialchars($item['color']); ?>"></span>
                            <?php endif; ?>
                        </td>
                        <td style="width: 80px;"><?php echo $item['quantity']; ?> عدد</td>
                        <td style="width: 120px;" class="text-start"><?php echo number_format($item['price']); ?> تومان</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
             <hr style="border-color: var(--admin-border);">
             <h5 style="text-align: left;">مبلغ نهایی: <?php echo number_format($order['total_amount']); ?> تومان</h5>
        </div>
        <div class="modal-footer">
            <form action="handler.php" method="POST" style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                <input type="hidden" name="action" value="update_order_status">
                <div class="form-group" style="display: flex; align-items: center; gap: 1rem;">
                    <label for="status_<?php echo $order['id']; ?>" class="form-label">تغییر وضعیت:</label>
                    <select class="form-control" name="status" id="status_<?php echo $order['id']; ?>">
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo $status; ?>" <?php echo ($order['status'] === $status) ? 'selected' : ''; ?>><?php echo $status; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">به‌روزرسانی</button>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewButtons = document.querySelectorAll('.view-order-btn');
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            document.getElementById('modal-' + orderId).classList.add('active');
        });
    });

    const closeButtons = document.querySelectorAll('.modal-close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.modal-overlay').classList.remove('active');
        });
    });
    
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>