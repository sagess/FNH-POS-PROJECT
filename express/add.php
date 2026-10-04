<?php

/**
 * @var array      $items
 * **/

// app.php is included in the parent file, so we don't need to include it here
require_once __DIR__ . '/../includes/app.php';

//verything below this line requires a logged-in user
require_login();

// express_require_access($user);
$user = current_user();

$error = null;
// Check if delivery is allowed and get the list of products
$delivery_allowed = delivery_window_open();

// Get the list of products for the form
$products = express_products();

// Initialize order and lines for the form
$order = [
    "customer_name"    => "",
    "phone"            => "",
    "delivery_method"  => "Curbside",
    "delivery_address" => "",
];
$lines = [["product_id" => 0, "quantity" => 0]];


// Handle form submission
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
        insert_express_order($order, $items, $method, $fee, $address, $user);
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