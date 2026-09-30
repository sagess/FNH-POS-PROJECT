<?php
require_once __DIR__ . '/../includes/app.php';
require_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    flash_add('error', 'Invalid request.');
    redirect('new.php');
}

$sale_id = (int)($_POST["sale_id"] ?? 0);

$sale = require_sale($sale_id, 'new.php', 'OPEN');

try {

    db()->beginTransaction();


    $stmt = db()->prepare(
        "SELECT product_id, quantity
         FROM sale_items
         WHERE sale_id = ?"
    );
    $stmt->execute([$sale_id]);
    $items = $stmt->fetchAll();

    $restore = db()->prepare(
        "UPDATE products
         SET quantity = quantity + ?
         WHERE id = ?"
    );

    foreach ($items as $item) {
        $restore->execute([$item['quantity'], $item['product_id']]);
    }

    $stmt = db()->prepare(
        "UPDATE sales
         SET status = 'CANCELLED',
             cancelled_at = NOW()
         WHERE id = ?
         AND operator_id = ?
         AND status = 'OPEN'"
    );
    $stmt->execute([$sale_id, $sale['operator_id']]);

    if ($stmt->rowCount() !== 1) {
        throw new Exception('This sale is no longer open.');
    }

    db()->commit();
} catch (Throwable $e) {

    if (db()->inTransaction()) {
        db()->rollBack();
    }

    flash_add('error', $e->getMessage());
    redirect("new.php?sale_id=$sale_id");
}

flash_add('success', "Sale #$sale_id was cancelled.");
redirect('new.php');
