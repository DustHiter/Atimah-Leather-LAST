<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>پنل مدیریت آتیمه</title>
    <meta name="robots" content="noindex, nofollow">
    <!-- IRANSans Font -->
    <link rel="stylesheet" href="https://font-ir.s3.ir-thr-at1.arvanstorage.com/IRANSans/css/IRANSans.css">
    
    <!-- Main Theme CSS -->
    <link rel="stylesheet" href="../assets/css/theme.css?v=<?php echo time(); ?>">

    <!-- Font Awesome for admin icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel="stylesheet" href="assets/css/admin_style.css?v=<?php echo time(); ?>">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <?php require_once 'nav.php'; ?>
    <div class="admin-main-content">
        <header class="admin-header-bar">
            <button id="sidebar-toggle" class="btn">
                <i class="fas fa-bars"></i>
            </button>
            <div class="admin-header-title">
                 <h1><?php echo isset($page_title) ? $page_title : 'داشبورد'; ?></h1>
            </div>
        </header>
        <main>