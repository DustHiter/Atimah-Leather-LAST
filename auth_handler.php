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
    case 'google_login':
        handle_google_login();
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
    $phone = preg_match('/^09[0-9]{9}$/', trim($_POST['phone'] ?? '')) ? trim($_POST['phone']) : null;
    
    if (!$email && !$phone) {
        flash_message('danger', 'لطفاً یک ایمیل یا شماره تلفن معتبر وارد کنید.', 'login.php');
    }

    $identifier = $email ?: $phone;
    $login_method = $email ? 'email' : 'phone';

    try {
        $pdo = db();

        // Generate a secure random code
        $otp_code = random_int(100000, 999999);
        $code_hash = password_hash((string)$otp_code, PASSWORD_DEFAULT);
        
        // OTP is valid for 10 minutes
        $expires_at = date('Y-m-d H:i:s', time() + (10 * 60));

        // Store the hashed code in the database. Using the 'email' column for both for now.
        $stmt = $pdo->prepare("INSERT INTO otp_codes (email, code_hash, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$identifier, $code_hash, $expires_at]);

        if ($login_method === 'email') {
            // Send the plain code to the user's email
            $subject = "کد ورود شما به فروشگاه آتیمه";
            $body = "<div dir='rtl' style='font-family: Vazirmatn, sans-serif; text-align: right;'><h2>کد تایید شما</h2><p>برای ورود یا ثبت‌نام در وب‌سایت آتیمه، از کد زیر استفاده کنید:</p><p style='font-size: 24px; font-weight: bold; letter-spacing: 5px; text-align: center; background: #f0f0f0; padding: 10px; border-radius: 8px;'>{$otp_code}</p><p>این کد تا ۱۰ دقیقه دیگر معتبر است.</p></div>";
            
            $mail_result = MailService::sendMail($identifier, $subject, $body);

            if (!$mail_result['success']) {
                error_log('OTP Mail Error: ' . ($mail_result['error'] ?? 'Unknown error'));
                flash_message('danger', 'خطایی در ارسال ایمیل رخ داد. لطفاً مطمئن شوید ایمیل را درست وارد کرده‌اید.', 'login.php');
            }
        } else {
            // Phone login: Simulate sending OTP since there is no SMS gateway
            error_log("OTP for {$identifier}: {$otp_code}"); // Log for debugging
            // In a real application, you would integrate with an SMS service here.
            // For now, we will show a message that it's not implemented, but allow verification for testing.
             $_SESSION['show_otp_for_debugging'] = $otp_code; // Temporarily show OTP on verify page for testing
        }

        // Store identifier in session to use on the verification page
        $_SESSION['otp_identifier'] = $identifier;
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

    $identifier = trim($_POST['identifier'] ?? '');
    $otp_code = trim($_POST['otp_code'] ?? '');

    if (!$identifier || !$otp_code) {
        flash_message('danger', 'شناسه یا کد تایید نامعتبر است.', 'login.php');
    }

    $login_method = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

    try {
        $pdo = db();
        
        // Find the latest, unused OTP for this identifier that has not expired
        $stmt = $pdo->prepare("SELECT * FROM otp_codes WHERE email = ? AND is_used = 0 AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$identifier]);
        $otp_row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($otp_row && password_verify($otp_code, $otp_row['code_hash'])) {
            // Mark OTP as used
            $stmt_update = $pdo->prepare("UPDATE otp_codes SET is_used = 1 WHERE id = ?");
            $stmt_update->execute([$otp_row['id']]);

            // Check if user exists
            $column = $login_method === 'email' ? 'email' : 'phone';
            $stmt_user = $pdo->prepare("SELECT * FROM users WHERE $column = ?");
            $stmt_user->execute([$identifier]);
            $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

            $user_id = null;
            if ($user) {
                // User exists, log them in
                $user_id = $user['id'];
                $_SESSION['user_name'] = $user['first_name'];
            } else {
                // User does not exist, create a new one
                $stmt_create = $pdo->prepare("INSERT INTO users ($column, is_admin) VALUES (?, 0)");
                $stmt_create->execute([$identifier]);
                $user_id = $pdo->lastInsertId();
                $_SESSION['user_name'] = null;
            }
            
            // Set session variables for login
            $_SESSION['user_id'] = $user_id;
            unset($_SESSION['otp_identifier']);
            unset($_SESSION['show_otp_for_debugging']);
            
            flash_message('success', 'شما با موفقیت وارد شدید!', 'index.php');

        } else {
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
    // Ensure identifier is carried over to verify page on error
    if ($location === 'verify.php' && isset($_POST['identifier'])) {
        $_SESSION['otp_identifier'] = $_POST['identifier'];
    }
    $_SESSION['flash_message'] = ['type' => $type, 'message' => $message];
    header("Location: $location");
    exit;
}

function handle_google_login() {
    // Load Google credentials from .env
    $google_client_id = getenv('GOOGLE_CLIENT_ID');
    $google_client_secret = getenv('GOOGLE_CLIENT_SECRET');
    
    // The redirect URI must be the exact same one configured in your Google Cloud project
    $redirect_uri = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . strtok($_SERVER["REQUEST_URI"], '?') . '?action=google_login';

    if (empty($google_client_id) || empty($google_client_secret)) {
        flash_message('danger', 'قابلیت ورود با گوگل هنوز پیکربندی نشده است.', 'login.php');
    }

    // If 'code' is not in the query string, this is the initial request. Redirect to Google.
    if (!isset($_GET['code'])) {
        $auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id' => $google_client_id,
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
            'access_type' => 'online',
            'prompt' => 'select_account'
        ]);
        header('Location: ' . $auth_url);
        exit;
    } 
    // If 'code' is present, this is the callback from Google.
    else {
        try {
            // Step 1: Exchange authorization code for an access token
            $token_url = 'https://oauth2.googleapis.com/token';
            $token_data = [
                'code' => $_GET['code'],
                'client_id' => $google_client_id,
                'client_secret' => $google_client_secret,
                'redirect_uri' => $redirect_uri,
                'grant_type' => 'authorization_code'
            ];

            $token_response = curl_request($token_url, 'POST', $token_data);
            
            if (!isset($token_response['access_token'])) {
                throw new Exception("Failed to get access token from Google. Response: " . json_encode($token_response));
            }
            
            // Step 2: Use access token to get user's profile information
            $userinfo_url = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $token_response['access_token'];
            $userinfo = curl_request($userinfo_url);

            if (!isset($userinfo['email'])) {
                throw new Exception("Failed to get user info from Google. Response: " . json_encode($userinfo));
            }

            // Step 3: Log in or create user
            $email = filter_var($userinfo['email'], FILTER_VALIDATE_EMAIL);
            $first_name = $userinfo['given_name'] ?? '';
            $last_name = $userinfo['family_name'] ?? '';

            $pdo = db();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $user_id = null;
            if ($user) {
                // User exists, log them in
                $user_id = $user['id'];
                // Update name if it's missing
                if (empty($user['first_name']) && !empty($first_name)) {
                    $stmt_update = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?");
                    $stmt_update->execute([$first_name, $last_name, $user_id]);
                }
            } else {
                // User does not exist, create a new one
                $stmt_create = $pdo->prepare("INSERT INTO users (email, first_name, last_name, is_admin) VALUES (?, ?, ?, 0)");
                $stmt_create->execute([$email, $first_name, $last_name]);
                $user_id = $pdo->lastInsertId();
            }

            // Set session variables for login
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $first_name;
            unset($_SESSION['otp_email']); // Clean up OTP session if it exists

            flash_message('success', 'شما با موفقیت با حساب گوگل وارد شدید!', 'index.php');

        } catch (Exception $e) {
            error_log('Google Login Error: ' . $e->getMessage());
            flash_message('danger', 'خطایی در فرآیند ورود با گوگل رخ داد. لطفاً دوباره تلاش کنید.', 'login.php');
        }
    }
}

function curl_request($url, $method = 'GET', $data = []) {
    $ch = curl_init();
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    // Set a common user agent
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        throw new Exception("cURL Error: " . $error_msg);
    }

    curl_close($ch);
    return json_decode($response, true);
}