<?php
session_start();

// Require user to be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'db/config.php';

$user_id = $_SESSION['user_id'];

// Fetch user data
try {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch user addresses
    $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
    $stmt->execute([$user_id]);
    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}

$page_title = 'حساب کاربری من';
require_once 'includes/header.php';
?>

<style>
    .profile-nav .nav-link {
        color: #6c757d;
        border-bottom: 2px solid transparent;
    }
    .profile-nav .nav-link.active {
        color: var(--bs-primary);
        border-bottom-color: var(--bs-primary);
    }
</style>

<div class="container my-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card text-center p-3">
                <i class="ri-user-smile-line fs-1 text-primary mb-3"></i>
                <h4 class="card-title"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
             <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">افزودن آدرس جدید</h5>
                     <form action="#" method="POST"> <!-- Will be handled by a future handler -->
                        <div class="mb-3">
                             <label for="province" class="form-label">استان</label>
                             <input type="text" class="form-control" id="province" name="province" required>
                        </div>
                        <div class="mb-3">
                             <label for="city" class="form-label">شهر</label>
                             <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        <div class="mb-3">
                            <label for="address_line" class="form-label">آدرس دقیق</label>
                            <textarea class="form-control" id="address_line" name="address_line" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                             <label for="postal_code" class="form-label">کد پستی</label>
                             <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                        </div>
                         <div class="d-grid">
                             <button type="submit" class="btn btn-secondary">ثبت آدرس</button>
                         </div>
                     </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">آدرس‌های من</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($addresses)): ?>
                        <p class="text-center text-muted">شما هنوز هیچ آدرسی ثبت نکرده‌اید.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($addresses as $address): ?>
                                <div class="list-group-item list-group-item-action flex-column align-items-start">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">
                                            <?php echo htmlspecialchars($address['province'] . '، ' . $address['city']); ?>
                                            <?php if($address['is_default']): ?>
                                                <span class="badge bg-primary">پیش‌فرض</span>
                                            <?php endif; ?>
                                        </h6>
                                        <small class="text-muted">#<?php echo $address['id']; ?></small>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars($address['address_line']); ?></p>
                                    <small class="text-muted">کدپستی: <?php echo htmlspecialchars($address['postal_code']); ?></small>
                                     <div class="mt-2">
                                        <a href="#" class="btn btn-sm btn-outline-danger">حذف</a>
                                        <a href="#" class="btn btn-sm btn-outline-primary">ویرایش</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
