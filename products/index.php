<?php

// Include necessary files and ensure the user is logged in
require_once __DIR__ . '/../includes/app.php';
require_login();

$products = list_products();

$page_title = 'Products';
require __DIR__ . '/../includes/header.php';
?>

<h1>Products</h1>

<div class="form-actions">

    <a
        href="add.php"
        class="button button-primary">
        ➕ Add Product
    </a>

</div>

<br>

<div class="table-container">

    <table>

        <thead>

            <tr>

                <th>ID</th>
                <th>Product Code</th>
                <th>Name</th>
                <th>Category</th>
                <th>Department</th>
                <th>Price</th>
                <th>Quantity</th>
                <!-- <th>Actions</th>-->

            </tr>

        </thead>

        <tbody>

            <?php foreach ($products as $product): ?>

                <tr>

                    <td>
                        <?= $product["id"] ?>
                    </td>

                    <td>
                        <?= $product["product_code"] ?>
                    </td>

                    <td>
                        <?= $product["name"] ?>
                    </td>

                    <td>
                        <?= $product["category_name"] ?? "None" ?>
                    </td>

                    <td>
                        <?= $product["department_name"] ?? "None" ?>
                    </td>

                    <td>
                        $<?= number_format($product["price"], 2) ?>
                    </td>

                    <td>
                        <?= $product["quantity"] ?>
                    </td>

                    <td>

                        <!--<div class="actions">

                            <a
                                href="edit.php?id=</?= $product["id"] ?>"
                                class="button button-secondary">
                                Edit
                            </a>

                            <a
                                href="delete.php?id=</?= $product["id"] ?>"
                                class="button button-danger"
                                onclick="return confirm('Delete this product?');">
                                Delete
                            </a>

                        </div>-->

                    </td>

                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>