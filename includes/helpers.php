<?php

// Escapes a string for safe output in HTML.
function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Formats a number as a currency string with two decimal places and a dollar sign.
function money($amount): string
{
    return '$' . number_format((float)$amount, 2);
}

// Redirects to a given URL and exits the script.
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

// Returns a trimmed string from the $_POST superglobal, or a default value if the key is not set.
function post(string $key, string $default = ''): string
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}


// Flash message functions
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


/*
=============================================================================
sale functions -- Assignment2
====================================================================
*/

// Returns an array of all sale items for a given sale ID, including product name and code.
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

// Returns an array of all sale items for a given sale ID.
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

// Returns an array of all sale items for a given sale ID.
function show_all_sale(int $sale_id): array
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

// Returns an array of all products, including their code, name, price, and quantity.
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

// Returns the total number of units in stock across all products.
function number_of_stock(): float
{
    $totalStmt = db()->prepare("SELECT COALESCE(SUM(quantity), 0) AS total_stock FROM products");
    $totalStmt->execute();
    $result = $totalStmt->fetch(PDO::FETCH_ASSOC);

    return (float) ($result['total_stock'] ?? 0);
}

// Returns an array of all categories, including their description and product count.
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

// Inserts a new category into the database.
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

// Get a category by ID
function get_category(int $id): ?array
{
    $stmt = db()->prepare("SELECT id, name, description FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch();
    return $category ?: null;
}

// Update a category by ID
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

// Deletes a category by ID.
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
=================================================================
 Express order functions -- Assignment3
=============================================================
*/


// Returns the number of express orders placed today that are not cancelled.
function express_used_today(): int
{
    $stmt = db()->query(
        "SELECT COUNT(*) FROM express_orders
         WHERE DATE(created_at) = CURDATE() AND status <> 'Cancelled'"
    );
    return (int)$stmt->fetchColumn();
}

// True if the current time is within the delivery window (8 AM to 4 PM).
function delivery_window_open(): bool
{
    $stmt = db()->prepare("SELECT TIME(NOW()) BETWEEN ? AND ?");
    $stmt->execute(array(DELIVERY_WINDOW_START, DELIVERY_WINDOW_END));
    return (bool)$stmt->fetchColumn();
}



// True if an order placed at $placed_at can be scheduled for home delivery.
function delivery_allowed_at(string $placed_at): bool
{
    $stmt = db()->prepare("SELECT TIME(?) BETWEEN ? AND ?");
    $stmt->execute(array($placed_at, DELIVERY_WINDOW_START, DELIVERY_WINDOW_END));
    return (bool)$stmt->fetchColumn();
}

// Returns an array of fulfilment details based on POST data, checking for delivery eligibility and address requirements.
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


// Returns an array of all products, including their code, name, price, and quantity.
function express_products(): array
{
    return db()->query(
        "SELECT id, product_code, name, price, quantity FROM products ORDER BY name"
    )->fetchAll();
}

// Returns an array of lines (product_id, quantity) from the POST data.
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

// Returns an array of items for a given express order, including product name and code.
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

// Returns the subtotal of an array of express order lines (sum of line_total).
function express_subtotal(array $lines): float
{
    $sum = 0.0;
    foreach ($lines as $l) {
        $sum += $l['line_total'];
    }
    return round($sum, 2);
}

// Returns an array of totals for an express order, including tax and delivery fee.
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

// Saves the items for an express order, replacing any existing items for that order.
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

// Returns an array of items for a given express order, including product name and code.
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

// Checks if an express order can be marked as packed and updates its status accordingly.
function check_order_packed(string $username, int $id)
{
    try {
        $stmt = db()->prepare(
            "UPDATE express_orders
         SET status = 'Packed', packed_by = ?, packed_at = NOW()
         WHERE id = ? AND status = 'Received'"
        );
        $stmt->execute([$username, $id]);
        return ['ok' => true];
    } catch (PDOException $e) {
        return ['ok' => false, 'error' => 'Database error: ' . $e->getMessage()];
    }
}

// Returns an array of today's express orders, including item counts, ordered by created_at descending.
function orders_progress(): array
{
    return db()
        ->query(
            "SELECT o.*,
                (SELECT COALESCE(SUM(i.quantity), 0)
                 FROM express_order_items i
                 WHERE i.express_order_id = o.id) AS item_count
         FROM express_orders o
         WHERE DATE(o.created_at) = CURDATE()
            OR o.status IN ('Received', 'Packed')
         ORDER BY o.created_at DESC"
        )
        ->fetchAll();
}

// Returns an array of products with their department names (or 'Unassigned' if no department).
function inventor_products_department(): array
{
    return db()
        ->query(
            "SELECT p.product_code, p.name,
                COALESCE(d.name, 'Unassigned') AS department,
                p.quantity
         FROM products p
         LEFT JOIN departments d ON d.id = p.department_id
         ORDER BY department, p.name"
        )
        ->fetchAll();
}

// Returns an array of departments with the count of products and total units in each.
function stock_products_department(): array
{
    return db()
        ->query(
            "SELECT COALESCE(d.name, 'Unassigned') AS department,
                COUNT(*) AS product_count,
                SUM(p.quantity) AS units
         FROM products p
         LEFT JOIN departments d ON d.id = p.department_id
         GROUP BY department
         ORDER BY department"
        )
        ->fetchAll();
}

//check if register #1 exists for the store and is active
function check_register_exists_store(): array
{
    $stmtRegister = db()->prepare(
        "SELECT register_id, status FROM register WHERE store_id = ? AND register_number = '1'"
    );
    $stmtRegister->execute([STORE_ID]);
    $register = $stmtRegister->fetch();
    return $register;
}


//insert express order into the database
function insert_express_order(array $order, array $items, string $method, float $fee, ?string $address, array $user): array
{
    try {
        db()->beginTransaction();

        $register = check_register_exists_store();

        if (!$register) {
            throw new Exception("The checkout lane #1 does not exist for this store.");
        }
        if ($register['status'] !== 'Active') {
            throw new Exception("The checkout lane #1 is currently under maintenance or inactive.");
        }

        $register_id = $register['register_id'];

        if (express_used_today() >= EXPRESS_DAILY_CAPACITY) {
            db()->rollBack();
            return ['ok' => false, 'error' => "Daily Express capacity reached. No more orders can be accepted today."];
        }

        $stmt = db()->prepare(
            "INSERT INTO express_orders
             (customer_name, phone, order_subtotal, delivery_method,
              delivery_fee, delivery_address, received_by, register_id, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );

        $stmt->execute([
            $order["customer_name"],
            $order["phone"],
            express_subtotal($items),
            $method,
            $fee,
            $address,
            $user["username"],
            $register_id
        ]);

        express_save_items((int)db()->lastInsertId(), $items);

        db()->commit();
        redirect('index.php?done=recorded');
        exit;

        // return ['ok' => true];
    } catch (Throwable $e) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        error_log($e->getMessage());
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}
