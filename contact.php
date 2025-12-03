<?php
session_start(); // Ensure session is started
$page_title = 'تماس با ما';
require_once 'includes/header.php';
require_once 'mail/MailService.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['contact_form'])) {
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING));

    if (empty($name) || empty($email) || empty($message)) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'لطفاً تمام فیلدها را پر کنید.'];
    } elseif (!$email) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'آدرس ایمیل وارد شده معتبر نیست.'];
    } else {
        // Send email using MailService
        $to_email = getenv('MAIL_TO') ?: 'your-default-email@example.com'; // Fallback email
        $subject = "پیام جدید از فرم تماس وب‌سایت";

        $email_result = MailService::sendContactMessage($name, $email, $message, $to_email, $subject);

        if (!empty($email_result['success'])) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'پیام شما با موفقیت ارسال شد. سپاسگزاریم!'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'خطا در ارسال پیام. لطفاً بعداً دوباره تلاش کنید.'];
            error_log("MailService Error: " . ($email_result['error'] ?? 'Unknown error'));
        }
    }
    
    // Redirect to the same page to prevent form resubmission
    header("Location: contact.php");
    exit();
}

// Check for flash messages
$flash_message = $_SESSION['flash_message'] ?? null;
if ($flash_message) {
    unset($_SESSION['flash_message']);
}
?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5" data-aos="fade-down">
                <h1 class="display-4 fw-bold">ارتباط با ما</h1>
                <p class="fs-5 text-muted">نظرات، پیشنهادات و سوالات شما برای ما ارزشمند است.</p>
            </div>

            <div class="card border-0 shadow-lg" data-aos="fade-up">
                <div class="card-body p-4 p-md-5">
                    <form action="contact.php" method="POST">
                        <input type="hidden" name="contact_form">
                        <div class="mb-4">
                            <label for="name" class="form-label fs-5">نام شما</label>
                            <input type="text" class="form-control form-control-lg bg-dark" id="name" name="name" required>
                        </div>
                        <div class="mb-4">
                            <label for="email" class="form-label fs-5">ایمیل</label>
                            <input type="email" class="form-control form-control-lg bg-dark" id="email" name="email" required>
                        </div>
                        <div class="mb-4">
                            <label for="message" class="form-label fs-5">پیام شما</label>
                            <textarea class="form-control form-control-lg bg-dark" id="message" name="message" rows="6" required></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">ارسال پیام</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    <?php if ($flash_message): ?>
    Swal.fire({
        title: '<?php echo $flash_message["type"] === "success" ? "موفق" : "خطا"; ?>',
        text: '<?php echo addslashes($flash_message["message"]); ?>',
        icon: '<?php echo $flash_message["type"]; ?>',
        confirmButtonText: 'باشه',
        background: '#2C2C2C',
        color: '#D5D5D5'
    });
    <?php endif; ?>
});
</script>

<?php require_once 'includes/footer.php'; ?>
