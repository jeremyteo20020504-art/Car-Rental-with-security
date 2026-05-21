<?php
// Past bookings for guests
include 'db.php';
session_start();


// Guests are unaffected
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'user'){
        header('Location: pastbookingview.php'); // redirect to all bookings tied to account
        exit;
    }
}


// Sidebar logic
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] != 'user') {
        include "sidebara.php";
    } else {
        include "sidebarout.php";
    } 
} else {
    include "sidebar.php";
}



// Initialize messages
$error_message = '';
$success_message = '';



// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $booking_id = isset($_POST['booking_id']) ? substr(trim($_POST['booking_id']), 0, 100) : '';
    $email = isset($_POST['email']) ? substr(trim($_POST['email']), 0, 100) : '';


// Admin logic
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {

    if (!empty($booking_id)) {

        try {
            // Fetch the internal 'id' using pend_booking_id
            $stmt = $pdo2->prepare("
                SELECT id
                FROM pend_booking 
                WHERE pend_booking_id = :pend_booking_id 
                AND JSON_UNQUOTE(JSON_EXTRACT(selected_details, '$.customer_email')) = :email
                LIMIT 1
            ");

            $stmt->execute([
                ':pend_booking_id' => (int)$booking_id,
                ':email' => $email
            ]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($result) {
                        header('Location: bookinginfo.php?id=' . $result['id']);
                        exit;
                    } else {
                        $error_message = "Booking not found or email does not match.";
                    }

                } catch (PDOException $e) {
                    $error_message = "Database error.";
                }

            } else {
                $error_message = "Please enter a valid Booking ID.";
            }


    // Guest logic
    } else {

        if ($booking_id && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $stmt = $pdo2->prepare("
                    SELECT booking_id 
                    FROM bookinglist 
                    WHERE booking_id = :id AND customer_email = :email
                ");

                $stmt->execute([
                    ':id' => $booking_id,
                    ':email' => $email
                ]);

                $booking = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($booking) {
                    $_SESSION['booking_id'] = $booking['booking_id'];
                    header('Location: bookingpreview.php');
                    exit;
                } else {
                    $error_message = "No booking found with that ID and email.";
                }

            } catch (PDOException $e) {
                $error_message = "Database error.";
            }

        } else {
            $error_message = "Please enter a valid Booking ID and Email.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Past Bookings</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/stylesform.css">
</head>
<body>
<div class="containercenter">
    <h2>Update Booking</h2>

    <?php if ($error_message): ?>
        <p style="color:red;"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <p style="color:green;"><?= htmlspecialchars($success_message) ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
        <div class="form-group">
            <label for="booking_id">Booking ID:</label>
            <input type="text" id="booking_id" name="booking_id" placeholder="Enter Booking ID" maxlength="100" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" maxlength="100" required>
        </div>

        <button type="submit">Submit</button>
    </form>
</div>


</body>
</html>
