<?php
session_start();
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../db/config.php';
require_once __DIR__ . '/header.php';

try {
    $pdo = db();
    $stmt = $pdo->query("SELECT id, name, price FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching products: " . $e->getMessage());
}

$flash_message = $_SESSION['flash_message'] ?? null;
if ($flash_message) {
    unset($_SESSION['flash_message']);
}
?>

<div class="admin-header">
    <h1>مدیریت محصولات</h1>
    <a href="add_product.php" class="btn btn-primary"><i class="fas fa-plus"></i> افزودن محصول</a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>نام محصول</th>
                    <th>قیمت</th>
                    <th style="text-align: left;">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="4" style="text-align: center; padding: 2rem;">هیچ محصولی یافت نشد.</td></tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['id']); ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo number_format($product['price']); ?> تومان</td>
                            <td style="text-align: left;">
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn" style="background-color: var(--admin-info); color: white;"><i class="fas fa-edit"></i></a>
                                <a href="handler.php?action=delete&id=<?php echo $product['id']; ?>" class="btn btn-danger delete-btn"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const style = getComputedStyle(document.body);

    <?php if ($flash_message): ?>
    Swal.fire({
        title: '<?php echo $flash_message["type"] === "success" ? "موفق" : "خطا"; ?>',
        html: '<?php echo addslashes($flash_message["message"]); ?>',
        icon: '<?php echo $flash_message["type"]; ?>',
        confirmButtonText: 'باشه',
        background: style.getPropertyValue('--admin-surface'),
        color: style.getPropertyValue('--admin-text')
    });
    <?php endif; ?>

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            Swal.fire({
                title: 'آیا مطمئن هستید؟',
                text: "این عمل غیرقابل بازگشت است!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: style.getPropertyValue('--admin-danger'),
                cancelButtonColor: style.getPropertyValue('--admin-info'),
                confirmButtonText: 'بله، حذف کن!',
                cancelButtonText: 'انصراف',
                background: style.getPropertyValue('--admin-surface'),
                color: style.getPropertyValue('--admin-text')
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>