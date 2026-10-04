<?php
require_once __DIR__ . '/../includes/app.php';
require_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    flash_add('error', 'Invalid request.');
    redirect('new.php');
}

$sale_id = (int)($_POST["sale_id"] ?? 0);
$product_code = trim($_POST["product_code"] ?? "");

// Validate sale_id
$sale = require_sale($sale_id, 'new.php', 'OPEN');

// Validate product_code
if ($product_code === "") {
    flash_add('error', 'Please scan or enter a product code.');
    redirect("new.php?sale_id=$sale_id");
}

try {

    db()->beginTransaction();

    $stmt = db()->prepare(
        "SELECT *
         FROM products
         WHERE product_code = ?
         FOR UPDATE"
    );

    $stmt->execute([$product_code]);
    $product = $stmt->fetch();

    if (!$product) {
        throw new Exception("Product was not found.");
    }

    if ((int)$product["quantity"] <= 0) {
        throw new Exception("This product is out of stock.");
    }

    // Decrement the product quantity in the products table
    $stmt = db()->prepare(
        "UPDATE products
         SET quantity = quantity - 1
         WHERE id = ?
         AND quantity > 0"
    );

    $stmt->execute([$product["id"]]);

    if ($stmt->rowCount() !== 1) {
        throw new Exception("Product is out of stock.");
    }


    // Check if the product is already in the sale_items table for this sale
    $stmt = db()->prepare(
        "SELECT id
         FROM sale_items
         WHERE sale_id = ?
         AND product_id = ?"
    );

    $stmt->execute([$sale_id, $product["id"]]);
    $existing = $stmt->fetch();

    if ($existing) {

        $stmt = db()->prepare(
            "UPDATE sale_items
             SET quantity = quantity + 1,
                 line_total = line_total + ?
             WHERE id = ?"
        );

        $stmt->execute([$product["price"], $existing["id"]]);
    } else {

        $stmt = db()->prepare(
            "INSERT INTO sale_items
             (sale_id, product_id, quantity, unit_price, line_total)
             VALUES (?, ?, 1, ?, ?)"
        );

        $stmt->execute([
            $sale_id,
            $product["id"],
            $product["price"],
            $product["price"],
        ]);
    }

    db()->commit();

    // Back to the scan screen to add the NEXT item.
    redirect("new.php?sale_id=$sale_id");
} catch (Throwable $e) {

    if (db()->inTransaction()) {
        db()->rollBack();
    }

    flash_add('error', $e->getMessage());
    redirect("new.php?sale_id=$sale_id");
}
