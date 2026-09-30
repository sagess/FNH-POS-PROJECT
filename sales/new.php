<?php
require_once __DIR__ . '/../includes/app.php';
require_login();

$sale_id = (int)($_GET["sale_id"] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $sale_id === 0) {
    $operator_id = (int)($_SESSION['user']['user_id'] ?? 0);

    $stmt = db()->prepare(
        "INSERT INTO sales (operator_id, status, started_at)
         VALUES (?, 'OPEN', NOW())"
    );
    $stmt->execute([$operator_id]);

    $new_sale_id = (int)db()->lastInsertId();

    redirect("new.php?sale_id=$new_sale_id");
}

$sale = null;
$items = [];
$total = 0.0;

if ($sale_id > 0) {
    $sale = require_sale($sale_id, 'new.php', 'OPEN'); 

    $items = list_sale_items($sale_id);

    foreach ($items as $item) {
        $total += $item['quantity'] * $item['price'];
    }
}

$products = ($sale_id > 0) ? list_products() : []; 

$page_title = 'New Sale';

require __DIR__ . '/../includes/header.php';

$flash = flash_take();
?>

<?php if ($flash): ?>
    <div class="flash flash-<?= $flash['type'] ?>">
        <?= $flash['message'] ?>
    </div>
<?php endif; ?>

<?php if ($sale_id === 0): ?>

    <h1>Start New Sale</h1>

    <p>
        Click the button to start a new sale at the register.
    </p>

    <form method="POST">

        <button
            type="submit"
            class="button-primary">
            Start New Sale
        </button>

    </form>

<?php else: ?>

    <div class="pos-layout">

        <div class="pos-main">

            <h1>
                Sale #<?= $sale_id ?>
            </h1>

            <h2>
                Scan Product
            </h2>

            <form
                method="POST"
                action="add_item.php">

                <input
                    type="hidden"
                    name="sale_id"
                    value="<?= $sale_id ?>">

                <div class="form-group">

                    <label for="product_code">
                        Barcode / Product Code
                    </label>

                    <input
                        type="text"
                        id="product_code"
                        name="product_code"
                        placeholder="Scan or enter product code"
                        autofocus
                        required>

                </div>

                <button
                    type="submit"
                    class="button-primary">
                    Add Scanned Product
                </button>

            </form>

            <h2>
                Or Tap a Product
            </h2>

            <div class="product-touch-grid">

                <?php foreach ($products as $product): ?>

                    <form
                        method="POST"
                        action="add_item.php">

                        <input
                            type="hidden"
                            name="sale_id"
                            value="<?= $sale_id ?>">

                        <input
                            type="hidden"
                            name="product_code"
                            value="<?= h($product["product_code"]) ?>">

                        <button
                            type="submit"
                            class="product-touch-button">

                            <strong>
                                <?= h($product["name"]) ?>
                            </strong>

                            <span>
                                $<?= money($product["price"]) ?>
                            </span>

                            <span>
                                Stock:
                                <?= $product["quantity"] ?>
                            </span>

                        </button>

                    </form>

                <?php endforeach; ?>

            </div>

        </div>

        <aside class="pos-cart" aria-label="Current sale">

            <div>
                Sale started at:
                <strong><?= h($sale["started_at"]) ?></strong>
            </div>

            <ul class="pos-cart-items">
                <?php if (empty($items)): ?>
                    <li>No items scanned yet.</li>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <li class="pos-cart-item">
                            <span>
                                <span class="qty"><?= (int)$item['quantity'] ?>×</span>
                                <?= h($item['name']) ?>
                            </span>
                            <span>
                                $<?= money($item['quantity'] * $item['price']) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>

            <div class="pos-cart-total">
                <span>Total</span>
                <span>$<?= money($total) ?></span>
            </div>

            <a
                href="checkout.php?sale_id=<?= $sale_id ?>"
                class="button button-primary pos-checkout-button">
                Go to Checkout
            </a>

            <form
                method="POST"
                action="cancel.php"
                onsubmit="return confirm('Cancel this sale? This cannot be undone.');">

                <input type="hidden" name="sale_id" value="<?= $sale_id ?>">

                <button type="submit" class="button button-secondary">
                    Cancel Sale
                </button>

            </form>

        </aside>

    </div>

<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>