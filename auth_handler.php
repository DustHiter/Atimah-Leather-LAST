<?php
session_start();
require_once __DIR__ . '/db/config.php';
require_once __DIR__ . '/mail/MailService.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'send_otp':
        handle_send_otp();
        break;
    case 'verify_otp':
        handle_verify_otp();
        break;
    case 'logout':
        handle_logout();
        break;
    default:
        header('Location: index.php');
        exit;
}

function handle_send_otp() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: login.php');
        exit;
    }

    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    if (!$email) {
        flash_message('danger', 'لطفاً یک آدرس ایمیل معتبر وارد کنید.', 'login.php');
    }

    try {
        $pdo = db();

        // Generate a secure random code
        $otp_code = random_int(100000, 999999);
        $code_hash = password_hash((string)$otp_code, PASSWORD_DEFAULT);
        
        // OTP is valid for 10 minutes
        $expires_at = date('Y-m-d H:i:s', time() + (10 * 60));

        // Store the hashed code in the database
        $stmt = $pdo->prepare("INSERT INTO otp_codes (email, code_hash, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $code_hash, $expires_at]);

        // Send the plain code to the user's email
        $subject = "کد ورود شما به فروشگاه آتیمه";
        $body = "<div dir='rtl' style='font-family: Vazirmatn, sans-serif; text-align: right;'><h2>کد تایید شما</h2><p>برای ورود یا ثبت‌نام در وب‌سایت آتیمه، از کد زیر استفاده کنید:</p><p style='font-size: 24px; font-weight: bold; letter-spacing: 5px; text-align: center; background: #f0f0f0; padding: 10px; border-radius: 8px;'>{$otp_code}</p><p>این کد تا ۱۰ دقیقه دیگر معتبر است.</p></div>";
        
        $mail_result = MailService::sendMail($email, $subject, $body);

        if (!$mail_result['success']) {
            error_log('OTP Mail Error: ' . ($mail_result['error'] ?? 'Unknown error'));
            flash_message('danger', 'خطایی در ارسال ایمیل رخ داد. لطفاً مطمئن شوید ایمیل را درست وارد کرده‌اید.', 'login.php');
        }

        // Store email in session to use on the verification page
        $_SESSION['otp_email'] = $email;
        header('Location: verify.php');
        exit;

    } catch (Exception $e) {
        error_log('OTP Generation Error: ' . $e->getMessage());
        flash_message('danger', 'خطای سرور. لطفاً لحظاتی دیگر دوباره تلاش کنید.', 'login.php');
    }
}

function handle_verify_otp() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: login.php');
        exit;
    }

    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $otp_code = trim($_POST['otp_code'] ?? '');

    if (!$email || !$otp_code) {
        flash_message('danger', 'ایمیل یا کد تایید نامعتبر است.', 'login.php');
    }

    try {
        $pdo = db();
        
        // Find the latest, unused OTP for this email that has not expired
        $stmt = $pdo->prepare("SELECT * FROM otp_codes WHERE email = ? AND is_used = 0 AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$email]);
        $otp_row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($otp_row && password_verify($otp_code, $otp_row['code_hash'])) {
            // Mark OTP as used
            $stmt_update = $pdo->prepare("UPDATE otp_codes SET is_used = 1 WHERE id = ?");
            $stmt_update->execute([$otp_row['id']]);

            // Check if user exists
            $stmt_user = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt_user->execute([$email]);
            $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

            $user_id = null;
            if ($user) {
                // User exists, log them in
                $user_id = $user['id'];
                $_SESSION['user_name'] = $user['first_name']; // Might be null, that's ok
            } else {
                // User does not exist, create a new one
                $stmt_create = $pdo->prepare("INSERT INTO users (email, is_admin) VALUES (?, 0)");
                $stmt_create->execute([$email]);
                $user_id = $pdo->lastInsertId();
                $_SESSION['user_name'] = null; // New user has no name yet
            }
            
            // Set session variables for login
            $_SESSION['user_id'] = $user_id;
            unset($_SESSION['otp_email']); // Clean up session
            
            // Redirect to homepage with success
            flash_message('success', 'شما با موفقیت وارد شدید!', 'index.php');

        } else {
            // Invalid or expired OTP
            flash_message('danger', 'کد وارد شده اشتباه یا منقضی شده است.', 'verify.php');
        }

    } catch (Exception $e) {
        error_log('OTP Verification Error: ' . $e->getMessage());
        flash_message('danger', 'خطای سرور. لطفاً لحظاتی دیگر دوباره تلاش کنید.', 'verify.php');
    }
}

function handle_logout() {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

function flash_message($type, $message, $location) {
    // Ensure email is carried over to verify page on error
    if ($location === 'verify.php' && isset($_POST['email'])) {
        $_SESSION['otp_email'] = $_POST['email'];
    }
    $_SESSION['flash_message'] = ['type' => $type, 'message' => $message];
    header("Location: $location");
    exit;
}