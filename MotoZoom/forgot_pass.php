<?php
include 'db.php';
include "sidebar.php";
session_start();

// make PDO throw exceptions
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function generate_token(): string { return bin2hex(random_bytes(32)); } // Generate a unique token

// Configuration
$token_ttl_seconds = 600; // 10 mins
$from_email = 'o8404175@gmail.com'; // Sender email
$site_base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); //Website with token attached at the back

// POST: submit email
$sent_message = null;
$error_message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please provide a valid email address.';
    } else {
        try {
            // Find the user by email
            $stmt = $pdo->prepare('SELECT id, username, email FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Always show the same response to avoid user enumeration
            if ($user) {
                // Create a unique token and set expiration time
                $token = generate_token();
                $expires_at = date('Y-m-d H:i:s', time() + $token_ttl_seconds); // 10 mins expiry

                // Insert token into the database (replace existing token if any)
                $insert = $pdo->prepare('INSERT INTO password_resets (user_id, token, expires_at, used) VALUES (:user_id, :token, :expires_at, 0)
                                         ON DUPLICATE KEY UPDATE token = :token, expires_at = :expires_at, used = 0');
                $insert->execute([
                    ':user_id' => $user['id'],
                    ':token' => $token,
                    ':expires_at' => $expires_at
                ]);

                // Compose email
                $subject = 'Password Reset Request';
                $message = "Hello " . $user['username'] . ",\n\n";
                $message .= "We received a request to reset your password. To reset your password, click the link below:\n\n";
                $message .= $site_base . "/change_pass.php?token=" . $token . "\n\n";
                $message .= "The link will expire in 10 mins. If you did not request a password reset, you can safely ignore this email.\n\n";
                $message .= "This is an automated message. Please do not reply to this email.";

                // Send email
                $headers = 'From: ' . $from_email . "\r\n" .
                           'Reply-To: ' . $from_email . "\r\n" .
                           'X-Mailer: PHP/' . phpversion();

                // Attempt to send email; ignore failure to avoid leaking info
                @mail($user['email'], $subject, $message, $headers);
            }

            // Message phrased in a way that does not reveal existing email
            $sent_message = 'If an account with that email exists, a password reset link has been sent to your email address. Check the spam email if it is not in the Inbox.'; // Keep response generic
        } catch (Exception $e) {
            $error_message = 'An error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/stylesform.css">
</head>
<body>
    <div class="containercenter">
    <h2>Forgot Password</h2>

    <?php if ($sent_message): ?><p style="color:green;"><?= e($sent_message) ?></p><?php endif; ?>
    <?php if ($error_message): ?><p style="color:red;"><?= e($error_message) ?></p><?php endif; ?>

    <form method="post" novalidate>
        <label for="email">Enter your account email</label>
        <input id="email" name="email" type="email" required placeholder="you@gmail.com">
        <br>
        <button type="submit">Send Reset Link</button>
    </form>
</body>
</html>
