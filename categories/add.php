<?php
require_once __DIR__ . '/../includes/app.php';

// Check if user is logged in
require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';

    $result = insert_category($name, $description);

    if ($result['ok']) {

        // Redirect to the categories index page after successful insertion
        redirect('index.php');
        exit();
    } else {
        $error = $result['error'];
    }
}

$page_title = 'Add Category';

// Include the header template
require __DIR__ . '/../includes/header.php';
?>
<h1>➕ Add New Category</h1>

<?php if ($error): ?>
    <div style="color: red; margin-bottom: 15px; font-weight: bold;"><?= ($error) ?></div>
<?php endif; ?>

<form method="POST" action="add.php">

    <div class="form-group">

        <label>
            Category Name
        </label>

        <input
            type="text"
            name="name"
            required>

    </div>

    <div class="form-group">

        <label>
            Description
        </label>

        <textarea
            name="description"></textarea>

    </div>

    <div class="form-actions">

        <button
            type="submit"
            class="button-primary">
            Save Category
        </button>

        <a
            href="index.php"
            class="button button-secondary">
            Cancel
        </a>

    </div>

</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>