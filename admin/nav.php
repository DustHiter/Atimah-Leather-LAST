<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h2><a href="index.php">آتیمه<span>.</span></a></h2>
    </div>
    <nav>
        <ul class="admin-nav">
            <li class="admin-nav-item">
                <a class="admin-nav-link <?php echo ($current_page == 'index.php' || $current_page == 'dashboard.php') ? 'active' : ''; ?>" href="index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>داشبورد</span>
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
                    <i class="fas fa-clipboard-list"></i>
                    <span>سفارشات</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a class="admin-nav-link <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>" href="reports.php">
                    <i class="fas fa-chart-bar"></i>
                    <span>گزارشات</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a class="admin-nav-link <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>" href="users.php">
                    <i class="fas fa-users"></i>
                    <span>کاربران</span>
                </a>
            </li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> مشاهده سایت</a>
        <hr style="border-color: var(--admin-border-light); margin: 1rem 0;">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> خروج</a>
    </div>
</aside>
