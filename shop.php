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

<main class="container section-padding">
    <div class="text-center" data-aos="fade-down">
        <h1 class="section-title">مجموعه کامل محصولات</h1>
        <p class="fs-5 text-muted">دست‌سازه‌هایی از چرم طبیعی، با عشق و دقت.</p>
    </div>

    <?php if (!empty($db_error)): ?>
        <div class="alert alert-danger">
            <?= $db_error; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($products) && empty($db_error)): ?>
        <div class="col-12">
            <p class="text-center text-muted fs-4">در حال حاضر محصولی برای نمایش وجود ندارد.</p>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 g-lg-5 mt-5">
            <?php 
            $delay = 0;
            foreach ($products as $product): 
            ?>
                <div class="col" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                    <div class="card product-card h-100">
                        <div class="product-image">
                            <a href="product.php?id=<?= htmlspecialchars($product['id']) ?>">
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                            </a>
                        </div>
                        <div class="product-info text-center">
                            <h3 class="product-title">
                                <a href="product.php?id=<?= htmlspecialchars($product['id']) ?>">
                                    <?= htmlspecialchars($product['name']) ?>
                                </a>
                            </h3>
                            <p class="product-price"><?= number_format($product['price']) ?> تومان</p>
                        </div>
                    </div>
                </div>
            <?php 
                $delay = ($delay + 100) % 400; // Stagger animation delay
            endforeach; 
            ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>
