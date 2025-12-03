<?php
session_start();
require_once __DIR__ . '/auth_check.php';

// New header
require_once __DIR__ . '/header.php';

$flash_message = $_SESSION['flash_message'] ?? null;
if ($flash_message) {
    unset($_SESSION['flash_message']);
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
             <h1 class="h2">افزودن محصول جدید</h1>
             <a href="products.php" class="btn btn-secondary">انصراف</a>
        </div>

        <div class="card shadow-sm" style="background-color: var(--admin-surface); border-color: var(--admin-border);">
            <div class="card-body p-4">
                <form action="handler.php?action=add" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">نام محصول</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">توضیحات</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">قیمت (تومان)</label>
                            <input type="number" class="form-control" id="price" name="price" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="image" class="form-label">تصویر محصول</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="colors" class="form-label">کدهای رنگ (اختیاری)</label>
                        <input type="text" class="form-control" id="colors" name="colors" placeholder="مثال: #8B4513, #2C2C2C">
                        <div class="form-text" style="color: var(--admin-text-secondary);">کدهای رنگ هگزادسیمال را با کاما جدا کنید.</div>
                    </div>
                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1" style="border-color: var(--admin-border);">
                        <label class="form-check-label" for="is_featured">این یک محصول ویژه است</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">افزودن محصول</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Page-specific scripts -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    <?php if ($flash_message): ?>
    Swal.fire({
        title: '<?php echo $flash_message["type"] === "success" ? "عالی" : "خطا"; ?>',
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
