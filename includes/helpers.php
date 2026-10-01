<?php
function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function money($amount): string
{
    return '$' . number_format((float)$amount, 2);
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}
function post(string $key, string $default = ''): string
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}



function flash_add(string $type, string $text): void
{
    $_SESSION['flash'][] = array('type' => $type, 'text' => $text);
}

function flash_take(): array
{
    $msgs = isset($_SESSION['flash']) ? $_SESSION['flash'] : array();
    unset($_SESSION['flash']);
    return $msgs;
}

/* ==========================================================================
   STUDENT: your own shared functions go below this line.

   Anything two or more pages need belongs here rather than being copied into
   each page - for example a function that returns the units in stock per
   department (use case 1), one that adds a product to an open sale (use case
   2), or one that totals a receipt (use case 3). Keep each function small and
   give it a comment saying which use case it serves.
   ========================================================================== */

// query and calculation functions 

/*
============
sale functions -- Assignment2
============
*/

function list_sale_items(int $sale_id): array
{

    if ($sale_id <= 0) {
        flash_add('error', 'No sale was specified.');
        redirect('new.php');
    }

    $stmt = db()->prepare(
        "SELECT
            si.id,
            si.sale_id,
            si.product_id,
            si.quantity,
            si.unit_price AS price,
            si.line_total,
            p.name
         FROM sale_items si
         INNER JOIN products p
            ON si.product_id = p.id
         WHERE si.sale_id = ?
         ORDER BY si.id"
    );

    $stmt->execute([$sale_id]);

    return $stmt->fetchAll();
}

function list_sale_items_products(int $sale_id): array
{

    if ($sale_id <= 0) {
        flash_add('error', 'No sale was specified.');
        redirect('new.php');
    }
    $stmt = db()->prepare(
        "SELECT
        si.*,
        p.name
     FROM sale_items si
     INNER JOIN products p
        ON si.product_id = p.id
     WHERE si.sale_id = ?
     ORDER BY si.id"
    );
    $stmt->execute([$sale_id]);
    return  $stmt->fetchAll();
}

//Display the values of the sale fields based on the current sale_id
function show_all_sale (int$sale_id): array
{
    if ($sale_id <= 0) {
        flash_add('error', 'No sale was specified.');
        redirect('new.php');
    }
$stmt = db()->prepare(
    "SELECT *
     FROM sale_items
     WHERE sale_id = ?"
);

$stmt->execute([$sale_id]);
return  $stmt->fetchAll();

}

//Products Displayed by Department 
function view_products_by_department(): array
{
    $stmt = db()->prepare(
        "SELECT
        d.name AS department,
        p.product_code,
        p.name AS product,
        p.quantity,
        p.price
     FROM products p
     LEFT JOIN departments d
        ON p.department_id = d.id
     ORDER BY d.name, p.name"
    );
    $stmt->execute();
    return $stmt->fetchAll();
}

//Quantity of products in stock at the store
function number_of_stock(): float
{
    $totalStmt = db()->prepare("SELECT COALESCE(SUM(quantity), 0) AS total_stock FROM products");
    $totalStmt->execute();
    $result = $totalStmt->fetch(PDO::FETCH_ASSOC);

    return (float) ($result['total_stock'] ?? 0);
}

// Displayed the categories
function list_categories(): array
{
    // left join 
    $sql = "SELECT c.id, c.name, c.description, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id 
            GROUP BY c.id 
            ORDER BY c.name ASC";

    $stmt = db()->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Save category
function insert_category(string $name, string $description): array
{
    $name = trim($name);
    $description = trim($description);

    // Validation
    if ($name === '') {
        return ['ok' => false, 'error' => 'Category name is required.'];
    }

    try {
        $stmt = db()->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        return ['ok' => true];
    } catch (PDOException $e) {
        return ['ok' => false, 'error' => 'Database error: ' . $e->getMessage()];
    }
}

// Display one category by ID
function get_category(int $id): ?array
{
    $stmt = db()->prepare("SELECT id, name, description FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch();
    return $category ?: null;
}

// update 
function update_category(int $id, string $name, string $description): array
{
    $name = trim($name);
    $description = trim($description);

    if ($name === '') {
        return ['ok' => false, 'error' => 'Category name cannot be empty.'];
    }

    try {
        $stmt = db()->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $description, $id]);
        return ['ok' => true];
    } catch (PDOException $e) {
        return ['ok' => false, 'error' => 'Database error: ' . $e->getMessage()];
    }
}

// delete
function delete_category(int $id): bool
{
    try {
        $stmt = db()->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return true;
    } catch (PDOException $e) {
        // Log error if necessary
        return false;
    }
}


/* 
===========
 Express order functions -- Assignment3
============
*/

function express_require_access(array $user): void
{
    // Adjust to match your role names.
    if (!in_array($user['role'], array('admin', 'operator', 'shopper'), true)) {
        http_response_code(403);
        die('You do not have access to Express orders.');
    }
}

/** Orders that count against today's capacity (cancelled ones free the slot). */
function express_used_today(): int
{
    $stmt = db()->query(
        "SELECT COUNT(*) FROM express_orders
         WHERE DATE(created_at) = CURDATE() AND status <> 'Cancelled'"
    );
    return (int)$stmt->fetchColumn();
}

/**
 * True if an order placed right now can be scheduled for home delivery.
 * Uses the database clock, the same clock that stamps created_at.
 */
function delivery_window_open(): bool
{
    $stmt = db()->prepare("SELECT TIME(NOW()) BETWEEN ? AND ?");
    $stmt->execute(array(DELIVERY_WINDOW_START, DELIVERY_WINDOW_END));
    return (bool)$stmt->fetchColumn();
}

/*function money($amount): string
{
    return '$' . number_format((float)$amount, 2);
}*/

/** True if an order placed at $placed_at (a DB datetime string) was inside the van's hours. */
function delivery_allowed_at(string $placed_at): bool
{
    $stmt = db()->prepare("SELECT TIME(?) BETWEEN ? AND ?");
    $stmt->execute(array($placed_at, DELIVERY_WINDOW_START, DELIVERY_WINDOW_END));
    return (bool)$stmt->fetchColumn();
}

/**
 * Reads the fulfilment fields from a form and applies the delivery rules.
 * Returns array(method, fee, address). Sets $error if the choice is not allowed.
 */
function express_fulfilment_from_post(array $post, bool $delivery_allowed, ?string &$error): array
{
    $wants   = (($post['fulfilment'] ?? 'Curbside') === 'Delivery');
    $address = trim($post['delivery_address'] ?? '');

    if ($wants && !$delivery_allowed) {
        $error = 'Home delivery is only available for orders placed between 8 AM and 4 PM. This order must be curbside pickup.';
    } elseif ($wants && $address === '') {
        $error = 'A delivery address is required for home delivery.';
    }

    if ($wants && $error === null) {
        return array('Delivery', DELIVERY_FEE, $address);
    }
    return array('Curbside', '0.00', null);
}

/* ------------------------------------------------------------------
 * Phoned-in grocery orders: items, totals, checkout settings
 * ------------------------------------------------------------------ */

// SET THESE to match your store / your existing sales module.
//const EXPRESS_TAX_RATE      = 0.00;        // e.g. 0.0625 for 6.25%. Applied to the subtotal, not the delivery fee.
//const SALE_STATUS_COMPLETED = 'completed'; // the value your `sales.status` uses for a finished sale

function express_products(): array
{
    return db()->query(
        "SELECT id, product_code, name, price, quantity FROM products ORDER BY name"
    )->fetchAll();
}

/** Rebuilds line rows from posted form arrays (to re-show the form after an error). */
function express_lines_from_post(array $post): array
{
    $ids  = (isset($post['product_id']) && is_array($post['product_id'])) ? $post['product_id'] : array();
    $qtys = (isset($post['qty']) && is_array($post['qty'])) ? $post['qty'] : array();
    $lines = array();
    foreach ($ids as $i => $pid) {
        $lines[] = array('product_id' => (int)$pid, 'quantity' => (int)($qtys[$i] ?? 0));
    }
    return $lines ? $lines : array(array('product_id' => 0, 'quantity' => 0));
}

/**
 * Validates posted product lines against the products table.
 * Returns lines with the current price; sets $error if something is wrong.
 */
function express_items_from_post(array $post, ?string &$error): array
{
    $wanted = array();
    foreach (express_lines_from_post($post) as $l) {
        if ($l['product_id'] <= 0 && $l['quantity'] <= 0) {
            continue; // blank row
        }
        if ($l['product_id'] <= 0 || $l['quantity'] <= 0) {
            $error = 'Each line needs a product and a quantity of at least 1.';
            return array();
        }
        $wanted[$l['product_id']] = ($wanted[$l['product_id']] ?? 0) + $l['quantity'];
    }
    if (!$wanted) {
        $error = 'Add at least one product to the order.';
        return array();
    }

    $stmt  = db()->prepare("SELECT id, name, price, quantity FROM products WHERE id = ?");
    $lines = array();
    foreach ($wanted as $pid => $qty) {
        $stmt->execute(array($pid));
        $p = $stmt->fetch();
        if (!$p) {
            $error = 'Unknown product selected.';
            return array();
        }
        if ($qty > (int)$p['quantity']) {
            $error = 'Only ' . (int)$p['quantity'] . ' of ' . $p['name'] . ' in stock.';
            return array();
        }
        $price   = round((float)$p['price'], 2);
        $lines[] = array(
            'product_id' => (int)$pid,
            'name'       => $p['name'],
            'quantity'   => $qty,
            'unit_price' => $price,
            'line_total' => round($price * $qty, 2),
        );
    }
    return $lines;
}

function express_subtotal(array $lines): float
{
    $sum = 0.0;
    foreach ($lines as $l) {
        $sum += $l['line_total'];
    }
    return round($sum, 2);
}

/** subtotal + tax + delivery fee = total due. */
function express_totals(float $subtotal, float $delivery_fee): array
{
    $tax = round($subtotal * TAX_RATE, 2);
    return array(
        'subtotal' => round($subtotal, 2),
        'tax'      => $tax,
        'fee'      => round($delivery_fee, 2),
        'total'    => round($subtotal + $tax + $delivery_fee, 2),
    );
}

function express_save_items(int $order_id, array $lines): void
{
    db()->prepare("DELETE FROM express_order_items WHERE express_order_id = ?")->execute(array($order_id));

    $ins = db()->prepare(
        "INSERT INTO express_order_items
         (express_order_id, product_id, quantity, unit_price, line_total)
         VALUES (?, ?, ?, ?, ?)"
    );
    foreach ($lines as $l) {
        $ins->execute(array($order_id, $l['product_id'], $l['quantity'], $l['unit_price'], $l['line_total']));
    }
}

function express_load_items(int $order_id): array
{
    $stmt = db()->prepare(
        "SELECT i.*, p.name, p.product_code
         FROM express_order_items i
         JOIN products p ON p.id = i.product_id
         WHERE i.express_order_id = ?
         ORDER BY i.id"
    );
    $stmt->execute(array($order_id));
    return $stmt->fetchAll();
}
