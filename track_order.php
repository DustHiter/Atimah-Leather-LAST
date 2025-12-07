<?php
$page_title = "پیگیری سفارش";
include 'includes/header.php';
?>

<div class="container section-padding">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card">
                <div class="card-body p-5">
                    <h1 class="text-center"><i class="ri-search-eye-line me-2"></i>پیگیری سفارش</h1>
                    <p class="text-center text-muted">کد رهگیری سفارش خود را برای مشاهده جزئیات وارد کنید.</p>
                    
                    <form id="track-order-form" class="mt-4">
                        <div class="mb-3">
                            <input type="text" id="tracking_id" name="tracking_id" class="form-control form-control-lg" placeholder="کد رهگیری سفارش" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg"><i class="ri-search-line me-2"></i>جستجو</button>
                        </div>
                    </form>

                    <div id="result-message" class="mt-4 text-center"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Tracking Modal -->
<div class="tracking-modal-container" id="tracking-modal">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>جزئیات سفارش <span id="modal-order-id"></span></h3>
            <button class="modal-close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <div class="order-summary">
                <div class="detail-item"><strong>تاریخ ثبت:</strong> <span id="modal-order-date"></span></div>
                <div class="detail-item"><strong>مبلغ کل:</strong> <span id="modal-order-amount"></span></div>
                <div class="detail-item"><strong>تخفیف:</strong> <span id="modal-order-discount"></span></div>
            </div>
            <div class="status-details">
                <h4>وضعیت سفارش: <span id="modal-order-status-text" style="font-weight: bold;"></span></h4>
                            <div class="status-tracker" id="modal-status-tracker">
                                <div class="status-progress"></div>
                                <div class="status-step" data-status="processing">
                                    <div class="dot"></div><span class="label">در حال پردازش</span>
                                </div>
                                <div class="status-step" data-status="shipped">
                                    <div class="dot"></div><span class="label">ارسال شده</span>
                                </div>
                                <div class="status-step" data-status="completed">
                                    <div class="dot"></div><span class="label">تکمیل شده</span>
                                </div>
                                <div class="status-step" data-status="cancelled">
                                    <div class="dot"></div><span class="label">لغو شده</span>
                                </div>
                            </div>
                        </div>
                        <div class="shipping-details">
                            <h4>اطلاعات ارسال</h4>
                            <div class="detail-item"><strong>تحویل گیرنده:</strong> <span id="modal-shipping-name"></span></div>
                            <div class="detail-item"><strong>آدرس:</strong> <span id="modal-shipping-address"></span></div>
                            <div class="detail-item"><strong>کدپستی:</strong> <span id="modal-shipping-postal-code"></span></div>
                        </div>
                        <div class="products-list">
                            <h4>محصولات سفارش</h4>
                            <div id="modal-products-list"></div>
                        </div>
                    </div>
                </div>
                </div>
                
                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const form = document.getElementById('track-order-form');
                    const modal = document.getElementById('tracking-modal');
                    const overlay = document.querySelector('.modal-overlay');
                    const closeBtn = document.querySelector('.modal-close-btn');
                    const resultMessage = document.getElementById('result-message');
                
                    form.addEventListener('submit', async function (e) {
                        e.preventDefault();
                        const trackingId = document.getElementById('tracking_id').value;
                        
                        resultMessage.innerHTML = `<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div> در حال جستجو...`;
                        resultMessage.className = 'text-center text-muted';
                
                        try {
                            const response = await fetch('api/get_order_details.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ tracking_id: trackingId }),
                            });
                
                            const data = await response.json();
                
                            if (data.success) {
                                resultMessage.innerHTML = '';
                                displayOrderDetails(data.order, data.products);
                                modal.classList.add('visible');
                            } else {
                                resultMessage.innerHTML = data.message;
                                resultMessage.className = 'text-center text-danger fw-bold';
                            }
                        } catch (error) {
                            console.error('Fetch Error:', error);
                            resultMessage.innerHTML = 'خطا در برقراری ارتباط با سرور. لطفاً اتصال اینترنت خود را بررسی کنید.';
                            resultMessage.className = 'text-center text-danger fw-bold';
                        }
                    });
                
                    function displayOrderDetails(order, products) {
                        document.getElementById('modal-order-id').textContent = '#' + order.id;
                        document.getElementById('modal-order-date').textContent = order.order_date;
                        document.getElementById('modal-order-amount').textContent = order.total_amount;
                        document.getElementById('modal-order-discount').textContent = order.discount_amount;
                        
                        document.getElementById('modal-shipping-name').textContent = order.shipping_name;
                        document.getElementById('modal-shipping-address').textContent = order.shipping_address;
                        document.getElementById('modal-shipping-postal-code').textContent = order.shipping_postal_code;
                
                        const productsContainer = document.getElementById('modal-products-list');
                        productsContainer.innerHTML = '';
                        if (products && products.length > 0) {
                            products.forEach(p => {
                                const imageUrl = p.image_url ? p.image_url : 'assets/images/placeholder.png';
                                productsContainer.innerHTML += `
                                    <div class="product-item">
                                        <img src="${imageUrl}" alt="${p.name}" onerror="this.onerror=null;this.src='assets/images/placeholder.png';">
                                        <div class="product-info">
                                            <span class="product-name">${p.name}</span>
                                            <div class="product-meta">
                                                <span class="product-quantity">تعداد: ${p.quantity}</span>
                                                ${p.color ? `
                                                <span class="product-color-wrapper">
                                                    رنگ: <span class="product-color-dot" style="background-color: ${p.color};"></span>
                                                </span>` : ''}
                                            </div>
                                        </div>
                                        <div class="product-price">${p.price}</div>
                                    </div>
                                `;
                            });
                        } else {
                            productsContainer.innerHTML = '<p class="text-center text-muted">محصولی برای این سفارش یافت نشد.</p>';
                        }
                        
                        updateStatusTracker(order.status, order.status_persian);
                    }
                
                    function updateStatusTracker(status, statusPersian) {
                        console.log('--- Debugging Status Animation ---');
                        console.log('Received status:', status);
                
                        const statusTextEl = document.getElementById('modal-order-status-text');
                
                        const statusMap = {
                            'pending': 'processing',
                            'processing': 'processing',
                            'shipped': 'shipped',
                            'delivered': 'completed',
                            'completed': 'completed',
                            'cancelled': 'cancelled'
                        };
                        const mappedStatus = status ? statusMap[status.toLowerCase()] : 'processing';
                
                        const statusDisplayMap = {
                            'processing': { color: '#ffc107', text: 'در حال پردازش' },
                            'shipped': { color: '#0d6efd', text: 'ارسال شده' },
                            'completed': { color: '#198754', text: 'تکمیل شده' },
                            'cancelled': { color: '#dc3545', text: 'لغو شده' }
                        };
                
                        const displayInfo = statusDisplayMap[mappedStatus] || statusDisplayMap['processing'];
                        
                        statusTextEl.textContent = statusPersian || displayInfo.text;
                        statusTextEl.style.color = displayInfo.color;
                
                        const tracker = document.getElementById('modal-status-tracker');
                        tracker.className = 'status-tracker';
                        const progress = tracker.querySelector('.status-progress');
                        const steps = Array.from(tracker.querySelectorAll('.status-step'));
                        
                        steps.forEach(step => step.classList.remove('active', 'completed'));
                
                        const statusOrder = ['processing', 'shipped', 'completed'];
                        let statusIndex = statusOrder.indexOf(mappedStatus);
                        console.log('Mapped status:', mappedStatus, '| Calculated status index:', statusIndex);
                
                        if (mappedStatus === 'cancelled') {
                            tracker.classList.add('is-cancelled');
                            const cancelledStep = tracker.querySelector('[data-status="cancelled"]');
                            if(cancelledStep) cancelledStep.classList.add('active');
                            progress.style.width = '0%';
                        } else {
                            tracker.classList.remove('is-cancelled');
                            if (statusIndex !== -1) {
                                for (let i = 0; i <= statusIndex; i++) {
                                    const step = tracker.querySelector(`[data-status="${statusOrder[i]}"]`);
                                    if(step) step.classList.add('completed');
                                }
                                const activeStep = tracker.querySelector(`[data-status="${mappedStatus}"]`);
                                if(activeStep) {
                                    activeStep.classList.remove('completed');
                                    activeStep.classList.add('active');
                                }
                                const progressPercentage = statusIndex >= 0 ? (statusIndex / (statusOrder.length - 1)) * 100 : 0;
                                console.log('Progress percentage:', progressPercentage);
                                progress.style.width = `${progressPercentage}%`;
                            } else {
                                 progress.style.width = '0%';
                                 console.log('Unknown status. Resetting progress bar.');
                            }
                        }
                    }
                
                    function closeModal() {
                        modal.classList.remove('visible');
                    }
                
                    closeBtn.addEventListener('click', closeModal);
                    overlay.addEventListener('click', closeModal);
                });
                </script>
<?php include 'includes/footer.php'; ?>