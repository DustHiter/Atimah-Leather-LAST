<?php
session_start();
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../db/config.php';

// Sanitize and validate product ID
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
    error_log("Database Error: " . $e->getMessage());
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'خطا در اتصال به پایگاه داده. لطفاً بعداً تلاش کنید.'];
    header('Location: products.php');
    exit;
}

$page_title = "ویرایش محصول: " . htmlspecialchars($product['name']);
require_once 'header.php';
?>

<div class="page-header">
    <h1 class="page-title">ویرایش محصول</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">داشبورد</a></li>
            <li class="breadcrumb-item"><a href="products.php">مدیریت محصولات</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
        </ol>
    </nav>
</div>

<div class="card-container">
    <div class="card-header">
        <h5 class="card-title">فرم ویرایش محصول</h5>
        <a href="products.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>بازگشت
        </a>
    </div>
    <div class="card-body">
        <form action="handler.php?action=edit" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($product['image_url']); ?>">

            <div class="row">
                <!-- Main Product Info -->
                <div class="col-lg-8">
                    <div class="form-group mb-4">
                        <label for="name">نام محصول</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>

                    <div class="form-group mb-4">
                        <label for="description">توضیحات</label>
                        <textarea class="form-control" id="description" name="description" rows="6" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-4">
                                <label for="price">قیمت (تومان)</label>
                                <input type="number" class="form-control" id="price" name="price" min="0" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-4">
                                <label for="colors">کدهای رنگ هگز</label>
                                <input type="text" class="form-control" id="colors" name="colors" value="<?php echo htmlspecialchars($product['colors'] ?? ''); ?>">
                                <div class="form-text">رنگ‌ها را با کاما جدا کنید (مثال: #FFFFFF, #000000).</div>
                            </div>
                        </div>
                    </div>
                     <div class="form-check form-switch custom-switch mb-4">
                        <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1" <?php echo ($product['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_featured">محصول ویژه (نمایش در صفحه اصلی)</label>
                    </div>
                </div>

                <!-- Image Upload -->
                <div class="col-lg-4">
                    <div class="form-group mb-4">
                        <label for="image">تصویر محصول</label>
                        <div class="image-upload-wrapper text-center">
                            <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="Current Image" class="img-thumbnail mb-3" id="image-preview" style="max-width: 180px; height: auto;">
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                            <small class="form-text text-muted mt-2">برای تغییر، تصویر جدید را انتخاب کنید.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="products.php" class="btn btn-outline-secondary">انصراف</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>ذخیره تغییرات
                </button>
            </div>
        </form>
    </div>
</div>

<?php
require_once 'footer.php';
?>
<script>
    // Preview image before upload
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('image-preview');
            output.src = reader.result;
        };
        if (event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }
</script>
