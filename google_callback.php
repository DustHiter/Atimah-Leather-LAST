<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'db/config.php';

// Check if the user has a temporary identifier from the initial login, and clear it.
if (isset($_SESSION['otp_identifier'])) {
    unset($_SESSION['otp_identifier']);
}

$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URL);
$client->addScope("email");
$client->addScope("profile");

// Handle the OAuth 2.0 server response
if (isset($_GET['code'])) {
    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        if (isset($token['error'])) {
            throw new Exception('Google auth error: ' . $token['error_description']);
        }
        $client->setAccessToken($token['access_token']);

        // Get user profile information
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        $email = $google_account_info->email;
        $name = $google_account_info->name;

        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // User exists, log them in
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['is_admin'] = $user['is_admin'];
        } else {
            // User does not exist, create a new one
            // A null password is used as authentication is managed by Google.
            $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password, is_admin, created_at) VALUES (?, ?, NULL, 0, NOW())");
            $insertStmt->execute([$name, $email]);
            $newUserId = $pdo->lastInsertId();

            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user_name'] = $name;
            $_SESSION['is_admin'] = 0;
        }

        // Redirect to the profile page upon successful login/registration
        header('Location: profile.php');
        exit();

    } catch (Exception $e) {
        // On error, redirect to login with an error message
        error_log($e->getMessage()); // Log the actual error for debugging
        header('Location: login.php?error=google_auth_failed');
        exit();
    }
} else {
    // If no authorization code is present, generate the authentication URL and redirect.
    $authUrl = $client->createAuthUrl();
    header('Location: ' . $authUrl);
    exit();
}
