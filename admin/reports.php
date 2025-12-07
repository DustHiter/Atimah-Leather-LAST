<?php
$page_title = 'گزارشات';
require_once __DIR__ . '/header.php';
?>

<div class="admin-header">
    <h1><?php echo $page_title; ?></h1>
</div>

<!-- Stat Cards -->
<div class="stat-cards-grid-reports" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <div class="stat-card-report">
        <p>مجموع درآمد</p>
        <h3 id="total-revenue">...</h3>
    </div>
    <div class="stat-card-report">
        <p>تعداد سفارشات</p>
        <h3 id="total-orders">...</h3>
    </div>
    <div class="stat-card-report">
        <p>تعداد کاربران</p>
        <h3 id="total-users">...</h3>
    </div>
    <div class="stat-card-report">
        <p>تعداد محصولات</p>
        <h3 id="total-products">...</h3>
    </div>
</div>

<!-- Sales Chart -->
<div class="card">
    <div class="card-header">نمودار فروش ماهانه</div>
    <div class="card-body">
        <canvas id="salesChart"></canvas>
    </div>
</div>

<div class="row" style="display: flex; gap: 2rem; margin-top: 2rem;">
    <!-- Recent Orders -->
    <div class="card" style="flex: 1;">
        <div class="card-header">آخرین سفارشات</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>شماره سفارش</th>
                            <th>مشتری</th>
                            <th>مبلغ</th>
                            <th>وضعیت</th>
                        </tr>
                    </thead>
                    <tbody id="recent-orders-body">
                        <!-- Data will be loaded via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Selling Products -->
    <div class="card" style="flex: 1;">
        <div class="card-header">محصولات پرفروش</div>
        <div class="card-body">
             <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>محصول</th>
                            <th>تعداد فروش</th>
                        </tr>
                    </thead>
                    <tbody id="top-products-body">
                        <!-- Data will be loaded via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fetch general reports data
    fetch('api.php?action=get_reports_data')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }
            
            document.getElementById('total-revenue').textContent = new Intl.NumberFormat('fa-IR').format(data.stats.total_revenue) + ' تومان';
            document.getElementById('total-orders').textContent = data.stats.total_orders;
            document.getElementById('total-users').textContent = data.stats.total_users;
            document.getElementById('total-products').textContent = data.stats.total_products;

            const recentOrdersBody = document.getElementById('recent-orders-body');
            if(data.recent_orders.length > 0) {
                data.recent_orders.forEach(order => {
                    let row = `<tr>
                        <td>#${order.id}</td>
                        <td>${order.customer_display_name}</td>
                        <td>${new Intl.NumberFormat('fa-IR').format(order.total_amount)} تومان</td>
                        <td><span class="status-badge status-${order.status.toLowerCase()}">${order.status}</span></td>
                    </tr>`;
                    recentOrdersBody.innerHTML += row;
                });
            } else {
                recentOrdersBody.innerHTML = '<tr><td colspan="4" class="text-center">سفارشی یافت نشد.</td></tr>';
            }

            const topProductsBody = document.getElementById('top-products-body');
            if(data.top_products.length > 0) {
                 data.top_products.forEach(product => {
                    let row = `<tr>
                        <td>${product.name}</td>
                        <td>${product.total_sold} عدد</td>
                    </tr>`;
                    topProductsBody.innerHTML += row;
                });
            } else {
                 topProductsBody.innerHTML = '<tr><td colspan="2" class="text-center">محصولی یافت نشد.</td></tr>';
            }
        })
        .catch(error => console.error('Error fetching reports:', error));

    // Fetch monthly sales data for the chart
    fetch('api.php?action=get_monthly_sales')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error fetching chart data:', data.error);
                return;
            }

            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'فروش ماهانه',
                        data: data.values,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value, index, values) {
                                    return new Intl.NumberFormat('fa-IR').format(value) + ' تومان';
                                }
                            }
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error fetching chart data:', error));
});
</script>


<?php
require_once __DIR__ . '/footer.php';
?>