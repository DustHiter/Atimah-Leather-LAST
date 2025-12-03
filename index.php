<?php 
$page_title = 'صفحه اصلی';
include 'includes/header.php'; 
?>

        <!-- Hero Section -->
        <section class="hero-section vh-100 d-flex align-items-center" style="background: #111;"> 
            <div class="video-background-wrapper">
                 <div class="video-overlay"></div>
                 <video playsinline="playsinline" autoplay="autoplay" muted="muted" loop="loop" style="opacity: 0.5;">
                    <source src="https://static.pexels.com/lib/videos/free-videos.mp4" type="video/mp4">
                </video>
            </div>
            <div class="container position-relative">
                <h1 class="display-3 fw-bold mb-3 hero-title" data-aos="zoom-in-out" data-aos-delay="100">اصالت در هر نگاه</h1>
                <p class="lead fs-4 mb-4 hero-subtitle" data-aos="fade-left" data-aos-delay="300">محصولات چرمی دست‌دوز، آفریده برای ماندگاری.</p>
                <a href="shop.php" class="btn btn-primary btn-lg" data-aos="fade-up" data-aos-delay="500">کاوش در مجموعه</a>
            </div>
        </section>


        <!-- Featured Products Section -->
        <section id="featured-products" class="py-5">
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
                <div class="text-center mb-5" data-aos="fade-up">
                    <h2 class="display-5 fw-bold">مجموعه برگزیده ما</h2>
                    <p class="fs-5">دست‌چین شده برای سلیقه‌های خاص.</p>
                </div>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 g-lg-5">
                    <?php
                    require_once 'db/config.php';
                    try {
                        $pdo = db();
                        $stmt = $pdo->query("SELECT * FROM products WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 3");
                        $featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        $animations = ['fade-up', 'zoom-in-up', 'fade-left'];
                        if (empty($featured_products)) {
                            echo '<div class="col-12"><p class="text-center text-white-50">هیچ محصولی برای نمایش وجود ندارد.</p></div>';
                        } else {
                            $delay = 0;
                            foreach ($featured_products as $key => $product) {
                                echo '<div class="col" data-aos="fade-up" data-aos-delay="' . $delay . '">';
                                echo '    <div class="product-card h-100">';
                                echo '        <div class="product-image">';
                                echo '            <a href="product.php?id=' . $product['id'] . '">';
                                echo '                <img src="' . htmlspecialchars($product['image_url']) . '" class="img-fluid" alt="' . htmlspecialchars($product['name']) . '">';
                                echo '            </a>';
                                echo '        </div>';
                                echo '        <div class="product-info text-center">';
                                echo '            <h3 class="product-title"><a href="product.php?id=' . $product['id'] . '" class="text-decoration-none">' . htmlspecialchars($product['name']) . '</a></h3>';
                                echo '            <p class="product-price">' . number_format($product['price']) . ' تومان</p>';
                                echo '        </div>';
                                echo '    </div>';
                                echo '</div>';
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
                    <a href="shop.php" class="btn btn-outline-gold btn-lg">مشاهده تمام محصولات</a>
                </div>
            </div>
        </section>

        <!-- About Us Section -->
        <section id="about-us" class="py-5">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6" data-aos="fade-right">
                        <img src="assets/images/pexels/about-us-34942790.jpg" alt="درباره ما" class="img-fluid rounded-4 shadow-lg">
                    </div>
                    <div class="col-md-6 mt-4 mt-md-0 ps-md-5" data-aos="fade-left">
                        <h2 class="display-5 fw-bold">داستان آتیمه</h2>
                        <p class="text-muted fs-5 mt-3">ما در آتیمه، به تلفیق هنر سنتی و طراحی مدرن باور داریم. هر محصول، حاصل ساعت‌ها کار دست هنرمندان ماهر و استفاده از بهترین چرم‌های طبیعی است. هدف ما خلق آثاری است که نه تنها یک وسیله، بلکه بخشی از داستان و استایل شما باشند.</p>
                        <a href="#" class="btn btn-primary mt-3" data-aos="fade-up" data-aos-delay="200">بیشتر بدانید</a>
                    </div>
                </div>
            </div>
        </section>

<?php include 'includes/footer.php'; ?>