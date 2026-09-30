<?php
require_once __DIR__ . '/../includes/app.php';
require_login();

$categories = Check_categories();

$departments = check_departments();


$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $product_code = trim($_POST["product_code"]);
    $name = trim($_POST["name"]);
    $category_id = $_POST["category_id"] ?: null;
    $department_id = $_POST["department_id"] ?: null;
    $price = $_POST["price"];
    $quantity = $_POST["quantity"];

    $result = insert_products($product_code, $name, $category_id, $department_id, $price, $quantity);

    if ($result['ok']) {
        // Redirect back index.php category
        redirect('index.php');
        exit();
    } else {
        $error = $result['error'];
    }
}

$page_title = 'Products';
require __DIR__ . '/../includes/header.php';
?>

<h1>➕ Add Product</h1>

<?php if ($error): ?>

    <div class="error">
        <?= $error ?>
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
            required>

    </div>

    <div class="form-group">

        <label>
            Product Name
        </label>

        <input
            type="text"
            name="name"
            required>

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

                <option value="<?= $category["id"] ?>">

                    <?= htmlspecialchars($category["description"]) ?>

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

                <option value="<?= $department["id"] ?>">

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
            required>

    </div>

    <div class="form-group">

        <label>
            Quantity
        </label>

        <input
            type="number"
            name="quantity"
            min="0"
            required>

    </div>

    <div class="form-actions">

        <button
            type="submit"
            class="button-primary">
            Add Product
        </button>

        <a
            href="index.php"
            class="button button-secondary">
            Cancel
        </a>

    </div>

</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>