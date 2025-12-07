<?php
$page_title = 'مدیریت کاربران';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../db/config.php';

try {
    $pdo = db();
    $stmt = $pdo->query("SELECT id, first_name, last_name, email, phone, created_at FROM users WHERE is_admin = 0 ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $user_count = count($users);
} catch (PDOException $e) {
    die("Error fetching users: " . $e->getMessage());
}
?>

<div class="admin-header">
    <h1><?php echo $page_title; ?></h1>
    <div style="display: flex; align-items: center; gap: 1rem;">
        <button id="add-user-btn" class="btn btn-primary">افزودن کاربر جدید</button>
        <span>تعداد کل کاربران:</span>
        <span class="badge bg-primary" style="font-size: 1rem; background-color: var(--admin-primary) !important; color: #000 !important; padding: 0.5rem 1rem; border-radius: 8px;"><?php echo $user_count; ?></span>
    </div>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success" style="background-color: var(--admin-success); color: #fff; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger" style="background-color: var(--admin-danger); color: #fff; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div id="add-user-form-container" class="card" style="display: none; margin-bottom: 2rem;">
    <div class="card-header">فرم افزودن کاربر جدید</div>
    <div class="card-body">
        <form action="handler.php" method="POST">
            <input type="hidden" name="action" value="add_user">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div class="form-group">
                    <label for="first_name" class="form-label">نام</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="last_name" class="form-label">نام خانوادگی</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email" class="form-label">ایمیل</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="phone" class="form-label">شماره تلفن</label>
                    <input type="text" id="phone" name="phone" class="form-control">
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">رمز عبور</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="form-group" style="align-self: center;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_admin" id="is_admin" value="1">
                        <label class="form-check-label" for="is_admin">
                            ادمین باشد؟
                        </label>
                    </div>
                </div>
            </div>
            <div style="text-align: left;">
                 <button type="submit" class="btn btn-primary">ذخیره کاربر</button>
                 <button type="button" id="cancel-add-user" class="btn btn-secondary">انصراف</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addUserBtn = document.getElementById('add-user-btn');
    const addUserForm = document.getElementById('add-user-form-container');
    const cancelBtn = document.getElementById('cancel-add-user');

    if(addUserBtn) {
        addUserBtn.addEventListener('click', () => {
            addUserForm.style.display = 'block';
            addUserBtn.style.display = 'none';
        });
    }

    if(cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            addUserForm.style.display = 'none';
            addUserBtn.style.display = 'block';
        });
    }
});
</script>



<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>نام</th>
                        <th>ایمیل</th>
                        <th>شماره تلفن</th>
                        <th>تاریخ عضویت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)):
 ?>
                        <tr><td colspan="5" style="text-align: center; padding: 2rem;">هیچ کاربری یافت نشد.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $user):
 ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars(trim($user['first_name'] . ' ' . $user['last_name'])); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? 'ثبت نشده'); ?></td>
                                <td><?php echo date("Y-m-d", strtotime($user['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/footer.php';
?>