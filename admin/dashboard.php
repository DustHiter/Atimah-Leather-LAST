<?php
$page_title = 'داشبورد';
require_once __DIR__ . '/header.php';
?>

<div class="admin-header">
    <h1><?php echo $page_title; ?></h1>
</div>

<div class="tabs">
    <div class="tab-links">
        <a href="#reports" class="tab-link active">گزارشات</a>
        <a href="#settings" class="tab-link">تنظیمات</a>
    </div>
    <div class="tab-content">
        <div id="reports" class="tab-pane active">
            <h3>گزارشات فروش</h3>
            <div class="stat-cards-grid-reports">
                <div class="stat-card-report">
                    <p>مجموع فروش (تکمیل شده)</p>
                    <h3 id="total-sales">...</h3>
                </div>
                <div class="stat-card-report">
                    <p>مجموع کاربران</p>
                    <h3 id="total-users">...</h3>
                </div>
                <div class="stat-card-report">
                    <p>سفارشات در حال پردازش</p>
                    <h3 id="processing-orders">...</h3>
                </div>
            </div>

            <div class="card" style="margin-top: 2rem;">
                <h5>نمودار فروش ماهانه (سفارشات تحویل شده)</h5>
                <div style="height: 350px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
        <div id="settings" class="tab-pane">
            <h3>تنظیمات</h3>
            <p>این بخش برای تنظیمات آینده در نظر گرفته شده است.</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');

            tabLinks.forEach(l => l.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));

            this.classList.add('active');
            document.querySelector(targetId).classList.add('active');
        });
    });

    // Fetch data for stats and chart
    Promise.all([
        fetch('api.php?action=get_stats').then(res => res.ok ? res.json() : Promise.reject('Failed to load stats')),
        fetch('api.php?action=get_sales_data').then(res => res.ok ? res.json() : Promise.reject('Failed to load sales data'))
    ]).then(([statsData, salesData]) => {
        if (statsData.error) throw new Error(statsData.error);
        document.getElementById('total-sales').textContent = new Intl.NumberFormat('fa-IR').format(statsData.total_sales) + ' تومان';
        document.getElementById('total-users').textContent = statsData.total_users;
        document.getElementById('processing-orders').textContent = statsData.processing_orders;

        if (salesData.error) throw new Error(salesData.error);
        renderSalesChart(salesData.labels, salesData.data);

    }).catch(error => {
        console.error('Dashboard Error:', error);
        const reportsTab = document.getElementById('reports');
        reportsTab.innerHTML = `<div style="color: #F44336; padding: 2rem; text-align: center;">خطا در بارگذاری داده‌های داشبورد. لطفاً بعداً تلاش کنید.</div>`;
    });

    function renderSalesChart(labels, data) {
        const ctx = document.getElementById('salesChart').getContext('2d');
        const primaryColor = getComputedStyle(document.body).getPropertyValue('--admin-primary').trim();

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'میزان فروش',
                    data: data,
                    backgroundColor: `${primaryColor}33`, // 20% opacity
                    borderColor: primaryColor,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                 scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});
</script>

<?php
require_once __DIR__ . '/footer.php';
?>