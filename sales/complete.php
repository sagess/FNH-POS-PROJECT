<?php
require_once __DIR__ . '/../includes/app.php';
require_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect('new.php');
}

$sale_id = (int)($_POST["sale_id"] ?? 0);
$cash_tendered = (float)($_POST["cash_tendered"] ?? 0);

$sale = require_sale($sale_id, 'new.php', 'OPEN');

$items = show_all_sale($sale_id);

$subtotal = 0;

foreach ($items as $item) {
    $subtotal += (float)$item["line_total"];
}

if ($subtotal <= 0) {
    flash_add('error', 'Add at least one item before completing the sale.');
    redirect("new.php?sale_id=$sale_id");
}

$tax = round($subtotal * TAX_RATE, 2);
$total = round($subtotal + $tax, 2);

if ($cash_tendered < $total) {
    flash_add('error', 'Customer payment is not enough. Total due is $' . money($total));
    redirect("checkout.php?sale_id=$sale_id");
}

$change = round($cash_tendered - $total, 2);


// Update the sale record in the database to mark it as completed.
$stmt = db()->prepare(
    "UPDATE sales
     SET checkout_at = NOW(),
         subtotal = ?,
         tax = ?,
         total = ?,
         cash_tendered = ?,
         change_due = ?,
         status = 'COMPLETED'
     WHERE id = ?
     AND operator_id = ?
     AND status = 'OPEN'"
);

$stmt->execute([
    $subtotal,
    $tax,
    $total,
    $cash_tendered,
    $change,
    $sale_id,
    $sale['operator_id'],
]);

if ($stmt->rowCount() !== 1) {

    flash_add('error', 'This sale is no longer open.');
    redirect('new.php');
}



$page_title = 'Complete';
require __DIR__ . '/../includes/header.php';
?>

<div class="success">

    Sale completed successfully.

</div>

<h1>
    Sale #<?= $sale_id ?>
</h1>

<div class="receipt-summary">

    <div class="receipt-summary-row">

        <span>
            Subtotal
        </span>

        <strong>
            $<?= money($subtotal) ?>
        </strong>

    </div>

    <div class="receipt-summary-row">

        <span>
            Sales Tax
        </span>

        <strong>
            $<?= money($tax) ?>
        </strong>

    </div>

    <div class="receipt-summary-row">

        <span>
            Total
        </span>

        <strong>
            $<?= money($total) ?>
        </strong>

    </div>

    <div class="receipt-summary-row">

        <span>
            Cash Tendered
        </span>

        <strong>
            $<?= money($cash_tendered) ?>
        </strong>

    </div>

    <div class="receipt-summary-row">

        <span>
            Change
        </span>

        <strong>
            $<?= money($change) ?>
        </strong>

    </div>

    <div class="receipt-summary-row">

        <span>
            Checkout Time
        </span>

        <strong>
            <?= date("Y-m-d H:i:s") ?>
        </strong>

    </div>

</div>

<br>

<a
    href="new.php"
    class="button button-primary">
    Start Another Sale
</a>

<a
    href="../menu.php"
    class="button button-secondary">
    Return to menu
</a>
<?php require __DIR__ . '/../includes/footer.php'; ?>