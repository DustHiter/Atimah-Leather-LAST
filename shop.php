<?php
$page_title = 'فروشگاه';
require_once 'includes/header.php';
require_once 'db/config.php';

// Fetch all products from the database
try {
    $pdo = db();
    $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $products = [];
    $db_error = "خطا در بارگذاری محصولات. لطفا بعدا تلاش کنید.";
}
?>

<main class="container py-5">
    <div class="text-center mb-5" data-aos="fade-down">
        <h1 class="display-4 fw-bold">مجموعه کامل محصولات</h1>
        <p class="fs-5">دست‌سازه‌هایی از چرم طبیعی، با عشق و دقت.</p>
    </div>

    <?php if (!empty($db_error)): ?>
        <div class="alert alert-danger">
            <?php echo $db_error; ?>
        </div>
    <?php endif; ?>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 g-lg-5">
        <?php
        if (!empty($products)) {
            $delay = 0;
            foreach ($products as $product) {
                echo '<div class="col" data-aos="fade-up" data-aos-delay="' . $delay . '">';
                echo '    <div class="product-card h-100">';
                echo '        <div class="product-image">';
                echo '            <a href="product.php?id=' . htmlspecialchars($product['id']) . '">';
                echo '                <img src="' . htmlspecialchars($product['image_url']) . '" class="img-fluid" alt="' . htmlspecialchars($product['name']) . '">';
                echo '            </a>';
                echo '        </div>';
                echo '        <div class="product-info text-center">';
                echo '            <h3 class="product-title"><a href="product.php?id=' . htmlspecialchars($product['id']) . '" class="text-decoration-none">' . htmlspecialchars($product['name']) . '</a></h3>';
                echo '            <p class="product-price">' . number_format($product['price']) . ' تومان</p>';
                echo '        </div>';
                echo '    </div>';
                echo '</div>';
                $delay = ($delay + 100) % 400; // Stagger animation delay
            }
        } else if (empty($db_error)) {
            echo '<div class="col-12"><p class="text-center text-white-50 fs-4">در حال حاضر محصولی برای نمایش وجود ندارد.</p></div>';
        }
        ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>