<?php

function validate_role(string $role): string
{
    return in_array($role, ['admin', 'operator'], true) ? '' : 'Invalid role.';
}


function list_users(?string $role = null): array
{
    $sql = 'SELECT user_id, username, full_name, email, role, is_active, created_at FROM users';
    if ($role !== null) {
        $sql .= ' WHERE role = ?';
    }
    $sql .= ' ORDER BY role, username';

    $stmt = db()->prepare($sql);
    $params = ($role !== null) ? [$role] : [];
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function create_user(
    string $username,
    string $password,
    string $full_name,
    string $email,
    string $role = 'operator',
): array {
    $username  = strtolower(trim($username));
    $full_name = trim($full_name);
    $email     = trim($email);

    $errors = array_filter([
        'username'  => validate_username($username),
        'full_name' => validate_full_name($full_name),
        'email'     => filter_var($email, FILTER_VALIDATE_EMAIL) ? '' : 'Invalid email format.',
        'role'      => validate_role($role),
        'password'  => validate_password($password),
    ]);


    if (!isset($errors['username']) && username_exists($username)) {
        $errors['username'] = "The username '$username' is already taken.";
    }

    if ($errors) {
        return ['ok' => false, 'user_id' => null, 'errors' => $errors];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = db()->prepare(
            'INSERT INTO users (username, password_hash, full_name, email, role) 
             VALUES (?, ?, ?, ?, ?)'
        );

        $stmt->execute([$username, $hash, $full_name, $email, $role]);

        $new_id = (int)db()->lastInsertId();
        return ['ok' => true, 'user_id' => $new_id, 'errors' => []];
    } catch (PDOException $e) {

        if ($e->getCode() == '23000') {
            return [
                'ok' => false,
                'user_id' => null,
                'errors' => ['username' => "The username '$username' is already taken."]
            ];
        }
        throw $e;
    }
}


function username_exists(string $username): bool
{
    $username = strtolower(trim($username));

    $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    $stmt->execute([$username]);

    return (int)$stmt->fetchColumn() > 0;
}



function validate_username(string $username): string
{
    $username = trim($username);
    if ($username === '') {
        return 'Username is required.';
    }
    if (strlen($username) < 3 || strlen($username) > 30) {
        return 'Username must be between 3 and 30 characters.';
    }
    if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $username)) {
        return 'Username can only contain letters, numbers, underscores, and hyphens.';
    }
    return '';
}

function validate_full_name(string $full_name): string
{
    $full_name = trim($full_name);
    if ($full_name === '') {
        return 'Full name is required.';
    }
    if (strlen($full_name) < 2 || strlen($full_name) > 100) {
        return 'Full name must be between 2 and 100 characters.';
    }
    return '';
}

function validate_password(string $password): string
{
    if ($password === '') {
        return 'Password is required.';
    }
    if (strlen($password) < 8) {
        return 'Password must be at least 8 characters long.';
    }
    return '';
}


function delete_user(int $user_id, int $acting_user_id): array
{
    $target = get_user($user_id);
    if ($target === null) {
        return ['ok' => false, 'errors' => ['general' => 'That account no longer exists.']];
    }
    if ($user_id === $acting_user_id) {
        return ['ok' => false, 'errors' => ['general' => 'You cannot delete your own account.']];
    }

    $is_active_admin = ($target['role'] === 'admin' && (int)$target['is_active'] === 1);

    // Obtain the global PDO connection object
    $pdo = db();

    // Start transaction using PDO
    $pdo->beginTransaction();
    try {
        if ($is_active_admin && count_active_admins(true) <= 1) {
            $pdo->rollBack();
            return ['ok' => false, 'errors' => ['general' => 'This is the last active admin; it cannot be deleted.']];
        }

        $stmt = $pdo->prepare('DELETE FROM users WHERE user_id = ?');
        $stmt->execute([$user_id]);

        $deleted = $stmt->rowCount();

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    if ($deleted !== 1) {
        return ['ok' => false, 'errors' => ['general' => 'That account no longer exists.']];
    }
    return ['ok' => true, 'errors' => []];
}


function get_user(int $user_id): ?array
{
    $stmt = db()->prepare('SELECT user_id, username, full_name, email, role, is_active, created_at FROM users WHERE user_id = ?');
    $stmt->execute([$user_id]);

    $user = $stmt->fetch();
    return $user ?: null;
}

function count_active_admins(bool $exclude_system_roles = true): int
{
    $sql = "SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1";

    $stmt = db()->prepare($sql);
    $stmt->execute();

    return (int)$stmt->fetchColumn();
}


function update_user(
    int $user_id,
    int $acting_user_id,
    string $full_name,
    string $email,
    string $role,
    bool $is_active,
    ?string $new_password = null
): array {
    $current = get_user($user_id);
    if ($current === null) {
        return ['ok' => false, 'errors' => ['general' => 'That account no longer exists.']];
    }

    $full_name = trim($full_name);
    $email     = trim($email);

    $errors = array_filter([
        'full_name' => validate_full_name($full_name),
        'email'     => filter_var($email, FILTER_VALIDATE_EMAIL) ? '' : 'Invalid email format.',
        'role'      => validate_role($role),
    ]);

    if (!isset($errors['email']) && email_exists_excluding_user($email, $user_id)) {
        $errors['email'] = "The email address '$email' is already in use by another account.";
    }


    $changing_password = ($new_password !== null && $new_password !== '');
    if ($changing_password) {
        $password_error = validate_password($new_password);
        if ($password_error !== '') {
            $errors['password'] = $password_error;
        }
    }

    $was_active_admin   = ($current['role'] === 'admin' && (int)$current['is_active'] === 1);
    $stays_active_admin = ($role === 'admin' && $is_active);

    if ($user_id === $acting_user_id && $was_active_admin && !$stays_active_admin) {
        $errors['role'] = 'You cannot demote or deactivate your own account; ask another admin.';
    }

    if ($errors) {
        return ['ok' => false, 'errors' => $errors];
    }

    $pdo = db();

    // Begin PDO Transaction
    $pdo->beginTransaction();
    try {
        if ($was_active_admin && !$stays_active_admin && count_active_admins(true) <= 1) {
            $pdo->rollBack();
            return ['ok' => false, 'errors' => ['role' => 'This is the last active admin; create another admin first.']];
        }

        $active = $is_active ? 1 : 0;

        if ($changing_password) {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'UPDATE users SET full_name = ?, email = ?, role = ?, is_active = ?, password_hash = ? WHERE user_id = ?'
            );
            $stmt->execute([$full_name, $email, $role, $active, $hash, $user_id]);
        } else {
            // Removed password_hash update field when password isn't changing
            $stmt = $pdo->prepare(
                'UPDATE users SET full_name = ?, email = ?, role = ?, is_active = ? WHERE user_id = ?'
            );
            $stmt->execute([$full_name, $email, $role, $active, $user_id]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    return ['ok' => true, 'errors' => []];
}

function email_exists_excluding_user(string $email, int $user_id): bool
{
    $email = strtolower(trim($email));
    $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND user_id != ?');
    $stmt->execute([$email, $user_id]);

    return (int)$stmt->fetchColumn() > 0;
}
