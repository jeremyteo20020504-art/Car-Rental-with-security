<?php
include 'db.php';
include "sidebar.php";
session_start();

// make PDO throw exceptions
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check if the user is logged in and is an admin
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; // Bypasses token check

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

// Token comes from GET or POST
$token = (string)($_GET['token'] ?? $_POST['token'] ?? '');
$error_message = null;
$success_message = null;
$show_form = true;
$min_password_length = 8;

if ($token === '' && !$is_admin) {
    $error_message = 'Invalid or missing token.';
    $show_form = false;
} else {
    try {
        if (!$is_admin) {
            // Fetch the reset row only if the user is not an admin
            $stmt = $pdo->prepare('SELECT pr.id AS pr_id, pr.user_id, pr.expires_at, pr.used, u.email, u.username FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.token = :token LIMIT 1');
            $stmt->execute([':token' => $token]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                $error_message = 'Invalid or expired token.';
                $show_form = false;
            } elseif ((int)$row['used'] === 1) {
                $error_message = 'This reset link has already been used.';
                $show_form = false;
            } elseif (strtotime($row['expires_at']) < time()) {
                $error_message = 'This reset link has expired.';
                $show_form = false;
            }
        } else {
            // Skip the token validation if the user is admin
            $row = null;
        }

        // Valid token; handle POST to change password
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = substr((string)($_POST['password'] ?? ''), 0, 100);
            $password_confirm = substr((string)($_POST['password_confirm'] ?? ''), 0, 100);

            // Password validation: Custom checker (minimum 8 chars, 1 uppercase, 1 lowercase, 1 number)
            if ($password === '' || $password_confirm === '') {
                $error_message = 'Please enter and confirm your new password.';
            } elseif ($password !== $password_confirm) {
                $error_message = 'Passwords do not match.';
            } elseif (strlen($password) < $min_password_length) {
                $error_message = 'Password must be at least ' . $min_password_length . ' characters.';
            } elseif (
                strlen($password) < 8 ||
                !preg_match('/[A-Z]/', $password) ||  // at least one uppercase letter
                !preg_match('/[a-z]/', $password) ||  // at least one lowercase letter
                !preg_match('/[0-9]/', $password)     // at least one number
            ) {
                $error_message = 'Password must be at least 8 characters long, include an uppercase letter, a lowercase letter, and a number.';
            } else {
                // All good: hash and update
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                // Start transaction to update password
                $pdo->beginTransaction();
                if (!$is_admin) {
                    // Update user password only if it's not an admin bypass
                    $updateUser = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
                    $updateUser->execute([':password' => $passwordHash, ':id' => $row['user_id']]);
                }

                if (!$is_admin) {
                    // Mark token as used only if it's not an admin bypass
                    $markUsed = $pdo->prepare('UPDATE password_resets SET used = 1 WHERE id = :id');
                    $markUsed->execute([':id' => $row['pr_id']]);
                }

                $pdo->commit();

                $success_message = 'Your password has been updated. You can now sign in with your new password.';
                $show_form = false;
            }
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        $error_message = 'An error occurred. Please try again later.';
        $show_form = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/stylesform.css">
</head>
<body>
    <div class="containercenter">
    <h2>Change Password</h2>

    <?php if ($success_message): ?><p style="color:green;"><?= e($success_message) ?></p><?php endif; ?>
    <?php if ($error_message): ?><p style="color:red;"><?= e($error_message) ?></p><?php endif; ?>

    <?php if ($show_form): ?>
        <form method="post" novalidate>
            <input type="hidden" name="token" value="<?= e($token) ?>">
            <label for="password">New password (at least 8 characters, 1 uppercase, 1 lowercase, 1 number)</label><br>
            <input id="password" name="password" type="password" maxlength="100" required>
            <br><br>
            <label for="password_confirm">Confirm new password</label><br>
            <input id="password_confirm" name="password_confirm" type="password" maxlength="100" required>
            <br><br>
            <button type="submit">Change Password</button>
        </form>
    <?php endif; ?>
</body>
</html>
