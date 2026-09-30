<?php
require_once __DIR__ . '/includes/app.php';
require_login();

$user = current_user();
$page_title = 'Home';
require __DIR__ . '/includes/header.php';
?>
<div class="card">

    <div class="card-icon">
        🏠 Home
    </div>

    <h1>
        Welcome,
        <strong>
            <?php echo h($user['full_name']); ?>
        </strong>.
    </h1>

    <span>
        This is the FnH Groceries point-of-sale system. Cashiers use it at the register,
        on touchscreens or standard computer monitors, to help customers with their shopping.
        The system allows cashiers to quickly search for products, add items to a customer’s cart,
        review the cart’s contents, calculate the total amount, process payments,
        and efficiently complete transactions. It also provides access to key store functions,
        such as product management, inventory checks, viewing transaction information,
        and handling customer feedback. The system is designed to be simple, fast,
        and easy to use, so that cashiers can provide customers with a seamless and efficient checkout experience.
    </span>

</div>
<?php require __DIR__ . '/includes/footer.php'; ?>