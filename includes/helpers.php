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
