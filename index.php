<?php 
$page_title = 'صفحه اصلی';
include 'includes/header.php'; 


// Load dynamic content
$about_us_image_data = json_decode(file_get_contents('about_us_image.json'), true);
$about_us_image_url = $about_us_image_data ? str_replace('\\/', '/', $about_us_image_data['local_path']) : 'assets/images/pexels/about-us-34942790.jpg';

require_once 'db/config.php';
?>

<!-- Hero Section -->
<section class="hero-section vh-100 d-flex align-items-center">
    <div class="video-background-wrapper">
        <div class="video-overlay"></div>
        <video playsinline="playsinline" autoplay="autoplay" muted="muted" loop="loop">
            <source src="https://static.pexels.com/lib/videos/free-videos.mp4" type="video/mp4">
        </video>
    </div>
    <div class="container position-relative text-center">
        <h1 class="display-3 hero-title" data-aos="zoom-in-out" data-aos-delay="100">اصالت در هر نگاه</h1>
        <p class="lead fs-4 mb-4 hero-subtitle" data-aos="fade-up" data-aos-delay="300">محصولات چرمی دست‌دوز، آفریده برای ماندگاری.</p>
        <a href="shop.php" class="btn btn-primary btn-lg" data-aos="fade-up" data-aos-delay="500">کاوش در مجموعه</a>
    </div>
</section>


<!-- Featured Products Section -->
<section id="featured-products" class="section-padding">
    <div class="container">
        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $_SESSION['success_message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $_SESSION['error_message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['error_message']);
        }
        ?>
        <div class="section-title text-center" data-aos="fade-up">
            <h1>مجموعه برگزیده ما</h1>
            <p class="fs-5 text-muted">دست‌چین شده برای سلیقه‌های خاص.</p>
        </div>
        
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 g-lg-5">
            <?php
            try {
                $pdo = db();
                $stmt = $pdo->query("SELECT * FROM products WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 3");
                $featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($featured_products)) {
                    echo '<div class="col-12"><p class="text-center text-muted">هیچ محصولی برای نمایش وجود ندارد.</p></div>';
                } else {
                    $delay = 0;
                    foreach ($featured_products as $product) {
            ?>
            <div class="col" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                <div class="product-card h-100">
                    <div class="product-image">
                        <a href="product.php?id=<?= $product['id'] ?>">
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        </a>
                    </div>
                    <div class="product-info text-center">
                        <h3 class="product-title"><a href="product.php?id=<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a></h3>
                        <p class="product-price"><?= number_format($product['price']) ?> تومان</p>
                    </div>
                </div>
            </div>
            <?php
                        $delay += 150;
                    }
                }
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                echo '<div class="col-12"><p class="text-center text-danger">خطا در بارگذاری محصولات.</p></div>';
            }
            ?>
        </div>

         <div class="text-center mt-5" data-aos="fade-up">
            <a href="shop.php" class="btn btn-primary">مشاهده تمام محصولات</a>
        </div>
    </div>
</section>

<!-- About Us Section -->
<section id="about-us" class="section-padding bg-surface">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-md-6" data-aos="fade-right">
                <img src="<?= htmlspecialchars($about_us_image_url) ?>" alt="درباره ما" class="about-us-image img-fluid">
            </div>
            <div class="col-md-6" data-aos="fade-left">
                <div class="section-title text-md-end text-start">
                     <h1>داستان آتیمه</h1>
                </div>
                <p class="text-muted fs-5 mt-3 text-md-end text-start">ما در آتیمه، به تلفیق هنر سنتی و طراحی مدرن باور داریم. هر محصول، حاصل ساعت‌ها کار دست هنرمندان ماهر و استفاده از بهترین چرم‌های طبیعی است. هدف ما خلق آثاری است که نه تنها یک وسیله، بلکه بخشی از داستان و استایل شما باشند.</p>
                <div class="text-md-end text-start">
                    <a href="about.php" class="btn btn-primary mt-3" data-aos="fade-up" data-aos-delay="200">بیشتر بدانید</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
