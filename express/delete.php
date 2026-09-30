<?php

require_once __DIR__ . "/../includes/app.php";
require_once __DIR__ . "/../includes/database.php";
require_once __DIR__ . "/lib.php";
require_login();

$user = current_user();
express_require_access($user);

// Deleting is limited to admin/operator; personal shoppers can cancel via Edit instead.
if (!in_array($user["role"], ["admin", "operator"], true)) {
    http_response_code(403);
    die("You do not have permission to delete Express orders.");
}

$result = "deleted";

// POST only, so a link or browser prefetch can't delete an order.
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = (int)($_POST["id"] ?? 0);

    if ($id > 0) {

        db()->beginTransaction();

        // Collected orders are linked to a real sale, so they are kept.
        $stmt = db()->prepare(
            "DELETE FROM express_orders WHERE id = ? AND status <> 'Collected'"
        );

        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            $result = "blocked";
        } else {
            // In case the tables don't enforce ON DELETE CASCADE
            db()->prepare("DELETE FROM express_order_items WHERE express_order_id = ?")->execute([$id]);
        }

        db()->commit();
    }
}

header("Location: index.php?done=" . $result);

exit;
