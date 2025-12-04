<?php
require_once __DIR__ . '/header.php';
?>

<div class="admin-header">
    <h1>گزارش‌ها</h1>
</div>

<div id="dashboard-error" class="card d-none" style="color: var(--admin-danger);"><div class="card-body"></div></div>

<style>
.stat-cards-grid-reports {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}
</style>

<div class="stat-cards-grid-reports">
    <div class="stat-card">
        <div class="icon bg-success"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-info">
            <p>مجموع فروش (تحویل شده)</p>
            <h3 id="total-sales">...</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="icon bg-info"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <p>مجموع کاربران</p>
            <h3 id="total-users">...</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="icon bg-primary"><i class="fas fa-truck"></i></div>
        <div class="stat-info">
            <p>سفارشات در حال ارسال</p>
            <h3 id="shipped-orders">...</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="icon bg-danger"><i class="fas fa-times-circle"></i></div>
        <div class="stat-info">
            <p>سفارشات لغو شده</p>
            <h3 id="cancelled-orders">...</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="icon bg-warning"><i class="fas fa-spinner"></i></div>
        <div class="stat-info">
            <p>سفارشات در حال پردازش</p>
            <h3 id="processing-orders">...</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="icon" style="background-color: #6f42c1;"><i class="fas fa-eye"></i></div>
        <div class="stat-info">
            <p>کل بازدید صفحات</p>
            <h3 id="total-page-views">...</h3>
        </div>
    </div>
</div>

<div class="chart-container" style="position: relative; height:40vh; max-height: 450px;">
    <h5>نمودار فروش ماهانه (سفارشات تحویل شده)</h5>
    <canvas id="salesChart"></canvas>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const errorDiv = document.getElementById('dashboard-error');
    const errorBody = errorDiv.querySelector('.card-body');

    function showError(message) {
        errorBody.textContent = message;
        errorDiv.classList.remove('d-none');
    }

    // Fetch all data in parallel for faster loading
    Promise.all([
        fetch('api.php?action=get_stats').then(res => res.json()),
        fetch('api.php?action=get_sales_data').then(res => res.json())
    ]).then(([statsData, salesData]) => {

        // Handle stats data
        if (statsData.error) throw new Error(`آمار: ${statsData.error}`);
        document.getElementById('total-sales').textContent = new Intl.NumberFormat('fa-IR').format(statsData.total_sales) + ' تومان';
        document.getElementById('total-users').textContent = statsData.total_users;
        document.getElementById('shipped-orders').textContent = statsData.shipped_orders;
        document.getElementById('cancelled-orders').textContent = statsData.cancelled_orders;
        document.getElementById('processing-orders').textContent = statsData.processing_orders;
        document.getElementById('total-page-views').textContent = statsData.total_page_views.count;

        // Handle sales chart data
        if (salesData.error) throw new Error(`نمودار فروش: ${salesData.error}`);
        renderSalesChart(salesData.labels, salesData.data);

    }).catch(error => {
        console.error(`خطا:`, error);
        showError(`خطا در بارگذاری گزارشات: ${error.message}`);
    });

    function renderSalesChart(labels, data) {
        const ctx = document.getElementById('salesChart').getContext('2d');
        const style = getComputedStyle(document.body);
        
        // Use a more vibrant and visible color for the chart
        const chartColor = '#FFD700'; // A nice gold color

        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, `${chartColor}60`); // 40% opacity
        gradient.addColorStop(1, `${chartColor}00`); // 0% opacity

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'میزان فروش',
                    data: data,
                    backgroundColor: gradient,
                    borderColor: chartColor,
                    borderWidth: 3,
                    fill: 'start',
                    tension: 0.4, // Makes the line curvy
                    pointBackgroundColor: chartColor,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBorderColor: style.getPropertyValue('--admin-bg').trim(),
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: style.getPropertyValue('--admin-surface').trim(),
                        titleColor: style.getPropertyValue('--admin-text').trim(),
                        bodyColor: style.getPropertyValue('--admin-text-muted').trim(),
                        titleFont: { family: 'Vazirmatn', size: 14, weight: 'bold' },
                        bodyFont: { family: 'Vazirmatn', size: 12 },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: (context) => `مجموع فروش: ${new Intl.NumberFormat('fa-IR').format(context.parsed.y)} تومان`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: style.getPropertyValue('--admin-text-muted').trim(),
                            font: { family: 'Vazirmatn', size: 12 }
                        }
                    },
                    y: {
                        grid: {
                            color: style.getPropertyValue('--admin-border').trim(),
                            drawBorder: false,
                        },
                        ticks: {
                            color: style.getPropertyValue('--admin-text-muted').trim(),
                            font: { family: 'Vazirmatn', size: 12 },
                            padding: 10,
                            callback: (value) => new Intl.NumberFormat('fa-IR', { notation: 'compact' }).format(value)
                        },
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