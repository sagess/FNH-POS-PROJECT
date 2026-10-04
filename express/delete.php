<?php
require_once __DIR__ . "/../includes/app.php";
require_login();

$result = "deleted";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = (int)($_POST["id"] ?? 0);

    if ($id > 0) {

        db()->beginTransaction();

        // Delete the order only if it is not collected
        $stmt = db()->prepare(
            "DELETE FROM express_orders WHERE id = ? AND status <> 'Collected'"
        );

        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            $result = "blocked";
        } else {
            // Delete the associated items for the order
            db()->prepare("DELETE FROM express_order_items WHERE express_order_id = ?")->execute([$id]);
        }

        db()->commit();
    }
}

redirect('index.php?done=' . $result);

exit;
