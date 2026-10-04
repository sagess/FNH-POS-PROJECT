<?php
require_once __DIR__ . '/../includes/app.php';
require_login();

$errors    = [];
$username  = '';
$full_name = '';
$email     = '';
$role      = 'operator';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = $_POST['username'] ?? '';
    $password  = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $email     = $_POST['email'] ?? '';
    $role      = $_POST['role'] ?? 'operator';

    if ($password !== $confirm) {
        $errors['password_confirm'] = 'The two passwords do not match.';
    } else {
        $result = create_user($username, $password, $full_name, $email, $role);

        if ($result['ok']) {
            redirect('user.php?success=1');
            exit;
        } else {

            $errors = $result['errors'];
        }
    }
}

$page_title = 'New Operator';

require __DIR__ . '/../includes/header.php';
?>

<h1> ➕ Create User</h1>
<p class="page-subtitle">
    Enter the information below to create a new operator.
</p>

<form method="POST" action="add-user.php">

    <div class="form-group">

        <label>
            Username
        </label>

        <input
            type="text"
            id="username" name="username"
            placeholder="Enter username" maxlength="30" autocomplete="off" autocapitalize="none" required>

    </div>
    <div class="form-group">

        <label>
            Full Name
        </label>

        <input
            type="text"
            placeholder="Enter full name" id="full_name" name="full_name"
            maxlength="80" required>

    </div>
    <div class="form-group">

        <label>
            Email
        </label>

        <input type="email" id="email" name="email"
            placeholder="operator@example.com" required>

    </div>
    <div class="form-group">

        <label>
            Role
        </label>

        <select id="role" name="role" required>

            <option value="operator">
                Operator
            </option>

            <option value="admin">
                Administrator
            </option>

        </select>

    </div>
    <div class="form-group">

        <label>
            Password
        </label>

        <input type="password" id="password" name="password"
            placeholder="Enter password" autocomplete="new-password" required>

    </div>
    <div class="form-group">

        <label>
            Confirm Password
        </label>

        <input type="password" id="confirmPassword" name="password_confirm" autocomplete="new-password"
            placeholder="Confirm password" required>

    </div>

    <div class="form-actions">

        <button
            type="submit"
            class="button-primary">
            Add User
        </button>

        <a
            href="user.php"
            class="button button-secondary">
            Cancel
        </a>

    </div>

</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>