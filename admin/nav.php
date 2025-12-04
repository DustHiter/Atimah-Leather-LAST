<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h2><a href="index.php">آتیمه<span>.</span></a></h2>
    </div>
    <ul class="admin-nav">
        <li class="admin-nav-item">
            <a class="admin-nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>داشبورد اصلی</span>
            </a>
        </li>
        <li class="admin-nav-item">
            <a class="admin-nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                <i class="fas fa-chart-line"></i>
                <span>گزارش‌ها</span>
            </a>
        </li>
        <li class="admin-nav-item">
            <a class="admin-nav-link <?php echo in_array($current_page, ['products.php', 'add_product.php', 'edit_product.php']) ? 'active' : ''; ?>" href="products.php">
                <i class="fas fa-box"></i>
                <span>محصولات</span>
            </a>
        </li>
        <li class="admin-nav-item">
            <a class="admin-nav-link <?php echo ($current_page == 'orders.php') ? 'active' : ''; ?>" href="orders.php">
                <i class="fas fa-receipt"></i>
                <span>سفارشات</span>
            </a>
        </li>
    </ul>
    <div class="sidebar-footer">
        <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> مشاهده سایت</a>
        <hr style="border-color: var(--admin-border); margin: 1rem 0;">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> خروج</a>
    </div>
</aside>