<?php
require_once __DIR__ . '/../includes/app.php';
require_login();

$categories = list_categories();

$page_title = 'Categories';
require __DIR__ . '/../includes/header.php';
?>

<h1>Categories</h1>

<a
    href="add.php"
    class="button button-primary">
    ➕ Add Category
</a>

<br><br>

<div class="table-container">

    <table>

        <thead>

            <tr>

                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Products</th>
                <th>Actions</th>

            </tr>

        </thead>

        <tbody>

            <?php foreach ($categories as $category): ?>

                <tr>

                    <td>
                        <?= $category["id"] ?>
                    </td>

                    <td>
                        <?= $category["name"] ?>
                    </td>

                    <td>
                        <?= $category["description"] ?? "" ?>
                    </td>

                    <td>
                        <?= $category["product_count"] ?>
                    </td>

                    <td>

                        <div class="actions">

                            <a
                                href="edit.php?id=<?= $category["id"] ?>"
                                class="button button-secondary">
                                Edit
                            </a>

                            <a
                                href="delete.php?id=<?= $category["id"] ?>"
                                class="button button-danger"
                                onclick="return confirm('Delete this category?');">
                                Delete
                            </a>

                        </div>

                    </td>

                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>