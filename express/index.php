<?php
require_once __DIR__ . '/../includes/app.php';
//require_once __DIR__ . "/lib.php";
require_login();

$user = current_user();
express_require_access($user);

$can_delete = in_array($user["role"], ["admin", "operator"], true);

/* ---------- Personal shopper marks an order as picked and packed ---------- */
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "pack") {

    $id = (int)($_POST["id"] ?? 0);

    $stmt = db()->prepare(
        "UPDATE express_orders
         SET status = 'Packed', packed_by = ?, packed_at = NOW()
         WHERE id = ? AND status = 'Received'"
    );

    $stmt->execute([$user["username"], $id]);

    header("Location: index.php?done=packed");
    exit;
}

/* ---------- Data ---------- */
$used      = express_used_today();
$available = max(0, EXPRESS_DAILY_CAPACITY - $used);

// Today's orders, plus anything from earlier days that is still open
$orders = db()
    ->query(
        "SELECT o.*,
                (SELECT COALESCE(SUM(i.quantity), 0)
                 FROM express_order_items i
                 WHERE i.express_order_id = o.id) AS item_count
         FROM express_orders o
         WHERE DATE(o.created_at) = CURDATE()
            OR o.status IN ('Received', 'Packed')
         ORDER BY o.created_at DESC"
    )
    ->fetchAll();

/* ---------- Stock levels (products + departments) ---------- */
$stock = db()
    ->query(
        "SELECT p.product_code, p.name,
                COALESCE(d.name, 'Unassigned') AS department,
                p.quantity
         FROM products p
         LEFT JOIN departments d ON d.id = p.department_id
         ORDER BY department, p.name"
    )
    ->fetchAll();

$stock_by_dept = db()
    ->query(
        "SELECT COALESCE(d.name, 'Unassigned') AS department,
                COUNT(*) AS product_count,
                SUM(p.quantity) AS units
         FROM products p
         LEFT JOIN departments d ON d.id = p.department_id
         GROUP BY department
         ORDER BY department"
    )
    ->fetchAll();

$stock_total = 0;
foreach ($stock_by_dept as $row) {
    $stock_total += (int)$row["units"];
}

$messages = [
    "recorded" => "Express order recorded.",
    "updated"  => "Express order updated.",
    "packed"   => "Order marked as packed and ready for collection.",
    "deleted"  => "Express order deleted.",
    "blocked"  => "Collected orders are part of your sales records and can't be deleted.",
];
$done = $_GET["done"] ?? "";

$page_title = "Express orders";
require __DIR__ . "/../includes/header.php";
?>

<h1>Express Orders</h1>

<?php if (isset($messages[$done])): ?>
    <p class="flash flash-success"><?= h($messages[$done]) ?></p>
<?php endif; ?>

<p>
    <strong><?= $available ?></strong> of <?= EXPRESS_DAILY_CAPACITY ?>
    Express orders still available today (<?= $used ?> taken).
</p>

<?php if ($available > 0): ?>
    <a href="add.php" class="button button-primary">Add Express Order</a>
<?php else: ?>
    <p>Daily capacity reached. New Express orders can't be recorded until tomorrow.</p>
<?php endif; ?>

<a href="deliveries.php" class="button button-secondary">Home deliveries</a>

<br><br>

<div class="table-container">

<table>

<thead>

<tr>

<th>ID</th>
<th>Customer</th>
<th>Phone</th>
<th>Items</th>
<th>Placed</th>
<th>Fulfilment</th>
<th>Total due</th>
<th>Status</th>
<th>Actions</th>

</tr>

</thead>

<tbody>

<?php if (!$orders): ?>
<tr><td colspan="9">No Express orders today.</td></tr>
<?php endif; ?>

<?php foreach ($orders as $order): ?>

<?php $t = express_totals((float)$order["order_subtotal"], (float)$order["delivery_fee"]); ?>

<tr>

<td><?= (int)$order["id"] ?></td>

<td><?= h($order["customer_name"]) ?></td>

<td><?= h($order["phone"] ?? "") ?></td>

<td><?= (int)$order["item_count"] ?></td>

<td><?= h($order["created_at"]) ?></td>

<td><?= $order["delivery_method"] === "Delivery" ? "Home delivery" : "Curbside pickup" ?></td>

<td><?= money($t["total"]) ?></td>

<td><?= h($order["status"]) ?></td>

<td>

<div class="actions">

<?php if ($order["status"] === "Received"): ?>
<form method="POST" style="display:inline"
      onsubmit="return confirm('Mark this order as picked and packed?');">
    <input type="hidden" name="action" value="pack">
    <input type="hidden" name="id" value="<?= (int)$order["id"] ?>">
    <button type="submit" class="button-secondary">Mark packed</button>
</form>
<?php endif; ?>

<?php if ($order["status"] === "Packed"): ?>
<a href="checkout.php?id=<?= (int)$order["id"] ?>" class="button button-primary">
    Check out
</a>
<?php endif; ?>

<a href="receipt.php?id=<?= (int)$order["id"] ?>" class="button button-secondary">
    Receipt
</a>

<?php if ($order["status"] !== "Collected"): ?>
<a href="edit.php?id=<?= (int)$order["id"] ?>" class="button button-secondary">
    Edit
</a>
<?php endif; ?>

<?php if ($can_delete && $order["status"] !== "Collected"): ?>
<form method="POST" action="delete.php" style="display:inline"
      onsubmit="return confirm('Delete this Express order?');">
    <input type="hidden" name="id" value="<?= (int)$order["id"] ?>">
    <button type="submit" class="button-danger">Delete</button>
</form>
<?php endif; ?>

</div>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<h2>Store stock</h2>

<p>Total inventory: <strong><?= $stock_total ?></strong> units</p>

<div class="table-container">

<table>

<thead>

<tr>
<th>Department</th>
<th>Products</th>
<th>Units in stock</th>
</tr>

</thead>

<tbody>

<?php foreach ($stock_by_dept as $d): ?>

<tr>
<td><?= h($d["department"]) ?></td>
<td><?= (int)$d["product_count"] ?></td>
<td><?= (int)$d["units"] ?></td>
</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<br>

<div class="table-container">

<table>

<thead>

<tr>
<th>Code</th>
<th>Product</th>
<th>Department</th>
<th>In stock</th>
</tr>

</thead>

<tbody>

<?php foreach ($stock as $s): ?>

<tr>
<td><?= h($s["product_code"]) ?></td>
<td><?= h($s["name"]) ?></td>
<td><?= h($s["department"]) ?></td>
<td><?= (int)$s["quantity"] ?></td>
</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
