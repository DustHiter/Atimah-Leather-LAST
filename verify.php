<?php
session_start();

// Redirect if identifier is not in session
if (!isset($_SESSION['otp_identifier'])) {
    header('Location: login.php');
    exit;
}

$identifier_for_display = htmlspecialchars($_SESSION['otp_identifier']);
$page_title = "تایید کد یکبار مصرف";

// For debugging phone OTPs without an SMS service
$debug_otp = $_SESSION['show_otp_for_debugging'] ?? null;

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
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/theme.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/custom.css?v=<?= time(); ?>">
    <style>
        /* Using styles from the modern login page for consistency */
        body { 
            background-color: var(--color-dark-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
    </style>
</head>
<body class="login-page-modern">

    <main class="login-container">
        <div class="login-form-wrapper">
            <div class="login-header text-center mb-4">
                <a href="index.php" class="logo-link"><h1 class="logo-title h2">آتیمه</h1></a>
                <p class="tagline">فقط یک قدم دیگر...</p>
            </div>
            
            <h2 class="form-title text-center mb-4">تایید کد</h2>
            <p class="text-center text-secondary mb-4">کد ۶ رقمی ارسال شده به <strong class="d-block mt-1"><?= $identifier_for_display; ?></strong> را وارد کنید.</p>

            <?php if(isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_message']['type']); ?> alert-dismissible fade show my-3" role="alert">
                    <?= htmlspecialchars($_SESSION['flash_message']['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>

            <?php if ($debug_otp): ?>
                <div class="alert alert-warning">
                    <strong>حالت آزمایشی:</strong> سرویس پیامک فعال نیست. کد شما: <strong><?= htmlspecialchars($debug_otp); ?></strong>
                </div>
            <?php endif; ?>

            <form action="auth_handler.php?action=verify_otp" method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="identifier" value="<?= $identifier_for_display; ?>">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="otp_code" name="otp_code" placeholder="کد تایید" required pattern="\d{6}" maxlength="6" autocomplete="one-time-code" style="text-align: center; font-size: 1.5rem; letter-spacing: 0.5rem;">
                    <label for="otp_code">کد تایید</label>
                    <div class="invalid-feedback">
                        کد تایید باید یک عدد ۶ رقمی باشد.
                    </div>
                </div>
                
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">تایید و ورود</button>
                </div>
            </form>

            <div class="auth-footer text-center mt-4">
                <p><a href="login.php"><i class="ri-arrow-right-line align-middle"></i> بازگشت و اصلاح</a></p>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
