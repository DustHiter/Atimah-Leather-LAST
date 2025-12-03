<?php
session_start();
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../db/config.php';

// New header
require_once __DIR__ . '/header.php';

// Fetch orders from the database
try {
    $pdo = db();
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "خطا در دریافت اطلاعات سفارشات: " . $e->getMessage();
    $orders = [];
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

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">مدیریت سفارشات</h1>
</div>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<div class="card shadow-sm card-table" style="background-color: var(--admin-surface); border-color: var(--admin-border);">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>شماره</th>
                        <th>نام مشتری</th>
                        <th>مبلغ کل</th>
                        <th>وضعیت</th>
                        <th>تاریخ</th>
                        <th class="text-end">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">هیچ سفارشی یافت نشد.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td><?php echo number_format($order['total_amount']); ?> تومان</td>
                                <td><span class="badge <?php echo get_status_badge($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                                <td><?php echo date("Y-m-d", strtotime($order['created_at'])); ?></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $order['id']; ?>">
                                        <i class="bi bi-eye-fill"></i> مشاهده
                                    </button>
                                </td>
                            </tr>
                            <!-- Modal for order details -->
                            <div class="modal fade" id="orderModal<?php echo $order['id']; ?>" tabindex="-1" aria-labelledby="orderModalLabel<?php echo $order['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="orderModalLabel<?php echo $order['id']; ?>">جزئیات سفارش #<?php echo $order['id']; ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>نام:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                                    <p><strong>ایمیل:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                                                    <p><strong>تلفن:</strong> <?php echo htmlspecialchars($order['customer_phone'] ?? '-'); ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                     <p><strong>آدرس:</strong> <?php echo htmlspecialchars($order['customer_address']); ?></p>
                                                </div>
                                            </div>
                                            <hr>
                                            <h6>محصولات خریداری شده:</h6>
                                            <?php 
                                                $items = json_decode($order['items_json'], true);
                                                if ($items):
                                            ?>
                                                <ul class="list-group list-group-flush">
                                                <?php foreach($items as $item): ?>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center" style="background: none; color: var(--admin-text-primary);">
                                                        <?php echo htmlspecialchars($item['name']); ?>
                                                        <span class="badge bg-secondary rounded-pill">
                                                            <?php echo $item['quantity']; ?> عدد - <?php echo number_format($item['price']); ?> ت
                                                        </span>
                                                    </li>
                                                <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <p>جزئیات محصولات موجود نیست.</p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                             <p class="w-100 text-start"><strong>مبلغ نهایی: <?php echo number_format($order['total_amount']); ?> تومان</strong></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// New footer
require_once __DIR__ . '/footer.php';
?>
