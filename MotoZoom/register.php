<?php
include 'db.php';
include "sidebar.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Account</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/stylesform.css">
</head>

<body>
    <div class="containercenter">
        <h1>Register</h1>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" maxlength="100" required>
            <input type="email" name="email" placeholder="Email" maxlength="100" required>
            <input type="password" name="password" placeholder="Password" maxlength="100" required>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a>.</p>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = substr(trim($_POST['username'] ?? ''), 0, 100);
    $email = substr(trim($_POST['email'] ?? ''), 0, 100);
    $password = substr($_POST['password'] ?? '', 0, 100);
    $role = 'user';

    // Password strength check
    if (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password)
    ) {
        echo "Password must be at least 8 characters long, include an uppercase letter, a lowercase letter, and a number.";
        exit;
    }

    // Check if email already exists
    $checkQuery = "SELECT COUNT(*) FROM users WHERE email = :email";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute([':email' => $email]);
    $emailExists = $checkStmt->fetchColumn();

    if ($emailExists) {
        echo "This email is already registered. Please use a different email or login.";
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $query = "INSERT INTO users (username, email, password, role) 
              VALUES (:username, :email, :password, :role)";
    $stmt = $pdo->prepare($query);

    try {
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':role' => $role
        ]);
        echo nl2br("User registered successfully!\n\nPlease go to the login page to continue.");
    } catch (PDOException $e) {
        echo "An error occurred. Please try again.";
    }
}
?>
</body>
</html>
