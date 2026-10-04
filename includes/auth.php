<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Returns the current logged-in user, or null if not logged in.
function current_user()
{
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

// Redirects to the given URL and exits the script.
function require_login(): void
{
    if (current_user() === null) {
        flash_add('error', 'Please log in first.');
        redirect('index.php');
    }
}

// Redirects to the given URL and exits the script.
function require_role(string ...$roles): void
{
    require_login();
    $user = current_user();
    if (!in_array($user['role'], $roles, true)) {
        flash_add('error', 'Your account role (' . $user['role'] . ') may not open that page.');
        redirect(LANDING_PAGE);
    }
}

// Attempts to log in a user with the given username and password.
function attempt_login(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT user_id, username, password_hash, full_name, role, is_active
                           FROM users WHERE username = ?');
    $stmt->execute(array($username));
    $row = $stmt->fetch();
    if ($row && (int)$row['is_active'] === 1 && password_verify($password, $row['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = array(
            'user_id'   => (int)$row['user_id'],
            'username'  => $row['username'],
            'full_name' => $row['full_name'],
            'role'      => $row['role'],
        );
        return true;
    }
    return false;
}

// Logs out the current user by clearing the session.
function logout_user(): void
{
    $_SESSION = array();
    session_destroy();
}

// Checks if the current user has the specified role.
function require_sale(int $sale_id, string $redirect_url = 'new.php', ?string $required_status = null): array
{
    require_login();

    // Get the operator ID from the session, defaulting to 0 if not set.
    $operator_id = (int)($_SESSION['user']['user_id'] ?? 0);
    if ($operator_id <= 0) {
        flash_add('error', 'Please log in to continue.');
        redirect('index.php');
    }


    // Check if a valid sale ID is provided.
    if ($sale_id <= 0) {
        flash_add('error', 'No sale was specified.');
        redirect($redirect_url);
    }

    // Fetch the sale from the database using the provided sale ID.
    $stmt = db()->prepare('SELECT * FROM sales WHERE id = ?');
    $stmt->execute([$sale_id]);
    $sale = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sale) {
        flash_add('error', "Sale #$sale_id was not found.");
        redirect($redirect_url);
    }

    // Check if the sale belongs to the current operator.
    if ((int)$sale['operator_id'] !== $operator_id) {
        flash_add('error', "Sale #$sale_id does not belong to you.");
        redirect($redirect_url);
    }

    // Check if the sale has the required status, if specified.
    if ($required_status !== null && $sale['status'] !== $required_status) {
        flash_add('error', "Sale #$sale_id is not $required_status.");
        redirect($redirect_url);
    }

    // Return the sale details if all checks pass.
    return $sale;
}
