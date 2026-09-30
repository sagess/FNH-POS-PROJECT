<?php
$user = current_user();
?>
<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">
    <title><?php echo h(isset($page_title) ? $page_title : SITE_TITLE); ?> - <?php echo h(SITE_TITLE); ?></title>

    <link
        rel="stylesheet"
        href="/FnH-Groceries/assets/css/style.css">

</head>

<body>
    <header class="site-header">

        <div class="banner">

            <img
                src="/FnH-Groceries/assets/images/banner/fruit.png"
                alt="Fresh fruit">
            <img
                src="/FnH-Groceries/assets/images/banner/bread.png"
                alt="Fresh bread">



            <img
                src="/FnH-Groceries/assets/images/banner/grocery.png"
                alt="Groceries">

            <img
                src="/FnH-Groceries/assets/images/banner/vegetables.png"
                alt="Fresh vegetables">

            <img
                src="/FnH-Groceries/assets/images/banner/milk.png"
                alt="Milk">

            <div class="banner-title">
                <?php echo h(SITE_TITLE); ?>
            </div>
        </div>

    </header>

    <div class="page-layout">
        <nav class="sidebar">

            <div class="nav-title">
                FnH Groceries
            </div>
            <?php if ($user): ?>
                <?php foreach (nav_items_for($user) as $item): ?>
                    <a class="nav-button" href="<?php echo h($item['file']); ?>"><?php echo h($item['label']); ?></a>
                <?php endforeach; ?>


                <div class="operator-box">

                    <strong>Logged in as:</strong>

                    <span>
                        <?php echo h($user['full_name']); ?>
                    </span>

                    <small>
                        (<?php echo h($user['username']); ?> &middot; <?php echo h($user['role']); ?>)
                    </small>


                </div><br>

                <a class="nav-button " href="/FnH-Groceries/logout.php">Log out</a>
            <?php else: ?>
                <a class="nav-button" href="index.php">Login</a>
            <?php endif; ?>

        </nav>

        <main class="main-content">
            <?php foreach (flash_take() as $f): ?>
                <p class="flash flash-<?php echo h($f['type']); ?>"><?php echo h($f['text']); ?></p>
            <?php endforeach; ?>