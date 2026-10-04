<?php
require_once __DIR__ . '/../includes/app.php';
require_login();

$sale_id = (int)($_GET["sale_id"] ?? 0);


$sale = require_sale($sale_id, 'new.php', 'OPEN');


// Retrieve the items for this sale
$items = list_sale_items_products($sale_id);

// If there are no items in the sale, redirect back to the sale page with an error message.
if (empty($items)) {
    flash_add('error', 'Add at least one item before checking out.');
    redirect("new.php?sale_id=$sale_id");
}

$subtotal = 0;

foreach ($items as $item) {
    $subtotal += (float)$item["line_total"];
}

$tax = round($subtotal * TAX_RATE, 2);
$total = round($subtotal + $tax, 2);

$page_title = "Checkout - Sale #$sale_id";


require __DIR__ . '/../includes/header.php';
?>

<h1>
    Checkout - Sale #<?= $sale_id ?>
</h1>

<div class="table-container">

    <table>

        <thead>

            <tr>

                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>

            </tr>

        </thead>

        <tbody>

            <?php foreach ($items as $item): ?>

                <tr>

                    <td>
                        <?= h($item["name"]) ?>
                    </td>

                    <td>
                        <?= h($item["quantity"]) ?>
                    </td>

                    <td>
                        <?= money($item["unit_price"]) ?>
                    </td>

                    <td>
                        <?= money($item["line_total"]) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

</div>


<div class="receipt-summary">

    <div class="receipt-summary-row">

        <span>
            Subtotal
        </span>

        <strong>
            <?= money($subtotal) ?>
        </strong>

    </div>


    <div class="receipt-summary-row">

        <span>
            Sales Tax (7.75%)
        </span>

        <strong>
            <?= money($tax) ?>
        </strong>

    </div>


    <div class="total-due">

        <div class="receipt-summary-row">

            <span>
                Total Due
            </span>

            <strong>
                <?= money($total) ?>
            </strong>

        </div>

    </div>

</div>


<h2>
    Take Customer Payment
</h2>

<form
    method="POST"
    action="complete.php">

    <input
        type="hidden"
        name="sale_id"
        value="<?= $sale_id ?>">

    <div class="form-group">

        <label for="cash_tendered">
            Cash Tendered
        </label>

        <input
            type="number"
            id="cash_tendered"
            name="cash_tendered"
            min="<?= number_format($total, 2, '.', '') ?>"
            step="0.01"
            required>

    </div>

    <button
        type="submit"
        class="button-primary">
        Complete Sale
    </button>

</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>