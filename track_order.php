<?php
$page_title = "پیگیری سفارش";
include 'includes/header.php';
?>



<div class="track-container">
    <h1>پیگیری سفارش</h1>
    <p>کد رهگیری و شماره تلفن خود را برای مشاهده جزئیات سفارش وارد کنید.</p>
    <form id="track-order-form">
        <div class="form-group">
            <label for="tracking_id">کد رهگیری</label>
            <input type="text" id="tracking_id" name="tracking_id" required>
        </div>
        <div class="form-group">
            <label for="phone">شماره تلفن</label>
            <input type="text" id="phone" name="phone" required>
        </div>
        <button type="submit" class="btn-track">جستجو</button>
    </form>
    <div id="result-message" style="margin-top: 20px; font-weight: bold;"></div>
</div>

<!-- The Modal -->
<div id="order-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <div id="modal-body">
            <!-- Order details will be injected here by JavaScript -->
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('track-order-form');
    const modal = document.getElementById('order-modal');
    const modalBody = document.getElementById('modal-body');
    const closeBtn = document.querySelector('.close-btn');
    const resultMessage = document.getElementById('result-message');

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const trackingId = document.getElementById('tracking_id').value;
        const phone = document.getElementById('phone').value;
        
        resultMessage.textContent = 'در حال جستجو...';
        resultMessage.style.color = 'var(--luxury-text-muted)';

        fetch('api/get_order_details.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                tracking_id: trackingId,
                phone: phone
            }),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                resultMessage.textContent = '';
                displayOrderDetails(data.order, data.products);
                modal.style.display = 'block';
            } else {
                resultMessage.textContent = data.message;
                resultMessage.style.color = '#ff6b6b'; // A clearer error color
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultMessage.textContent = 'خطا در برقراری ارتباط با سرور.';
            resultMessage.style.color = '#ff6b6b';
        });
    });

    function displayOrderDetails(order, products) {
        let productsHtml = `
            <div class="detail-box" style="grid-column: 1 / -1;">
                <h3>محصولات سفارش</h3>
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>محصول</th>
                            <th>تعداد</th>
                            <th>رنگ</th>
                            <th>قیمت واحد</th>
                            <th>قیمت کل</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        products.forEach(p => {
            productsHtml += `
                <tr>
                    <td><img src="assets/images/products/${p.image}" alt="${p.name}">${p.name}</td>
                    <td>${p.quantity}</td>
                    <td>${p.color || '-'}</td>
                    <td>${parseInt(p.price).toLocaleString()} تومان</td>
                    <td>${(p.quantity * p.price).toLocaleString()} تومان</td>
                </tr>
            `;
        });
        productsHtml += `
                    </tbody>
                </table>
            </div>
        `;

        modalBody.innerHTML = `
            <div class="modal-header">
                <h2>جزئیات سفارش</h2>
                <p>کد رهگیری: ${order.tracking_id}</p>
            </div>
            <div class="order-details-grid">
                <div class="detail-box">
                    <h3>اطلاعات خریدار</h3>
                    <p><strong>نام و نام خانوادگی:</strong> ${order.full_name}</p>
                    <p><strong>ایمیل:</strong> ${order.email}</p>
                    <p><strong>تلفن:</strong> ${order.billing_phone}</p>
                </div>
                <div class="detail-box">
                    <h3>اطلاعات سفارش</h3>
                    <p><strong>وضعیت:</strong> <span style="font-weight: bold; color: #81c784;">${order.status}</span></p>
                    <p><strong>تاریخ ثبت:</strong> ${order.created_at_jalali}</p>
                    <p><strong>آدرس کامل:</strong> ${order.address}</p>
                </div>
                ${productsHtml}
            </div>
            <div class="total-price-container">
                <p>جمع کل: ${parseInt(order.total_price).toLocaleString()} تومان</p>
            </div>
        `;
    }

    closeBtn.onclick = function () {
        modal.style.display = 'none';
    }

    window.onclick = function (event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>