<?php

session_start();

require_once "includes/database.php";

$success = "";
$error = "";

$name = "";
$email = "";
$message = "";

if (isset($_SESSION["username"])) {
    $name = $_SESSION["username"];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $message = trim($_POST["message"]);

    if (
        $name === "" ||
        $email === "" ||
        $message === ""
    ) {

        $error = "Please complete all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        $stmt = $pdo->prepare(
            "INSERT INTO feedback
             (name, email, message)
             VALUES (?, ?, ?)"
        );

        $stmt->execute([
            $name,
            $email,
            $message
        ]);

        $success =
            "Thank you. Your feedback has been submitted.";

        $message = "";
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Feedback - FnH Groceries</title>

<link
    rel="stylesheet"
    href="assets/css/style.css"
>

</head>

<body>

<?php require_once "includes/header.php"; ?>

<div class="page-layout">

<?php if (isset($_SESSION["user_id"])): ?>

<?php require_once "includes/navbar.php"; ?>

<?php else: ?>

<nav class="sidebar">

<div class="nav-title">
FnH Groceries
</div>

<a
    href="index.php"
    class="nav-button"
>
Home
</a>

<a
    href="login.php"
    class="nav-button"
>
Login
</a>

</nav>

<?php endif; ?>


<main class="main-content">

<div class="login-container">

<h1>
Send Feedback
</h1>

<p>
Please send your comments or suggestions
about the FnH Groceries application.
</p>

<?php if ($success): ?>

<div class="success">
<?= htmlspecialchars($success) ?>
</div>

<?php endif; ?>


<?php if ($error): ?>

<div class="error">
<?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>


<form method="POST">

<div class="form-group">

<label for="name">
Name
</label>

<input
    type="text"
    id="name"
    name="name"
    value="<?= htmlspecialchars($name) ?>"
    required
>

</div>


<div class="form-group">

<label for="email">
Email
</label>

<input
    type="email"
    id="email"
    name="email"
    value="<?= htmlspecialchars($email) ?>"
    required
>

</div>


<div class="form-group">

<label for="message">
Feedback
</label>

<textarea
    id="message"
    name="message"
    required
><?= htmlspecialchars($message) ?></textarea>

</div>


<button
    type="submit"
    class="button-primary"
>
    Send Feedback
</button>

</form>

</div>

</main>

</div>

<?php require_once "includes/footer.php"; ?>

</body>

</html>