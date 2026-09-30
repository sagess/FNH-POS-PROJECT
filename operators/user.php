<?php
require_once __DIR__ . '/../includes/app.php';
require_login();

$list = list_users();

$page_title = 'Operators';

require __DIR__ . '/../includes/header.php';
?>
<h1>👥 Manage operators</h1>

<a href="add-user.php" class="button button-primary">
    + Add User
</a>

<br><br>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($list)): ?>
            <?php foreach ($list as $user): ?>
                <?php $created = strtotime((string)$user['created_at']); ?>
                <tr>
                    <td><?= (int)$user['user_id'] ?></td>
                    <td><?= $user['username'] ?></td>
                    <td><?= $user['full_name'] ?></td>
                    <td><?= $user['email'] ?></td>
                    <td><?= $user['role'] ?></td>
                    <td><?= $user['is_active'] ? 'Active' : 'Inactive' ?></td>
                    <td><?= $created === false ? '' : date('M j, Y', $created) ?></td>
                    <td>
                        <div class="actions">

                            <a href="edit-user.php?id=<?= (int)$user["user_id"] ?>" class="button button-secondary">
                                Edit
                            </a>
                            <a href="delete-user.php?id=<?= (int)$user["user_id"] ?>" class="button button-danger" onclick="return confirm('Delete this operator?');">
                                Delete
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../includes/footer.php'; ?>