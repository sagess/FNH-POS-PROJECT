<?php
require_once __DIR__ . '/../includes/app.php';
require_login();

$user_id = (int)($_GET['id'] ?? 0);


$current_logged_in = current_user();
$acting_user_id = (int)($current_logged_in['user_id'] ?? 0);

if ($user_id > 0) {
    $result = delete_user($user_id, $acting_user_id);

    if ($result['ok']) {
        
        redirect('user.php?deleted=1');
        exit;
    } else {
        $error_message = $result['errors']['general'] ?? 'An error occurred during deletion.';
        die($error_message);
    }
} else {
    die('Invalid User ID.');
}
