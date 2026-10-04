<?php
require_once __DIR__ . "/../includes/app.php";
require_login();

$user = current_user();

$id = (int)($_GET["id"] ?? $_POST["id"] ?? 0);

$stmt = db()->prepare("SELECT * FROM express_orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found.");
}

$items  = express_load_items($id);
$totals = express_totals((float)$order["order_subtotal"], (float)$order["delivery_fee"]);
$error  = null;

// Handle checkout form submission.
if ($_SERVER["REQUEST_METHOD"] === "POST" && $order["status"] === "Packed") {

    $cash = is_numeric($_POST["cash_tendered"] ?? "") ? round((float)$_POST["cash_tendered"], 2) : -1;

    if ($cash < $totals["total"]) {
        $error = "Cash tendered must cover the total due of " . money($totals["total"]) . ".";
    } else {

        try {
            db()->beginTransaction();

            // Lock the order row to prevent concurrent checkouts.
            $lock = db()->prepare("SELECT status FROM express_orders WHERE id = ? FOR UPDATE");
            $lock->execute([$id]);

            if ($lock->fetchColumn() !== "Packed") {
                db()->rollBack();
                $error = "This order is no longer waiting for checkout.";
            } else {

                // Attempt to take the items from stock. If any item is short, rollback and show an error.
                $take = db()->prepare(
                    "UPDATE products SET quantity = quantity - ? WHERE id = ? AND quantity >= ?"
                );
                $short = null;
                foreach ($items as $it) {
                    $take->execute([$it["quantity"], $it["product_id"], $it["quantity"]]);
                    if ($take->rowCount() === 0) {
                        $short = $it["name"];
                        break;
                    }
                }

                if ($short !== null) {
                    db()->rollBack();
                    $error = "Not enough stock of " . $short . " to complete this order.";
                } else {

                    $sale = db()->prepare(
                        "INSERT INTO sales
                         (operator_id, started_at, checkout_at, subtotal, tax,
                          delivery_fee, total, cash_tendered, change_due, status)
                         VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)"
                    );

                    $sale->execute([
                        $user["user_id"] ?? $user["id"] ?? null,
                        $order["created_at"],
                        $totals["subtotal"],
                        $totals["tax"],
                        $totals["fee"],
                        $totals["total"],
                        $cash,
                        round($cash - $totals["total"], 2),
                        SALE_STATUS_COMPLETED
                    ]);

                    $sale_id = (int)db()->lastInsertId();

                    $line = db()->prepare(
                        "INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, line_total)
                         VALUES (?, ?, ?, ?, ?)"
                    );
                    foreach ($items as $it) {
                        $line->execute([
                            $sale_id,
                            $it["product_id"],
                            $it["quantity"],
                            $it["unit_price"],
                            $it["line_total"]
                        ]);
                    }

                    $done = db()->prepare(
                        "UPDATE express_orders
                         SET status = 'Collected', collected_by = ?, collected_at = NOW(), sale_id = ?
                         WHERE id = ?"
                    );
                    $done->execute([$user["username"], $sale_id, $id]);

                    db()->commit();

                    header("Location: receipt.php?id=" . $id . "&done=checkedout");
                    exit;
                }
            }
        } catch (Throwable $e) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            $error = "Checkout failed and nothing was changed. Please try again.";
        }
    }
}

$page_title = "Check out Express order";
require __DIR__ . "/../includes/header.php";
?>

<h1>Check Out Express Order #<?= (int)$order["id"] ?></h1>

<?php if ($error): ?>
    <p class="flash flash-error"><?= h($error) ?></p>
<?php endif; ?>

<?php if ($order["status"] !== "Packed"): ?>

    <p>
        This order is <strong><?= h($order["status"]) ?></strong>.
        <?= $order["status"] === "Received" ? "It must be marked as packed before it can be checked out." : "It can't be checked out." ?>
    </p>

    <a href="index.php" class="button button-secondary">Back</a>

<?php else: ?>

    <p>
        Customer: <strong><?= h($order["customer_name"]) ?></strong>
        (<?= h($order["phone"] ?? "") ?>)<br>
        Fulfilment: <?= $order["delivery_method"] === "Delivery" ? "Home delivery" : "Curbside pickup" ?>
        <?php if ($order["delivery_method"] === "Delivery"): ?>
            <br>Deliver to: <?= h($order["delivery_address"]) ?>
        <?php endif; ?>
    </p>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit price</th>
                    <th>Line total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><?= h($it["name"]) ?></td>
                        <td><?= (int)$it["quantity"] ?></td>
                        <td><?= money($it["unit_price"]) ?></td>
                        <td><?= money($it["line_total"]) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3">Subtotal</td>
                    <td><?= money($totals["subtotal"]) ?></td>
                </tr>
                <tr>
                    <td colspan="3">Tax</td>
                    <td><?= money($totals["tax"]) ?></td>
                </tr>
                <?php if ($totals["fee"] > 0): ?>
                    <tr>
                        <td colspan="3">Home delivery fee</td>
                        <td><?= money($totals["fee"]) ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td colspan="3"><strong>Total due</strong></td>
                    <td><strong><?= money($totals["total"]) ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <form method="POST">
        <input type="hidden" name="id" value="<?= (int)$order["id"] ?>">

        <div class="form-group">
            <label>Cash tendered ($)</label>
            <input type="number" name="cash_tendered" min="0" step="0.01"
                value="<?= h(number_format($totals["total"], 2, ".", "")) ?>" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="button-primary">Complete checkout</button>
            <a href="index.php" class="button button-secondary">Cancel</a>
        </div>
    </form>

<?php endif; ?>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>