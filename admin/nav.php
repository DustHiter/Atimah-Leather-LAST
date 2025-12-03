<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<div class="admin-sidebar">
    <div class="admin-sidebar-header">
        <a href="index.php" class="logo">آتیمه<span>.</span></a>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>داشبورد</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'products.php' || $current_page == 'add_product.php' || $current_page == 'edit_product.php') ? 'active' : ''; ?>" href="products.php">
                <i class="bi bi-box-seam"></i>
                <span>محصولات</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'orders.php') ? 'active' : ''; ?>" href="orders.php">
                <i class="bi bi-card-checklist"></i>
                <span>سفارشات</span>
            </a>
        </li>
    </ul>
    <div class="admin-sidebar-footer">
        <a href="../index.php" target="_blank" class="btn btn-outline-secondary w-100 mb-2">
            <i class="bi bi-box-arrow-up-right"></i> مشاهده سایت
        </a>
        <a href="logout.php" class="btn btn-outline-danger w-100">
            <i class="bi bi-box-arrow-right"></i> خروج
        </a>
    </div>
</div>
<button class="btn sidebar-toggle d-lg-none" type="button" onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
</button>
<script>
function toggleSidebar() {
    document.querySelector('.admin-sidebar').classList.toggle('is-open');
}
</script>