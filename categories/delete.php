<?php
require_once __DIR__ . '/../includes/app.php';
require_login();

// Get the category ID from the query 
$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // Execute the deletion query 
    delete_category($id);
}

//Clean fallback redirection back to the index view
if (function_exists('redirect')) {
    redirect('index.php');
} else {
    redirect('index.php');
}
exit();
