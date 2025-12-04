<?php
require_once __DIR__ . '/auth_handler.php';

// Check if the user is logged in. If not, redirect to the login page.
if (!is_admin()) {
    header('Location: login.php');
    exit;
}
