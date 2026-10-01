<?php
require_once __DIR__ . '/../includes/app.php';

require_login();

$user = current_user();
express_require_access($user);

$error = null;
$delivery_allowed = delivery_window_open();
$products = express_products();

$order = [
    "customer_name"    => "",
    "phone"            => "",
    "delivery_method"  => "Curbside",
    "delivery_address" => "",
];
$lines = [["product_id" => 0, "quantity" => 0]];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $order["customer_name"]    = trim($_POST["customer_name"] ?? "");
    $order["phone"]            = trim($_POST["phone"] ?? "");
    $order["delivery_address"] = trim($_POST["delivery_address"] ?? "");
    $order["delivery_method"]  = ($_POST["fulfilment"] ?? "") === "Delivery" ? "Delivery" : "Curbside";
    $lines = express_lines_from_post($_POST);

    if ($order["customer_name"] === "") {
        $error = "Customer name is required.";
    } elseif ($order["phone"] === "") {
        $error = "A phone number is required.";
    }

    if ($error === null) {
        $items = express_items_from_post($_POST, $error);
    }

    if ($error === null) {
        list($method, $fee, $address) =
            express_fulfilment_from_post($_POST, $delivery_allowed, $error);
    }

    if ($error === null) {

        try {
            db()->beginTransaction();

            if (express_used_today() >= EXPRESS_DAILY_CAPACITY) {
                db()->rollBack();
                $error = "Daily Express capacity reached. No more orders can be accepted today.";
            } else {
                $stmt = db()->prepare(
                    "INSERT INTO express_orders
                     (customer_name, phone, order_subtotal, delivery_method,
                      delivery_fee, delivery_address, received_by, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
                );

                $stmt->execute([
                    $order["customer_name"],
                    $order["phone"],
                    express_subtotal($items),
                    $method,
                    $fee,
                    $address,
                    $user["username"]
                ]);

                express_save_items((int)db()->lastInsertId(), $items);

                db()->commit();

                header("Location: index.php?done=recorded");
                exit;
            }
        } catch (Throwable $e) {
    if (db()->inTransaction()) {
        db()->rollBack();
    }
    error_log($e->getMessage());
    $error = "DEBUG: " . $e->getMessage();   // remove after fixing
}
    }
}

$used      = express_used_today();
$available = max(0, EXPRESS_DAILY_CAPACITY - $used);

$page_title = "Add Express Order";
require __DIR__ . "/../includes/header.php";
?>

<h1>Add Express Order</h1>

<p>
    <strong><?= $available ?></strong> of <?= EXPRESS_DAILY_CAPACITY ?>
    Express orders still available today.
</p>

<?php if ($error): ?>
    <p class="flash flash-error"><?= h($error) ?></p>
<?php endif; ?>

<?php if ($available > 0): ?>

<form method="POST">

<?php require __DIR__ . "/_form.php"; ?>

<div class="form-actions">

<button type="submit" class="button-primary">
    Add Order
</button>

<a href="index.php" class="button button-secondary">
    Cancel
</a>

</div>

</form>

<?php else: ?>

<p>Daily capacity reached. New Express orders can't be recorded until tomorrow.</p>

<a href="index.php" class="button button-secondary">Back</a>

<?php endif; ?>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
