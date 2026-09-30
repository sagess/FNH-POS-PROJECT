<?php
require_once __DIR__ . '/../includes/app.php';
require_login();

$current_logged_in = current_user();
$acting_user_id = (int)($current_logged_in['user_id'] ?? 0);

$user_id = (int)($_GET['id'] ?? 0);
$user_data = get_user($user_id);

if ($user_data === null) {
    die('Error: That user profile does not exist.');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name    = $_POST['full_name'] ?? '';
    $email        = $_POST['email'] ?? '';
    $role         = $_POST['role'] ?? 'operator';
    $is_active    = (int)($_POST['is_active'] ?? 0) === 1;
    $new_password = $_POST['new_password'] ?? '';
    $confirm      = $_POST['password_confirm'] ?? '';

    if ($new_password !== '' && $new_password !== $confirm) {
        $errors['password_confirm'] = 'The two passwords do not match.';
    } else {

        $pass_to_update = ($new_password === '') ? null : $new_password;
        $result = update_user($user_id, $acting_user_id, $full_name, $email, $role, $is_active, $pass_to_update);

        if ($result['ok']) {
        
        if (function_exists('redirect')) {
            redirect('user.php');
            exit();
        }else {
            $errors = $result['errors'];
        }
        }
    }

}

$page_title = 'Edit User';
require __DIR__ . '/../includes/header.php';
?>
<h1>✏️ Edit Operator</h1>

<?php if ($errors): ?>
    <div class="alert alert-danger" style="color: red; margin-bottom: 15px;">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= ($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="edit-user.php?id=<?= $user_id ?>">

    <div class="form-group">
        <label for="username">Username</label>
        <input
            type="text"
            id="username" name="username"
            value="<?= $user_data['username'] ?>" maxlength="30" autocomplete="off" autocapitalize="none" disabled>
    </div>

    <div class="form-group">
        <label for="full_name">Full Name</label>
        <input
            type="text"
            id="full_name" name="full_name"
            maxlength="80" value="<?= $user_data['full_name'] ?>" required>
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email"
            value="<?= $user_data['email'] ?>" required>
    </div>

    <div class="form-group">
        <label for="role">Role</label>
        <select id="role" name="role" required>
            <option value="operator" <?= $user_data['role'] === 'operator' ? 'selected' : '' ?>>
                Operator
            </option>
            <option value="admin" <?= $user_data['role'] === 'admin' ? 'selected' : '' ?>>
                Admin
            </option>
        </select>
    </div>

    <div class="form-group">
        <label for="is_active">Status</label>
        <select id="is_active" name="is_active" required>
            <option value="1" <?= (int)$user_data['is_active'] === 1 ? 'selected' : '' ?>>
                Active
            </option>
            <option value="0" <?= (int)$user_data['is_active'] === 0 ? 'selected' : '' ?>>
                Inactive
            </option>
        </select>
    </div>

    <div class="form-group">
        <label for="password">New password (Leave blank to keep current)</label>
        <input type="password" id="password" name="new_password"
            placeholder="New password" autocomplete="new-password">
    </div>

    <div class="form-group">
        <label for="confirmPassword">Confirm Password</label>
        <input type="password" id="confirmPassword" name="password_confirm" autocomplete="new-password"
            placeholder="Confirm password">
    </div>

    <div class="form-actions">
        <button type="submit" class="button-primary">
            Update Operator
        </button>
        <a href="user.php" class="button button-secondary">
            Back to List
        </a>
    </div>

</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>