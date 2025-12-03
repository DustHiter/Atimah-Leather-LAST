<?php
require_once 'db/config.php';
require_once 'includes/header.php';

$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$product = null;
$db_error = '';

if (!$product_id) {
    // Redirect or show error if ID is not valid
    header("Location: shop.php");
    exit;
}

try {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $db_error = "<p>خطا در برقراری ارتباط با پایگاه داده.</p>";
}

// If product not found, show a message and stop
if (!$product) {
    echo '<main class="container py-5 text-center"><div class="alert alert-danger">محصولی با این شناسه یافت نشد.</div><div><a href="shop.php" class="btn btn-primary mt-3">بازگشت به فروشگاه</a></div></main>';
    require_once 'includes/footer.php';
    exit;
}

// Set page title after fetching product name
$page_title = htmlspecialchars($product['name']);

// Safely decode colors JSON
$available_colors = json_decode($product['colors'] ?? '[]', true);
if (json_last_error() !== JSON_ERROR_NONE) {
    $available_colors = []; // Assign empty array if JSON is invalid
}

?>

<main class="container py-5">
    <div class="row g-5">
        <div class="col-lg-6" data-aos="fade-right">
            <div class="product-image-gallery">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="img-fluid rounded-4 shadow-lg" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
        </div>

        <div class="col-lg-6" data-aos="fade-left">
            <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div class="d-flex align-items-center mb-4">
                <p class="display-6 text-primary fw-bold m-0"><?php echo number_format($product['price']); ?> تومان</p>
            </div>

            <p class="fs-5 mb-4"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

            <form action="cart_handler.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <input type="hidden" name="action" value="add">

                <?php if (!empty($available_colors)): ?>
                    <div class="mb-4">
                        <h5 class="mb-3">انتخاب رنگ:</h5>
                        <div class="color-swatches">
                            <?php foreach ($available_colors as $index => $color_hex): ?>
                                <input type="radio" class="btn-check" name="product_color" id="color_<?php echo $index; ?>" value="<?php echo htmlspecialchars($color_hex); ?>" <?php echo ($index === 0) ? 'checked' : ''; ?>>
                                <label class="btn" for="color_<?php echo $index; ?>" style="background-color: <?php echo htmlspecialchars($color_hex); ?>;" title="<?php echo htmlspecialchars($color_hex); ?>"></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="row align-items-center mb-4">
                    <div class="col-md-5 col-lg-4">
                         <label for="quantity" class="form-label fw-bold">تعداد:</label>
                        <input type="number" name="quantity" id="quantity" class="form-control form-control-lg bg-dark text-center" value="1" min="1" max="10">
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-shopping-cart me-2"></i> افزودن به سبد خرید</button>
                </div>
            </form>

        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>