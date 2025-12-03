<?php
session_start();
$page_title = 'سبد خرید';
require_once 'includes/header.php';

$cart_items = $_SESSION['cart'] ?? [];
$total_price = 0;
?>

<div class="cart-page-wrapper">
    <div class="container">

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart-container">
                <i class="ri-shopping-cart-line"></i>
                <h2>سبد خرید شما خالی است</h2>
                <p>به نظر می‌رسد هنوز محصولی به سبد خرید خود اضافه نکرده‌اید. همین حالا گشتی در فروشگاه بزنید.</p>
                <a href="shop.php" class="btn btn-primary btn-lg btn-checkout">
                    <i class="ri-store-2-line me-2"></i>
                    رفتن به فروشگاه
                </a>
            </div>
        <?php else: ?>
            <div class="text-center mb-5">
                <h1 class="fw-bold display-5">سبد خرید شما</h1>
                <p class="text-white-50 fs-5">جزئیات سفارش خود را بررسی و نهایی کنید.</p>
            </div>
            <div class="row g-5">
                <div class="col-lg-8">
                    <?php foreach ($cart_items as $item_id => $item): 
                        $item_total = $item['price'] * $item['quantity'];
                        $total_price += $item_total;
                    ?>
                        <div class="cart-item-card">
                             <div class="remove-item-btn">
                                <form action="cart_handler.php" method="POST" class="d-inline">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                    <input type="hidden" name="product_color" value="<?php echo htmlspecialchars($item['color'] ?? ''); ?>">
                                    <input type="hidden" name="action" value="remove">
                                    <button type="submit" class="btn btn-link text-decoration-none p-0"><i class="ri-close-circle-line"></i></button>
                                </form>
                            </div>
                            <div class="row align-items-center g-3">
                                <div class="col-md-2 col-3 cart-item-image">
                                    <a href="product.php?id=<?php echo $item['product_id']; ?>">
                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    </a>
                                </div>
                                <div class="col-md-4 col-9 cart-item-details">
                                    <h5><a href="product.php?id=<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['name']); ?></a></h5>
                                    <?php if (!empty($item['color'])): ?>
                                        <div class="d-flex align-items-center">
                                            <small class="text-white-50 me-2">رنگ:</small>
                                            <span class="d-inline-block rounded-circle border" style="width: 20px; height: 20px; background-color: <?php echo htmlspecialchars($item['color']); ?>;"></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-3 col-7">
                                    <form action="cart_handler.php" method="POST" class="quantity-selector">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        <input type="hidden" name="product_color" value="<?php echo htmlspecialchars($item['color'] ?? ''); ?>">
                                        <input type="hidden" name="action" value="update">
                                        
                                        <button type="submit" name="quantity" value="<?php echo $item['quantity'] + 1; ?>" class="btn">+</button>
                                        <input type="text" value="<?php echo $item['quantity']; ?>" class="quantity-input" readonly>
                                        <button type="submit" name="quantity" value="<?php echo $item['quantity'] - 1; ?>" class="btn" <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>>-</button>
                                    </form>
                                </div>
                                <div class="col-md-3 col-5 text-end">
                                    <span class="item-price"><?php echo number_format($item_total); ?> <small>تومان</small></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="col-lg-4">
                    <div class="order-summary-card">
                        <h4 class="card-title">خلاصه سفارش</h4>
                        <div class="summary-item">
                            <span class="label">جمع کل</span>
                            <span class="value"><?php echo number_format($total_price); ?> تومان</span>
                        </div>
                        <div class="summary-item">
                            <span class="label">هزینه ارسال</span>
                            <span class="value text-success">رایگان</span>
                        </div>
                        <div class="summary-total">
                             <div class="summary-item">
                                <span class="label">مبلغ نهایی</span>
                                <span class="value"><?php echo number_format($total_price); ?> تومان</span>
                            </div>
                        </div>
                        <div class="d-grid mt-4">
                            <a href="checkout.php" class="btn btn-primary btn-lg btn-checkout">ادامه و پرداخت</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>