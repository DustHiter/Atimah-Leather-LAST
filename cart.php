<?php
session_start();
$page_title = 'سبد خرید';
require_once 'includes/header.php';

$cart_items = $_SESSION['cart'] ?? [];
$total_price = 0;
?>

<main>
    <section class="section-padding">
        <div class="container">

            <?php if (empty($cart_items)): ?>
                <div class="card card-body text-center p-4 p-md-5" data-aos="fade-up">
                    <div class="d-inline-block mx-auto mb-4">
                        <i class="ri-shopping-cart-2-line display-1 text-gold"></i>
                    </div>
                    <h2 class="mb-3">سبد خرید شما خالی است</h2>
                    <p class="text-muted fs-5 mb-4">به نظر می‌رسد هنوز محصولی به سبد خرید خود اضافه نکرده‌اید. همین حالا گشتی در فروشگاه بزنید.</p>
                    <div class="d-inline-block">
                        <a href="shop.php" class="btn btn-primary btn-lg">
                            <i class="ri-store-2-line me-2"></i>
                            رفتن به فروشگاه
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center" data-aos="fade-down">
                    <h1 class="section-title">سبد خرید شما</h1>
                    <p class="text-muted fs-5">جزئیات سفارش خود را بررسی و نهایی کنید.</p>
                </div>
                
                <div class="row g-5 mt-5">
                    <div class="col-lg-8">
                        <?php foreach ($cart_items as $item_id => $item): 
                            $item_total = $item['price'] * $item['quantity'];
                            $total_price += $item_total;
                        ?>
                            <div class="card card-body mb-4" data-aos="fade-up">
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
                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        </a>
                                    </div>
                                    <div class="col-md-4 col-9 cart-item-details">
                                        <h5><a href="product.php?id=<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['name']); ?></a></h5>
                                        <?php if (!empty($item['color'])) : ?>
                                            <div class="d-flex align-items-center">
                                                <small class="text-muted me-2">رنگ:</small>
                                                <span class="cart-item-color-swatch" style="background-color: <?php echo htmlspecialchars($item['color']); ?>;"></span>
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
                        <div class="card card-body position-sticky" style="top: 2rem;" data-aos="fade-left">
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
                                <a href="checkout.php" class="btn btn-primary btn-lg btn-checkout"><i class="ri-secure-payment-line me-2"></i>ادامه و پرداخت</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>