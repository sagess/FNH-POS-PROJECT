

-------------------------------------------------$_COOKIE



<h1>✏️ Edit User: <?= htmlspecialchars($user_data['username']) ?></h1>

<?php if ($success): ?>
    <div style="color: green; background: #e6ffe6; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if (isset($errors['general'])): ?>
    <div style="color: red; background: #ffe6e6; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
        <?= htmlspecialchars($errors['general']) ?>
    </div>
<?php endif; ?>

<form method="POST">
    <div>
        <label>Full Name:</label><br>
        <input type="text" name="full_name" value="<?= htmlspecialchars($user_data['full_name']) ?>" required>
        <?php if (isset($errors['full_name'])): ?>
            <span style="color: red; display: block; font-size: 14px;"><?= htmlspecialchars($errors['full_name']) ?></span>
        <?php endif; ?>
    </div>
    <br>

    <div>
        <label>Email Address:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" required>
        <?php if (isset($errors['email'])): ?>
            <span style="color: red; display: block; font-size: 14px;"><?= htmlspecialchars($errors['email']) ?></span>
        <?php endif; ?>
    </div>
    <br>

    <div>
        <label>Role:</label><br>
        <select name="role">
            <option value="operator" <?= $user_data['role'] === 'operator' ? 'selected' : '' ?>>Operator</option>
            <option value="admin" <?= $user_data['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
        <?php if (isset($errors['role'])): ?>
            <span style="color: red; display: block; font-size: 14px;"><?= htmlspecialchars($errors['role']) ?></span>
        <?php endif; ?>
    </div>
    <br>

    <div>
        <label>Status:</label><br>
        <select name="is_active">
            <option value="1" <?= (int)$user_data['is_active'] === 1 ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= (int)$user_data['is_active'] === 0 ? 'selected' : '' ?>>Inactive</option>
        </select>
    </div>
    <br>

    <div style="background: #f9f9f9; padding: 15px; border: 1px solid #ddd; border-radius: 4px;">
        <label><strong>Change Password</strong> (Leave blank to keep current password):</label><br><br>
        <input type="password" name="new_password" autocomplete="new-password">
        <?php if (isset($errors['password'])): ?>
            <span style="color: red; display: block; font-size: 14px;"><?= htmlspecialchars($errors['password']) ?></span>
        <?php endif; ?>
    </div>
    <br>

    <button type="submit" class="button button-primary">Update User</button>
    <a href="user.php" class="button button-secondary" style="margin-left: 10px;">Back to List</a>
</form>

<?php require __DIR__ . '/includes/header.php'; ?>


---------------------------------------------------------------------------$_COOKIE
<?php
require_once __DIR__ . '/includes/app.php';
require_login();

$errors    = [];
$username  = '';
$full_name = '';
$email     = '';
$role      = 'operator';
$is_active = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = $_POST['username'] ?? '';
    $password  = $_POST['password'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $email     = $_POST['email'] ?? '';
    $role      = $_POST['role'] ?? 'operator';
    $is_active = (int)($_POST['is_active'] ?? 1);

    // Call updated PDO user generation function
    $result = create_user($username, $password, $full_name, $email, $role, $is_active);

    if ($result['ok']) {
        header('Location: user.php?success=1');
        exit;
    } else {
        $errors = $result['errors'];
    }
}

$page_title = 'Add User';
require __DIR__ . '/includes/header.php';
?>

<h1>👥 Add New Operator</h1>

<form method="POST">
    <div>
        <label>Username:</label><br>
        <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required>
        <?php if (isset($errors['username'])): ?>
            <span style="color: red; display: block; font-size: 14px;"><?= htmlspecialchars($errors['username']) ?></span>
        <?php endif; ?>
    </div>
    <br>

    <div>
        <label>Full Name:</label><br>
        <input type="text" name="full_name" value="<?= htmlspecialchars($full_name) ?>" required>
        <?php if (isset($errors['full_name'])): ?>
            <span style="color: red; display: block; font-size: 14px;"><?= htmlspecialchars($errors['full_name']) ?></span>
        <?php endif; ?>
    </div>
    <br>

    <div>
        <label>Email:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
        <?php if (isset($errors['email'])): ?>
            <span style="color: red; display: block; font-size: 14px;"><?= htmlspecialchars($errors['email']) ?></span>
        <?php endif; ?>
    </div>
    <br>

    <div>
        <label>Password:</label><br>
        <input type="password" name="password" required>
        <?php if (isset($errors['password'])): ?>
            <span style="color: red; display: block; font-size: 14px;"><?= htmlspecialchars($errors['password']) ?></span>
        <?php endif; ?>
    </div>
    <br>

    <div>
        <label>Role:</label><br>
        <select name="role">
            <option value="operator" <?= $role === 'operator' ? 'selected' : '' ?>>Operator</option>
            <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
        <?php if (isset($errors['role'])): ?>
            <span style="color: red; display: block; font-size: 14px;"><?= htmlspecialchars($errors['role']) ?></span>
        <?php endif; ?>
    </div>
    <br>

    <div>
        <label>Status:</label><br>
        <select name="is_active">
            <option value="1" <?= $is_active === 1 ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= $is_active === 0 ? 'selected' : '' ?>>Inactive</option>
        </select>
    </div>
    <br>

    <button type="submit" class="button button-primary">Save User</button>
    <a href="user.php" class="button button-secondary" style="margin-left: 10px;">Cancel</a>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>



----------------------------------------------------------------
