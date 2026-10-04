<?php

// Include the application setup and functions
require_once __DIR__ . '/includes/app.php';

if (current_user() !== null) {
    redirect(LANDING_PAGE);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (attempt_login(post('username'), post('password'))) {
        redirect(LANDING_PAGE);
    }
    flash_add('error', 'Invalid username or password.');
    redirect('index.php');
}

$page_title = 'Log in';
require __DIR__ . '/includes/header.php';
?>

<div class="login-container">

    <h1> 🔐 Login</h1>

    <form method="POST">

        <div class="form-group">

            <label for="username">
                Username
            </label>

            <input
                type="text"
                id="username"
                name="username"
                required
                autocomplete="username">

        </div>

        <div class="form-group">

            <label for="password">
                Password
            </label>

            <input
                type="password"
                id="password"
                name="password"
                required
                autocomplete="current-password">

        </div>

        <button
            type="submit"
            class="button-primary">
            Login
        </button>

    </form>
    <hr>
    <a class="menu-card">
        <strong>Temporary accounts for logging in</strong>
        <br>
        <p>1: <span>Username: admin </span>, <span>Password: 11111111 </span> and <span>Role: Admin</span></p>
        <p>2: <span>Username: sage1994 </span>, <span>Password: 2222!222 </span> and <span>Role: Operator</span></p>


    </a>
</div>
<?php require_once "includes/footer.php"; ?>