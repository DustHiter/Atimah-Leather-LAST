<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$page_title = "ورود یا ثبت‌نام";
// We don't include the standard header/footer as this is a standalone page design
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title; ?> - آتیمه</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Remixicon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <!-- Main Custom CSS (for variables) -->
    <link rel="stylesheet" href="assets/css/theme.css?v=<?= time(); ?>">
</head>
<body class="dark-luxury">

    <div class="auth-wrapper">
        <!-- Left Side: Background Image and Branding -->
        <div class="auth-bg">
            <div class="auth-bg-content">
                <h1>به خانه چرم بازگردید</h1>
                <p>اصالت و زیبایی در دستان شما. برای ورود یا ساخت حساب کاربری، ایمیل خود را وارد کنید.</p>
            </div>
        </div>

        <!-- Right Side: Form -->
        <div class="auth-form-wrapper">
            <div class="auth-form-container">
                <div class="form-header">
                    <div class="logo">آتیمه</div>
                    <h2>ورود یا ثبت‌نام</h2>
                    <p>برای دریافت کد یکبار مصرف، ایمیل خود را وارد کنید.</p>
                </div>

                <?php if(isset($_SESSION['flash_message'])): ?>
                    <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_message']['type']); ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['flash_message']['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['flash_message']); ?>
                <?php endif; ?>

                <form action="auth_handler.php?action=send_otp" method="POST">
                    <div class="form-group">
                         <label for="email" class="form-label visually-hidden">ایمیل</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="ایمیل خود را وارد کنید" required>
                    </div>
                    
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">ادامه با ایمیل</button>
                    </div>
                </form>

                <div class="auth-footer">
                    <p><a href="index.php"><i class="ri-arrow-right-line"></i> بازگشت به صفحه اصلی</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
