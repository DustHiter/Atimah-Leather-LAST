        </main> <!-- .admin-main-content's inner main -->
    </div> <!-- .admin-main-content -->
</div> <!-- .admin-wrapper -->

<div class="sidebar-backdrop" id="sidebar-backdrop"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.admin-sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const adminWrapper = document.querySelector('.admin-wrapper');
    const backdrop = document.getElementById('sidebar-backdrop');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            if (window.innerWidth <= 992) {
                sidebar.classList.toggle('open');
                backdrop.classList.toggle('show');
            } else {
                adminWrapper.classList.toggle('sidebar-collapsed');
            }
        });
    }

    if (backdrop) {
        backdrop.addEventListener('click', function() {
            sidebar.classList.remove('open');
            this.classList.remove('show');
        });
    }
});
</script>

</body>
</html>
