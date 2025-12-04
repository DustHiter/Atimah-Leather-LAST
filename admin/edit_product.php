<?php
session_start();
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../db/config.php';

$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$product_id) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'شناسه محصول نامعتبر است.'];
    header('Location: products.php');
    exit;
}

try {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'محصول مورد نظر یافت نشد.'];
        header('Location: products.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'خطا در اتصال به پایگاه داده.'];
    header('Location: products.php');
    exit;
}

require_once __DIR__ . '/header.php';
?>

<style>
.form-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
}
.image-preview-container {
    background-color: var(--admin-bg);
    border: 1px dashed var(--admin-border);
    padding: 1rem;
    border-radius: 8px;
    text-align: center;
}
.image-preview {
    max-width: 100%;
    height: auto;
    max-height: 200px;
    border-radius: 8px;
    margin-bottom: 1rem;
}
</style>

<div class="admin-header">
    <h1>ویرایش محصول: <?php echo htmlspecialchars($product['name']); ?></h1>
    <a href="products.php" class="btn" style="background: var(--admin-border); color: var(--admin-text);">بازگشت</a>
</div>

<form action="handler.php?action=edit" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">
    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($product['image_url']); ?>">

    <div class="form-grid">
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label for="name" class="form-label">نام محصول</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">توضیحات</label>
                    <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label for="price" class="form-label">قیمت (تومان)</label>
                        <input type="number" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="stock" class="form-label">موجودی</label>
                        <input type="number" class="form-control" id="stock" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" required>
                    </div>
                </div>
                
                 <div class="form-group">
                    <label for="colors" class="form-label">کدهای رنگ (اختیاری)</label>
                    <input type="text" class="form-control" id="colors" name="colors" value="<?php echo htmlspecialchars($product['colors'] ?? ''); ?>" placeholder="مثال: #8B4513, #2C2C2C">
                    <small style="color: var(--admin-text-muted);">کدهای رنگ هگزادسیمال را با کاما جدا کنید.</small>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="is_featured" value="1" <?php echo ($product['is_featured'] ?? 0) ? 'checked' : ''; ?> style="width: 20px; height: 20px;">
                        <span>این یک محصول ویژه است</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="card">
             <div class="card-header">تصویر محصول</div>
            <div class="card-body">
                <div class="image-preview-container">
                    <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="Current Image" id="image-preview" class="image-preview">
                    <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                    <small style="color: var(--admin-text-muted); margin-top: 0.5rem; display: block;">برای تغییر، تصویر جدیدی انتخاب کنید.</small>
                </div>
            </div>
        </div>
    </div>

    <div style="text-align: left; margin-top: 2rem;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> ذخیره تغییرات</button>
    </div>
</form>

<script>
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = () => document.getElementById('image-preview').src = reader.result;
    if (event.target.files[0]) reader.readAsDataURL(event.target.files[0]);
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>