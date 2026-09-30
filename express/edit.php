<?php

require_once __DIR__ . "/../includes/app.php";
require_once __DIR__ . "/../includes/database.php";
require_once __DIR__ . "/lib.php";
require_login();

$user = current_user();
express_require_access($user);

$id = (int)($_GET["id"] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM express_orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found.");
}

// Collected orders are already rung up as a sale, so they are locked.
if ($order["status"] === "Collected") {
    $page_title = "Edit Express Order";
    require __DIR__ . "/../includes/header.php";
    echo '<h1>Edit Express Order #' . (int)$order["id"] . '</h1>';
    echo '<p>This order has been collected and checked out, so it can no longer be edited.</p>';
    echo '<a href="receipt.php?id=' . (int)$order["id"] . '" class="button button-secondary">View receipt</a> ';
    echo '<a href="index.php" class="button button-secondary">Back</a>';
    require_once __DIR__ . "/../includes/footer.php";
    exit;
}

// Collected is set only by checking the order out, not here.
$statuses = ["Received", "Packed", "Cancelled"];
$error = null;
$products = express_products($pdo);

// The van rule depends on when the order was PLACED, not on the current time.
$delivery_allowed = delivery_allowed_at($pdo, $order["created_at"]);

$lines = [];
foreach (express_load_items($pdo, $id) as $it) {
    $lines[] = ["product_id" => (int)$it["product_id"], "quantity" => (int)$it["quantity"]];
}
if (!$lines) {
    $lines = [["product_id" => 0, "quantity" => 0]];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $customer_name = trim($_POST["customer_name"] ?? "");
    $phone         = trim($_POST["phone"] ?? "");
    $status        = $_POST["status"] ?? $order["status"];

    if ($customer_name === "") {
        $error = "Customer name is required.";
    } elseif ($phone === "") {
        $error = "A phone number is required.";
    } elseif (!in_array($status, $statuses, true)) {
        $error = "Invalid status.";
    }

    if ($error === null) {
        $items = express_items_from_post($pdo, $_POST, $error);
    }

    if ($error === null) {
        list($method, $fee, $address) =
            express_fulfilment_from_post($_POST, $delivery_allowed, $error);
    }

    if ($error === null) {

        try {
            $pdo->beginTransaction();

            // Re-activating a cancelled order from today takes a slot again.
            $reactivating = false;
            if ($order["status"] === "Cancelled" && $status !== "Cancelled") {
                $chk = $pdo->prepare("SELECT DATE(?) = CURDATE()");
                $chk->execute([$order["created_at"]]);
                $reactivating = (bool)$chk->fetchColumn();
            }

            if ($reactivating && express_used_today($pdo) >= EXPRESS_DAILY_CAPACITY) {
                $pdo->rollBack();
                $error = "Daily Express capacity reached. This order can't be re-activated today.";
            } else {
                $stmt = $pdo->prepare(
                    "UPDATE express_orders
                     SET customer_name = ?,
                         phone = ?,
                         order_subtotal = ?,
                         delivery_method = ?,
                         delivery_fee = ?,
                         delivery_address = ?,
                         status = ?,
                         packed_by = CASE WHEN ? = 'Packed' THEN COALESCE(packed_by, ?) ELSE NULL END,
                         packed_at = CASE WHEN ? = 'Packed' THEN COALESCE(packed_at, NOW()) ELSE NULL END
                     WHERE id = ?"
                );

                $stmt->execute([
                    $customer_name,
                    $phone,
                    express_subtotal($items),
                    $method,
                    $fee,
                    $address,
                    $status,
                    $status, $user["username"],
                    $status,
                    $id
                ]);

                express_save_items($pdo, $id, $items);

                $pdo->commit();

                header("Location: index.php?done=updated");
                exit;
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "The order could not be saved. Please try again.";
        }
    }

    // Re-show what the person typed
    $order["customer_name"]    = $customer_name;
    $order["phone"]            = $phone;
    $order["delivery_method"]  = ($_POST["fulfilment"] ?? "") === "Delivery" ? "Delivery" : "Curbside";
    $order["delivery_address"] = trim($_POST["delivery_address"] ?? "");
    $order["status"]           = $status;
    $lines = express_lines_from_post($_POST);
}

$page_title = "Edit Express Order";
require __DIR__ . "/../includes/header.php";
?>

<h1>Edit Express Order #<?= (int)$order["id"] ?></h1>

<?php if ($error): ?>
    <p class="flash flash-error"><?= h($error) ?></p>
<?php endif; ?>

<form method="POST">

<?php require __DIR__ . "/_form.php"; ?>

<div class="form-actions">

<button type="submit" class="button-primary">
    Update Order
</button>

<a href="index.php" class="button button-secondary">
    Cancel
</a>

</div>

</form>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
