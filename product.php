<?php
require_once 'db/config.php';
require_once 'includes/header.php';

$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$product = null;
$db_error = '';

if (!$product_id) {
    // Redirect or show error if ID is not valid
    header("Location: shop.php");
    exit;
}

try {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $db_error = "<p>خطا در برقراری ارتباط با پایگاه داده.</p>";
}

// If product not found, show a message and stop
if (!$product) {
    echo '<main class="container py-5 text-center"><div class="alert alert-danger">محصولی با این شناسه یافت نشد.</div><div><a href="shop.php" class="btn btn-primary mt-3">بازگشت به فروشگاه</a></div></main>';
    require_once 'includes/footer.php';
    exit;
}

// Set page title after fetching product name
$page_title = htmlspecialchars($product['name']);

// Parse comma-separated colors string
$available_colors = [];
if (!empty($product['colors'])) {
    $colors_raw = explode(',', $product['colors']);
    foreach ($colors_raw as $color) {
        $trimmed_color = trim($color);
        if (!empty($trimmed_color)) {
            $available_colors[] = $trimmed_color;
        }
    }
}

?>

<main class="container section-padding">
    <div class="row g-5">
        <div class="col-lg-6" data-aos="fade-right">
            <div class="card card-static p-3">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
        </div>

        <div class="col-lg-6" data-aos="fade-left">
            <div class="card h-100">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
            
                    <div class="d-flex align-items-center mb-4">
                        <p class="display-6 fw-bold m-0"><?php echo number_format($product['price']); ?> تومان</p>
                    </div>

                    <p class="fs-5 mb-4 text-muted"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

                    <form action="cart_handler.php" method="POST" class="mt-auto">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="action" value="add">

                        <?php if (!empty($available_colors)): ?>
                            <div class="mb-4">
                                <h5 class="mb-3">انتخاب رنگ:</h5>
                                <div class="color-swatches">
                                    <?php foreach ($available_colors as $index => $color_hex): ?>
                                        <input type="radio" class="btn-check" name="product_color" id="color_<?php echo $index; ?>" value="<?php echo htmlspecialchars($color_hex); ?>" autocomplete="off" <?php echo (count($available_colors) === 1) ? 'checked' : ''; ?>/>
                                        <label class="btn" for="color_<?php echo $index; ?>" style="background-color: <?php echo htmlspecialchars($color_hex); ?>;"></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="row align-items-center mb-4">
                            <div class="col-md-5 col-lg-4 quantity-input-wrapper">
                                 <label for="quantity" class="form-label fw-bold">تعداد:</label>
                                <input type="number" name="quantity" id="quantity" class="form-control quantity-input text-center" value="1" min="1" max="10">
                            </div>
                        </div>

                        <div class="d-grid gap-2 add-to-cart-btn">
                            <button type="submit" class="btn btn-primary"><i class="ri-shopping-bag-add-line"></i> افزودن به سبد خرید</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- SweetAlert for color validation -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- 1. Flash Message Handling (from server-side) ---
    <?php
    if (isset($_SESSION['flash_message'])) {
        $flash_message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        echo "Swal.fire({
            title: '".addslashes($flash_message['message'])."',
            icon: '". $flash_message['type'] ."',
            toast: true,
            position: 'top-start',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            showCloseButton: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },
            customClass: {
                popup: 'dark-theme-toast'
            }
        });";
    }
    ?>

    // --- 2. Client-side Color Selection Validation ---
    const form = document.querySelector('form[action="cart_handler.php"]');
    if (form) {
        form.addEventListener('submit', function(event) {
            const availableColors = <?php echo json_encode($available_colors); ?>;
            const hasMultipleColors = Array.isArray(availableColors) && availableColors.length > 1;

            if (hasMultipleColors) {
                const selectedColor = document.querySelector('input[name="product_color"]:checked');
                if (!selectedColor) {
                    event.preventDefault(); // Stop form submission
                    Swal.fire({
                        title: 'لطفاً یک رنگ انتخاب کنید',
                        text: 'برای افزودن این محصول به سبد خرید، انتخاب رنگ الزامی است.',
                        icon: 'warning',
                        confirmButtonText: 'متوجه شدم',
                        customClass: {
                            popup: 'dark-theme-popup',
                            title: 'dark-theme-title',
                            htmlContainer: 'dark-theme-content',
                            confirmButton: 'dark-theme-button'
                        }
                    });
                }
            }
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>