<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log page views for non-admin pages
if (strpos($_SERVER['REQUEST_URI'], '/admin/') === false) {
    require_once __DIR__ . '/../db/config.php';
    try {
        $pdo = db();
        // Check if the table exists to avoid errors before migration
        $table_check = $pdo->query("SHOW TABLES LIKE 'page_views'");
        if ($table_check->rowCount() > 0) {
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $page_url = $_SERVER['REQUEST_URI'];
            $stmt = $pdo->prepare("INSERT INTO page_views (page_url, ip_address) VALUES (?, ?)");
            $stmt->execute([$page_url, $ip_address]);
        }
    } catch (PDOException $e) {
        // Silently fail or log to a file to not break the page for users
        error_log("Could not log page view: " . $e->getMessage());
    }
}

$cart_item_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
$page_title = $page_title ?? 'فروشگاه آتیمه'; // Default title

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($_SERVER['PROJECT_DESCRIPTION'] ?? 'خرید محصولات چرمی لوکس و با کیفیت.'); ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Variable Overrides for Dark Luxury Theme -->
    <style>
        :root {
            --luxury-bg: #111214;
            --luxury-surface: #1a1b1e;
            --luxury-text: #eceff1;
            --luxury-text-muted: #90a4ae;
            --luxury-primary: #c09f80;
            --luxury-border: #37474f;

            /* Override Bootstrap's root variables */
            --bs-body-bg: var(--luxury-bg);
            --bs-body-color: var(--luxury-text);
            --bs-primary: var(--luxury-primary);
            --bs-primary-rgb: 192, 159, 128;
            --bs-link-color: var(--luxury-primary);
            --bs-link-hover-color: #d4b090; /* A lighter gold for hover */
            --bs-border-color: var(--luxury-border);
            --bs-tertiary-bg: var(--luxury-surface);
        }
    </style>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Remix Icon CSS -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    
    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/custom.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/css/dark_luxury.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script>
        // Apply theme from local storage before page load to prevent flashing
        (function() {
            const theme = localStorage.getItem('theme') || 'dark';
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

</head>
<body class="dark-luxury">
    <div class="overflow-hidden">
    <?php
    $is_admin_page = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
    ?>

<header class="site-header sticky-top py-3">
    <nav class="navbar navbar-expand-lg container">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4" href="index.php">آتیمه</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">خانه</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="shop.php">فروشگاه</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">درباره ما</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">تماس با ما</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center">
                    <a href="cart.php" class="ms-4 position-relative">
                        <i class="ri-shopping-bag-line fs-5"></i>
                        <?php if ($cart_item_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $cart_item_count; ?>
                                <span class="visually-hidden">محصول در سبد خرید</span>
                            </span>
                        <?php endif; ?>
                    </a>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="dropdown ms-3">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-user-line fs-5 me-1"></i>
                                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end text-small shadow" aria-labelledby="dropdownUser">
                                <li><a class="dropdown-item" href="profile.php">حساب کاربری</a></li>
                                <?php if (!empty($_SESSION['is_admin'])): ?>
                                    <li><a class="dropdown-item" href="/admin/index.php">پنل مدیریت</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">خروج</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary btn-sm ms-3">ورود / ثبت‌نام</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>

<main>