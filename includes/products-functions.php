<?php

//list all products
function list_products(): array
{

    $sql = "SELECT 
                p.id, 
                p.product_code, 
                p.name, 
                p.price, 
                p.quantity, 
                c.name AS category_name, 
                d.name AS department_name
            FROM 
                products p
            LEFT JOIN 
                categories c ON p.category_id = c.id
            LEFT JOIN 
                departments d ON p.department_id = d.id
            ORDER BY 
                p.id DESC";

    $stmt = db()->prepare($sql);
    $stmt->execute();

    // Return all rows as an associative array
    return $stmt->fetchAll();
}

// Retrieve all categories by description
function check_categories(): array
{
    $sql = "SELECT id, description FROM categories ORDER BY description";

    $stmt = db()->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Retrieve all departments by name
function check_departments(): array
{
    $sql = "SELECT id, name FROM departments ORDER BY name";

    $stmt = db()->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}


// Check if a product code already exists in the database
function produce_code_exists(string $product_code): bool
{
    $username = strtolower(trim($product_code));

    $stmt = db()->prepare('SELECT COUNT(*) FROM products WHERE product_code = ?');
    $stmt->execute([$product_code]);

    return (int)$stmt->fetchColumn() > 0;
}

// Insert a new product into the database with validation
function insert_products(string $product_code, string $name, int $category_id, int $department_id, float $price, int $quantity): array
{
    // Validation
    if ($product_code === '' || $name === "") {
        return ['ok' => false, 'error' => 'Product code and name are required'];
    }
    if ($price < 0 || $quantity < 0) {
        return ['ok' => false, 'error' => 'Price and quantity cannot be negative.'];
    }
    try {
        $stmt = db()->prepare("INSERT INTO products(product_code, name, category_id, department_id,price, quantity) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$product_code, $name, $category_id, $department_id, $price, $quantity]);
        return ['ok' => true];
    } catch (PDOException $e) {
        return ['ok' => false, 'error' => ' Database error: ' . $e->getMessage()];
    }
}
