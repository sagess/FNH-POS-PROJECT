<?php

require_once __DIR__ . '/../includes/app.php';
require_login();

$id = (int)($_GET["id"] ?? 0);

if ($id > 0) {

    $stmt = db()->prepare(
        "DELETE FROM products WHERE id = ?"
    );

    $stmt->execute([$id]);

}

header("Location: index.php");

exit;
?>