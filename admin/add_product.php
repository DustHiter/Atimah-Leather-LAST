<?php
session_start();
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/header.php';

$flash_message = $_SESSION['flash_message'] ?? null;
if ($flash_message) {
    unset($_SESSION['flash_message']);
}
?>

<style>
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}
</style>

<div class="admin-header">
    <h1>افزودن محصول جدید</h1>
    <a href="products.php" class="btn" style="background: var(--admin-border); color: var(--admin-text);">بازگشت</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="handler.php?action=add" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name" class="form-label">نام محصول</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">توضیحات</label>
                <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="price" class="form-label">قیمت (تومان)</label>
                    <input type="number" class="form-control" id="price" name="price" required>
                </div>
                <div class="form-group">
                    <label for="stock" class="form-label">موجودی</label>
                    <input type="number" class="form-control" id="stock" name="stock" required value="0">
                </div>
            </div>

            <div class="form-group">
                <label for="image" class="form-label">تصویر محصول</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
            </div>

            <div class="form-group">
                <label for="colors" class="form-label">کدهای رنگ (اختیاری)</label>
                <input type="text" class="form-control" id="colors" name="colors" placeholder="مثال: #8B4513, #2C2C2C">
                <small style="color: var(--admin-text-muted);">کدهای رنگ هگزادسیمال را با کاما جدا کنید.</small>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="is_featured" name="is_featured" value="1" style="width: 20px; height: 20px;">
                    <span>این یک محصول ویژه است</span>
                </label>
            </div>

            <div style="text-align: left; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">افزودن محصول</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const style = getComputedStyle(document.body);
    <?php if ($flash_message): ?>
    Swal.fire({
        title: '<?php echo $flash_message["type"] === "success" ? "عالی" : "خطا"; ?>',
        html: '<?php echo addslashes($flash_message["message"]); ?>',
        icon: '<?php echo $flash_message["type"]; ?>',
        confirmButtonText: 'باشه',
        background: style.getPropertyValue('--admin-surface'),
        color: style.getPropertyValue('--admin-text')
    });
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>