<?php
include 'db.php';
session_start();
include 'csrf.php';

if (isset($_SESSION['role'])) { //Sidebar for users
	if ($_SESSION['role'] != 'user') {
    		include "sidebara.php";
	} else{
    		include "sidebarout.php";
	} 
	}
else {
    		include "sidebar.php";
     }


// Require login
if (!isset($_SESSION['username']) && !isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('You must be logged in to access this page.');
}

// Determine identity by user id or username
$identity = null;
$identity_type = null; // 'id' or 'username'

if (!empty($_SESSION['user_id'])) {
    $identity = (int) $_SESSION['user_id'];
    $identity_type = 'id';
} else {
    // session 'username' may contain id digits or a username string
    $sess = (string) ($_SESSION['username'] ?? '');
    if ($sess !== '' && ctype_digit($sess)) {
        $identity = (int) $sess;
        $identity_type = 'id';
    } else {
        $identity = $sess;
        $identity_type = 'username';
    }
}

$error_message = null;
$success_message = null;
$rows = null;

if ($identity_type === 'id') {
    $sql = 'SELECT id, username, password, email FROM users WHERE id = :id LIMIT 1';
    $params = [':id' => $identity];
} else {
    $sql = 'SELECT id, username, password, email FROM users WHERE username = :username LIMIT 1';
    $params = [':username' => $identity];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetch(PDO::FETCH_ASSOC);



// If the session used username but we fetched the user row, normalize session user_id for future use
if ($identity_type === 'username') {
    $_SESSION['user_id'] = (int)$rows['id'];
}

// Handle POST update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
        $error_message = 'Invalid request.';
    } else {
        $username = substr(trim((string)($_POST['username'] ?? '')), 0, 100);
        $email = substr(trim((string)($_POST['email'] ?? '')), 0, 100);
        $password = substr((string)($_POST['password'] ?? ''), 0, 100);

        if ($username === '' || $email === '') {
            $error_message = 'Username and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // Ensure email field has @gmail.com, or relevant mail
            $error_message = 'Email is not valid.';
        } else {
            // Check conflicts with other users
            // Does not trigger updating of database
            $check = $pdo->prepare('SELECT id FROM users WHERE (email = :email) AND id <> :id LIMIT 1');
            $check->execute([':email' => $email, ':id' => $rows['id']]);
            $conflict = $check->fetch(PDO::FETCH_ASSOC);
            if ($conflict) {
                $error_message = 'Email is already taken.';
            } else {
                // Only run if password field is added
                if ($password !== '') {
                    //Password parameters
                    if (
                        strlen($password) < 8 ||
                        !preg_match('/[A-Z]/', $password) ||
                        !preg_match('/[a-z]/', $password) ||
                        !preg_match('/[0-9]/', $password)
                    ) {
                        $error_message = 'Password must be at least 8 characters long, include an uppercase letter, a lowercase letter, and a number.';
                    } else {
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT); // Hash new password
                    }

                } else {
                    $passwordHash = $rows['password']; // Unchanged password
                }

                // Only update if there is no error
                if (!$error_message) {

                    $update = $pdo->prepare('UPDATE users 
                        SET username = :username, email = :email, password = :password 
                        WHERE id = :id');

                    $update->execute([
                        ':username' => $username,
                        ':email' => $email,
                        ':password' => $passwordHash,
                        ':id' => $rows['id']
                    ]);

                    // reload
                    $stmt = $pdo->prepare('SELECT id, username, password, email FROM users WHERE id = :id LIMIT 1');
                    $stmt->execute([':id' => $rows['id']]);
                    $rows = $stmt->fetch(PDO::FETCH_ASSOC);

                    $_SESSION['username'] = $rows['username'];
                    $_SESSION['user_id'] = (int)$rows['id'];

                    $success_message = 'Your information has been updated successfully!';
                    $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
                }

            }
        }
    }
}
$csrf = generate_csrf_token();  // Generate after updating session
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/stylesform.css">
</head>


<body>
    <div class="containercenter">
    <h2>Account Information</h2>
    
    <?php if ($success_message): ?><p class="success"><?= e($success_message) ?></p><?php endif; ?>
    <?php if ($error_message): ?><p class="error"><?= e($error_message) ?></p><?php endif; ?>

    <table>
        <thead><tr><th>Field</th><th>Value</th></tr></thead>
        <tbody>
            <tr><td><strong>Username</strong></td><td><?= e($rows['username']) ?></td></tr>
            <tr><td><strong>Email</strong></td><td><?= e($rows['email']) ?></td></tr>
            <tr><td><strong>Password</strong></td><td>******* (you cannot view your password)</td></tr>
        </tbody>
    </table>

    <h3>Edit Your Information</h3>
    <form method="post" novalidate>
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <label for="username"> Username </label>
        <input id="username" name="username" type="text" maxlength="100" required value="<?= e($rows['username']) ?>">
            <label for="email"> Email </label>
        <input id="email" name="email" type="email" maxlength="100" required value="<?= e($rows['email']) ?>">
            <label for="password"> New Password </label>
        <input id="password" name="password" type="password" maxlength="100" placeholder="Leave blank to keep current password">
            <button type="submit"> Update Info </button>
    </form>
    
    <a href="pastbookingview.php"> Past Bookings </a><br><br><br>
</body>
</html>
