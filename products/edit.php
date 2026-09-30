<?php

require_once "../includes/auth.php";
require_once "../includes/database.php";

$id = (int)($_GET["id"] ?? 0);

$stmt = $pdo->prepare(
    "SELECT * FROM products WHERE id = ?"
);

$stmt->execute([$id]);

$product = $stmt->fetch();

if (!$product) {
    die("Product not found.");
}

$categories = $pdo
    ->query("SELECT id, name FROM categories ORDER BY name")
    ->fetchAll();

$departments = $pdo
    ->query("SELECT id, name FROM departments ORDER BY name")
    ->fetchAll();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $product_code = trim($_POST["product_code"]);
    $name = trim($_POST["name"]);
    $category_id = $_POST["category_id"] ?: null;
    $department_id = $_POST["department_id"] ?: null;
    $price = $_POST["price"];
    $quantity = $_POST["quantity"];

    try {

        $stmt = $pdo->prepare(
            "UPDATE products
             SET product_code = ?,
                 name = ?,
                 category_id = ?,
                 department_id = ?,
                 price = ?,
                 quantity = ?
             WHERE id = ?"
        );

        $stmt->execute([
            $product_code,
            $name,
            $category_id,
            $department_id,
            $price,
            $quantity,
            $id
        ]);

        header("Location: index.php");
        exit;

    } catch (PDOException $e) {

        $error = "Unable to update product.";

    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Edit Product</title>

<link
    rel="stylesheet"
    href="../assets/css/style.css"
>

</head>

<body>

<?php require_once "../includes/header.php"; ?>

<div class="page-layout">

<?php require_once "../includes/navbar.php"; ?>

<main class="main-content">

<h1>Edit Product</h1>

<?php if ($error): ?>

<div class="error">
<?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>

<form method="POST">

<div class="form-group">

<label>
Product Code / Barcode
</label>

<input
    type="text"
    name="product_code"
    value="<?= htmlspecialchars($product["product_code"]) ?>"
    required
>

</div>

<div class="form-group">

<label>
Product Name
</label>

<input
    type="text"
    name="name"
    value="<?= htmlspecialchars($product["name"]) ?>"
    required
>

</div>

<div class="form-group">

<label>
Category
</label>

<select name="category_id">

<option value="">
-- Select Category --
</option>

<?php foreach ($categories as $category): ?>

<option
    value="<?= $category["id"] ?>"
    <?= $product["category_id"] == $category["id"] ? "selected" : "" ?>
>

<?= htmlspecialchars($category["name"]) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="form-group">

<label>
Department
</label>

<select name="department_id">

<option value="">
-- Select Department --
</option>

<?php foreach ($departments as $department): ?>

<option
    value="<?= $department["id"] ?>"
    <?= $product["department_id"] == $department["id"] ? "selected" : "" ?>
>

<?= htmlspecialchars($department["name"]) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="form-group">

<label>
Price
</label>

<input
    type="number"
    name="price"
    min="0"
    step="0.01"
    value="<?= htmlspecialchars($product["price"]) ?>"
    required
>

</div>

<div class="form-group">

<label>
Quantity
</label>

<input
    type="number"
    name="quantity"
    min="0"
    value="<?= htmlspecialchars($product["quantity"]) ?>"
    required
>

</div>

<div class="form-actions">

<button
    type="submit"
    class="button-primary"
>
    Update Product
</button>

<a
    href="index.php"
    class="button button-secondary"
>
    Cancel
</a>

</div>

</form>

</main>

</div>

<?php require_once "../includes/footer.php"; ?>

</body>

</html>