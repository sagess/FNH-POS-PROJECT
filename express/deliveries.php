<?php
require_once __DIR__ . '/../includes/app.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/lib.php';
require_login();

$user = current_user();
express_require_access($user);

// Date filter (defaults to today)
$date = $_GET['date'] ?? '';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = date('Y-m-d');
}

$stmt = db()->prepare(
    "SELECT * FROM express_orders
     WHERE DATE(created_at) = ? AND status <> 'Cancelled'
     ORDER BY created_at ASC"
);
$stmt->execute(array($date));
$orders = $stmt->fetchAll();

$delivery_count = 0;
$curbside_count = 0;
$fees_total     = 0.0;
foreach ($orders as $o) {
    if ($o['delivery_method'] === 'Delivery') {
        $delivery_count++;
        $fees_total += (float)$o['delivery_fee'];
    } else {
        $curbside_count++;
    }
}

$page_title = 'Express deliveries';
require __DIR__ . '/../includes/header.php';
?>

<h1>Express Orders: Home Delivery</h1>

<p>
    <a class="button button-secondary" href="index.php">Back to Express orders</a>
</p>

<form method="GET" class="form-group">
    <label>Date</label>
    <input type="date" name="date" value="<?php echo h($date); ?>">
    <button type="submit" class="button-primary">Show</button>
</form>

<section class="menu-section">
    <p>
        <strong><?php echo $delivery_count; ?></strong> home delivery,
        <strong><?php echo $curbside_count; ?></strong> curbside pickup.
        Delivery fees for this day: <strong><?php echo money($fees_total); ?></strong>
        (<?php echo money(DELIVERY_FEE); ?> per delivery).
    </p>
    <p>
        <small>
            The van runs for orders placed between 8 AM and 4 PM.
            Orders placed outside those hours are curbside pickup only.
        </small>
    </p>
</section>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>Placed</th>
                <th>Fulfilment</th>
                <th>Delivery address</th>
                <th>Delivery fee</th>
                <th>Status</th>
                <th>Receipt</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$orders): ?>
                <tr><td colspan="9">No Express orders for this date.</td></tr>
            <?php endif; ?>
            <?php foreach ($orders as $o): ?>
                <?php $is_delivery = ($o['delivery_method'] === 'Delivery'); ?>
                <tr>
                    <td><?php echo (int)$o['id']; ?></td>
                    <td><?php echo h($o['customer_name']); ?></td>
                    <td><?php echo h($o['phone'] ?? ''); ?></td>
                    <td><?php echo h(date('g:i A', strtotime($o['created_at']))); ?></td>
                    <td><strong><?php echo $is_delivery ? 'Home delivery' : 'Curbside pickup'; ?></strong></td>
                    <td><?php echo $is_delivery ? h($o['delivery_address']) : '&mdash;'; ?></td>
                    <td><?php echo money($o['delivery_fee']); ?></td>
                    <td><?php echo h($o['status']); ?></td>
                    <td><a class="button button-secondary" href="receipt.php?id=<?php echo (int)$o['id']; ?>">View</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
