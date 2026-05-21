<?php
include 'db.php';
session_start();

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] != 'user') {
        include "sidebara.php";
    } else {
        include "sidebarout.php";
    }
} else {
    include "sidebar.php";
}

// Configuration
$maxAttempts = 5;  // Stop bruteforce
$lockoutTime = 10; // in seconds

// Initialize session variables if not set
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
if (!isset($_SESSION['last_attempt_time'])) {
    $_SESSION['last_attempt_time'] = time();
}

$timeSinceLastAttempt = time() - $_SESSION['last_attempt_time']; // time difference
$remaining = $lockoutTime - $timeSinceLastAttempt;
$remainingAttempts = $maxAttempts - $_SESSION['login_attempts'];

// Regenerate session ID at the start of every attempt
session_regenerate_id(true);

$error_message = null; // for any login error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = substr(trim($_POST['email'] ?? ''), 0, 100);
    $password = substr($_POST['password'] ?? '', 0, 100);
    $_SESSION['login_attempts'] += 1; 
    $_SESSION['last_attempt_time'] = time(); // Reset attempt timer

    if ($remainingAttempts > 0) {
                $error_message = "Invalid email or password. You have $remainingAttempts attempt(s) remaining.";
            } else {
                $error_message = "Too many failed login attempts. Try again in $remaining seconds.";
        }

    if ($remainingAttempts <= 0){
        header('Location: index.php');
        exit;// Redirect to avoid spamming
    }

    // Get user by email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify password
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];
        // Record admin login
        if ($_SESSION['role'] === 'admin') {
            // Insert login time into the admin_login_logs table
            $stmt = $pdo->prepare("INSERT INTO admin_login_logs (username) VALUES (:username)");
            $stmt->execute([':username' => $_SESSION['username']]);
        }

        // Reset login attempts on success
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = time();

        header('Location: account.php'); // Redirect to Account page to view dashboard
        exit;
    }

        sleep(3);   // Slow down brute force
}

if ($lockoutTime < $timeSinceLastAttempt) {
    $_SESSION['login_attempts'] = 0; // Reset after timeout. Require 1 click after timeout though.
    $remainingAttempts = $maxAttempts - $_SESSION['login_attempts']; // rerun to prevent the next if from running
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/stylesform.css">
</head>

<body>
    <div class="containercenter">
        <h1>Login</h1>

        <!-- Display error message if there is one -->
        <?php if ($error_message): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="email" placeholder="Email" maxlength="100" required>
            <input type="password" name="password" placeholder="Password" maxlength="100" required>
            <button type="submit">Login</button>
        </form>
        <a href="change_pass.php">Change Password</a>
        <a href="forgot_pass.php">Forgot Password</a>
    </div>
</body>
</html>