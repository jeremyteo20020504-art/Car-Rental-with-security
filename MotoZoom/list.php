<?php
include 'db.php';
session_start();

// Check if the user is an admin, as only admins should access the logs page
if ($_SESSION['role'] != 'admin') {
    header('Location: index.php'); // Redirect to home if not an admin
    exit;
}
include 'sidebara.php'; // only admin access anyway
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

try {
    $stmt = $pdo->query('SELECT id, username, email, password FROM users ORDER BY id ASC');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database error.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Users</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="containerwide">    <h2>User list</h2>
    <p class="small">Showing username, email and stored password (do not expose this page publicly).</p>
    <table>
        <thead>
            <tr>
                <th>username</th>
                <th>email</th>
                <th>password</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($users)): ?> // Unlikely since admin account is required to open the page
            <tr><td colspan="4">No users found.</td></tr>
        <?php else: ?>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= e($u['username']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e($u['password']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
        <div class="navbar navbar-admin">
            <p><a href="admin.php">← Back to Admin Panel</a></p>
        </div>
</body>
</html>
