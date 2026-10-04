<?php
require_once __DIR__ . '/../includes/app.php';

// Get the current user
require_login();

$user = current_user();

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM express_orders WHERE id = ?");
$stmt->execute(array($id));
$o = $stmt->fetch();

if (!$o) {
    die('Express order not found.');
}

$items       = express_load_items($id);
$is_delivery = ($o['delivery_method'] === 'Delivery');
$t           = express_totals((float)$o['order_subtotal'], (float)$o['delivery_fee']);


// Payment details exist once the order has been checked out as a sale.
$sale = null;
if (!empty($o['sale_id'])) {
    $s = db()->prepare("SELECT * FROM sales WHERE id = ?");
    $s->execute(array((int)$o['sale_id']));
    $sale = $s->fetch();
}

$page_title = 'Express receipt';
require __DIR__ . '/../includes/header.php';
?>

<h1>Sales Receipt</h1>

<?php if (($_GET['done'] ?? '') === 'checkedout'): ?>
    <p class="flash flash-success">Order checked out and recorded as a sale.</p>
<?php endif; ?>

<section class="menu-section" id="receipt">
    <h2>FnH Groceries: Express Order #<?php echo (int)$o['id']; ?></h2>

    <p>
        Customer: <strong><?= h($o['customer_name']); ?></strong>
        <?php if (!empty($o['phone'])): ?>(<?= h($o['phone']); ?>)<?php endif; ?><br>
        Ordered: <?= h($o['created_at']); ?><br>
        Status: <?= h($o['status']); ?><br>
        Fulfilment: <?= $is_delivery ? 'Home delivery' : 'Curbside pickup'; ?>
        <?php if ($is_delivery): ?>
            <br>Deliver to: <?= h($o['delivery_address']); ?>
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
                        <td><?= h($it['name']); ?></td>
                        <td><?= (int)$it['quantity']; ?></td>
                        <td><?= money($it['unit_price']); ?></td>
                        <td><?= money($it['line_total']); ?></td>
                    </tr>
                <?php endforeach; ?>

                <tr>
                    <td colspan="3">Subtotal</td>
                    <td><?= money($t['subtotal']); ?></td>
                </tr>
                <tr>
                    <td colspan="3">Tax</td>
                    <td><?= money($t['tax']); ?></td>
                </tr>

                <?php if ($is_delivery): ?>
                    <tr>
                        <td colspan="3">Home delivery fee</td>
                        <td><?= money($t['fee']); ?></td>
                    </tr>
                <?php endif; ?>

                <tr>
                    <td colspan="3"><strong>Total due</strong></td>
                    <td><strong><?= money($t['total']); ?></strong></td>
                </tr>

                <?php if ($sale): ?>
                    <tr>
                        <td colspan="3">Cash tendered</td>
                        <td><?= money($sale['cash_tendered']); ?></td>
                    </tr>
                    <tr>
                        <td colspan="3">Change due</td>
                        <td><?= money($sale['change_due']); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<p>
    <button type="button" class="button-primary no-print" onclick="window.print()">Print receipt</button>

    <?php if ($o['status'] === 'Packed'): ?>
        <a class="button button-primary" href="checkout.php?id=<?= (int)$o['id']; ?>">Check out</a>
    <?php endif; ?>
    <a class="button button-secondary" href="index.php">Back</a>
</p>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>