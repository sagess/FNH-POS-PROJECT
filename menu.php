<?php
require_once __DIR__ . '/includes/app.php';
require_login();

$user = current_user();
$page_title = 'Main menu';
require __DIR__ . '/includes/header.php';
?>

<div class="top-menu-header">

    <h2>
        ☰ Main menu
    </h2>

    <p>
        Welcome,
        <strong>
            <?php echo h($user['full_name']); ?>
        </strong>.
    </p>

    <p>
        Select an option below.
    </p>

</div>


<!-- SALES -->

<section class="menu-section">

    <h2>
        Sales and Register
    </h2>

    <div class="top-menu-grid">

        <a
            href="sales/new.php"
            class="menu-card">

            <div class="menu-icon">
                🛒
            </div>

            <h3>
                New Sale
            </h3>

            <p>
                Start a new sale, scan products,
                or tap products on the screen.
            </p>

        </a>


        <a
            href="stock/index.php"
            class="menu-card">

            <div class="menu-icon">
                📦
            </div>

            <h3>
                Store Stock
            </h3>

            <p>
                View total inventory and stock
                by department.
            </p>

        </a>


        <a
            href="express/index.php"
            class="menu-card">

            <div class="menu-icon">
                🧾
            </div>

            <h3>
                Orders
            </h3>

            <p>
                Start a new order, View customer orders and status.
            </p>

        </a>

    </div>

</section>


<!-- STORE MANAGEMENT -->

<section class="menu-section">

    <h2>
        Store Management
    </h2>

    <div class="top-menu-grid">

        <a
            href="products/index.php"
            class="menu-card">

            <div class="menu-icon">
                🥦
            </div>

            <h3>
                Products
            </h3>

            <p>
                Manage products, prices,
                departments and inventory.
            </p>

        </a>


        <a
            href="categories/index.php"
            class="menu-card">

            <div class="menu-icon">
                🗂️
            </div>

            <h3>
                Categories
            </h3>

            <p>
                Manage product categories.
            </p>

        </a>

    </div>

</section>
<?php require_once "includes/footer.php"; ?>