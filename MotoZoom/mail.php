<?php

// Check if the user is an admin, shouldn't be accessible anywhere besides testing and debug purposes
if ($_SESSION['role'] != 'admin') {
    header('Location: index.php'); // Redirect to home if not an admin
    exit;
}

$receiver = "o8404175@gmail.com";
$subject  = "Email Test via PHP using Localhost";
$body     = "Hi, there... This is a test email sent from Localhost.";
$sender   = "From: o8404175@gmail.com";

if (mail($receiver, $subject, $body, $sender)) {
    $message = "Email sent successfully to $receiver";
} else {
    $message = "Sorry, failed while sending mail!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Email Test</title>
</head>
<body>

<script>
    alert("<?php echo addslashes($message); ?>");
</script>
<?php
header('Location: index.php');
exit;
?>
</body>
</html>
