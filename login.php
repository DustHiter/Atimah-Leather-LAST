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
    <title><?php echo $page_title; ?> - آتیمه</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Vazirmatn Font -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <!-- Main Custom CSS (for variables) -->
    <link rel="stylesheet" href="assets/css/custom.css?v=<?php echo time(); ?>">
    <!-- Custom Auth CSS -->
    <link rel="stylesheet" href="assets/css/auth_style.css?v=<?php echo time(); ?>">
</head>
<body>

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
                <div class="form-header text-center">
                    <h2>ورود یا ثبت‌نام</h2>
                    <p>برای دریافت کد یکبار مصرف، ایمیل خود را وارد کنید.</p>
                </div>

                <?php if(isset($_SESSION['flash_message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
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

                <div class="separator my-4"><span>یا</span></div>

                <div class="d-grid">
                    <a href="auth_handler.php?action=google_login" class="btn btn-google">
                        <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/><path fill="none" d="M0 0h48v48H0z"/></svg>
                        ورود با گوگل
                    </a>
                </div>
                
                <div class="auth-footer">
                    <p><a href="index.php">بازگشت به صفحه اصلی</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>