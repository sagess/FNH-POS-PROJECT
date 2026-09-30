<?php
require_once __DIR__ . '/../includes/app.php';
require_login();

$products = view_products_by_department();
$totalStock = number_of_stock();

$page_title = 'Stock';
require __DIR__ . '/../includes/header.php';
?>
<h1>Store Stock</h1>

<div class="stock-total">

    <div class="stock-total-number">

        <?= money($totalStock) ?>

    </div>

    <div class="stock-total-label">

        Total Units Currently in Stock

    </div>

</div>

<h2>
    Stock by Department and Product
</h2>

<div class="table-container">

    <table>

        <thead>

            <tr>

                <th>Department</th>
                <th>Product Code</th>
                <th>Product</th>
                <th>Units Remaining</th>
                <th>Price</th>

            </tr>

        </thead>

        <tbody>

            <?php foreach ($products as $product): ?>

                <tr>

                    <td>
                        <?= $product["department"] ?? "Unassigned" ?>
                    </td>

                    <td>
                        <?= $product["product_code"] ?>
                    </td>

                    <td>
                        <?= $product["product"] ?>
                    </td>

                    <td>
                        <?= $product["quantity"] ?>
                    </td>

                    <td>
                        <?= money($product["price"]) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>