<?php
function all_nav_items(): array
{
    return array(
        array('label' => 'Home',             'file' => '/FnH-Groceries/home.php',            'icon' => 'home.png',  'roles' => array('admin', 'operator')),
        array('label' => 'Main menu',        'file' => '/FnH-Groceries/menu.php',            'icon' => 'menu.png',  'roles' => array('admin', 'operator')),
        array('label' => 'Store Stock ',     'file' => '/FnH-Groceries/stock/index.php',           'icon' => 'stock.png', 'roles' => array('admin', 'operator')),
        array('label' => 'New sale',         'file' => '/FnH-Groceries/sales/new.php',        'icon' => 'new.png',  'roles' => array('admin', 'operator')),
        array('label' => 'Operators',        'file' => '/FnH-Groceries/operators/user.php',   'icon' => 'users.png',       'roles' => array('admin')),
        array('label' => 'Express orders', 'file' => '/FnH-Groceries/express/index.php', 'icon' => 'express.png', 'roles' => array('shopper')),
    );
}


function nav_items_for(array $user): array
{
    $items = array();
    foreach (all_nav_items() as $item) {
        if (in_array($user['role'], $item['roles'], true)) {
            $items[] = $item;
        }
    }
    return $items;
}
