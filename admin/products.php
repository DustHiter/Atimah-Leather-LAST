<?php
session_start();
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../db/config.php';

// New header - includes nav, head, and opening body/main tags
require_once __DIR__ . '/header.php';

try {
    $pdo = db();
    $stmt = $pdo->query("SELECT id, name, price FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In a real app, log this error instead of just dying
    die("Error fetching products: " . $e->getMessage());
}

$flash_message = $_SESSION['flash_message'] ?? null;
if ($flash_message) {
    unset($_SESSION['flash_message']);
}

?>

<!-- Page Title and Action Button -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">مدیریت محصولات</h1>
    <a href="add_product.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> افزودن محصول
    </a>
</div>

<!-- Products Table -->
<div class="card shadow-sm" style="background-color: var(--admin-surface); border-color: var(--admin-border);">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-dark table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">نام محصول</th>
                        <th scope="col">قیمت</th>
                        <th scope="col" class="text-end">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4">هیچ محصولی یافت نشد.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <th scope="row"><?php echo htmlspecialchars($product['id']); ?></th>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo number_format($product['price']); ?> تومان</td>
                                <td class="text-end">
                                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-pencil-fill"></i> ویرایش
                                    </a>
                                    <a href="handler.php?action=delete&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger delete-btn">
                                        <i class="bi bi-trash-fill"></i> حذف
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Page-specific scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Flash message handling
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

        // Delete confirmation
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                Swal.fire({
                    title: 'آیا مطمئن هستید؟',
                    text: "این عمل غیرقابل بازگشت است!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: 'var(--admin-danger)',
                    cancelButtonColor: 'var(--admin-info)',
                    confirmButtonText: 'بله، حذف کن!',
                    cancelButtonText: 'انصراف',
                    background: 'var(--admin-surface)',
                    color: 'var(--admin-text-primary)'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            });
        });
    });
</script>

<?php
// New footer - includes closing tags
require_once __DIR__ . '/footer.php';
?>
