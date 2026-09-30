<?php
require_once __DIR__ . '/../includes/app.php';
require_login();

// target category ID from the query 
$id = (int)($_GET['id'] ?? 0);
$category = get_category($id);

// If the ID is invalid or doesn't exist
if ($category === null) {
    die('Error: That category does not exist.');
}

$error = '';

//  Handle Form Processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';

    $result = update_category($id, $name, $description);

    if ($result['ok']) {
        redirect('index.php');
        exit();
    } else {
        $error = $result['error'];
    }
}

$page_title = 'Edit Category';
require __DIR__ . '/../includes/header.php';
?>
<h1>✏️ Edit Category</h1>

<?php if ($error): ?>
    <div class="alert alert-danger" style="color: red; margin-bottom: 15px; font-weight: bold;">
        <?= ($error) ?>
    </div>
<?php endif; ?>

<form method="POST" action="edit.php?id=<?= $id ?>">

    <div class="form-group">
        <label for="name">Category Name</label>
        <input
            type="text"
            id="name"
            name="name"
            value="<?= $category["name"] ?>"
            required>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" required><?= $category["description"] ?? "" ?></textarea>
    </div>

    <div class="form-actions">
        <button
            type="submit"
            class="button-primary">
            Update Category
        </button>

        <a
            href="index.php"
            class="button button-secondary">
            Cancel
        </a>
    </div>

</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>