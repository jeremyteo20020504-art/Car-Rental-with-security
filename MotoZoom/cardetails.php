<?php
// Extended car details to view
session_start();
include 'db.php';

$id = (int)($_GET['id'] ?? 0);  // Set car ID

if ($id === 0) {
    header('Location: index.php'); // Redirect to home if invalid car id is given
    exit;
}

$stmt = $pdo->prepare("SELECT Car, Model, Year, Price, Stock, Seat, FuelType, KMperL, Transmission, Others FROM cars WHERE id = :id");
$stmt->execute(['id' => $id]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    header('Location: index.php'); // Redirect to home if invalid car chosen
    exit;
}

// When removing a car from booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_car'])) {

    if (!isset($_SESSION['booking_id'])) {
        header("Location: cardetails.php?id=" . (int)$_POST['car_id']);
        exit;
    }

    $booking_id = $_SESSION['booking_id'];  // Update existing booking, if it exist
    $car_id = (int)$_POST['car_id'];        
    $needle = $car_id . ' -';               // Set up JSON file

    // Clear matching booking from ALL column
    $stmt = $pdo2->prepare("
        UPDATE bookinglist
        SET
            booking1_id = IF(booking1_id LIKE :needle, NULL, booking1_id),
            booking2_id = IF(booking2_id LIKE :needle, NULL, booking2_id),
            booking3_id = IF(booking3_id LIKE :needle, NULL, booking3_id),
            booking4_id = IF(booking4_id LIKE :needle, NULL, booking4_id)
        WHERE booking_id = :booking_id
    ");
    $stmt->execute([
        'needle' => $needle . '%',
        'booking_id' => $booking_id
    ]);

    // Re-fetch booking AFTER update
    $stmt = $pdo2->prepare("
        SELECT booking1_id, booking2_id, booking3_id, booking4_id
        FROM bookinglist
        WHERE booking_id = :booking_id
    ");
    $stmt->execute(['booking_id' => $booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    // If booking is empty → clear session
    if (
        empty($booking['booking1_id']) &&
        empty($booking['booking2_id']) &&
        empty($booking['booking3_id']) &&
        empty($booking['booking4_id'])
    ) {
        unset($_SESSION['booking_id']);
    }

    header("Location: bookingpreview.php"); // View booking
    exit;
}


// When adding a car to booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_now'])) {

    $car_id = (int)$_POST['car_id'];
    $bookingValue = $car_id . ' - ' . $car['Model'];

    // Check if booking already exists in session
    if (!isset($_SESSION['booking_id'])) {

        // Create new booking, only if none exists
        $stmt = $pdo2->prepare("
            INSERT INTO bookinglist (booking_date, booking1_id)
            VALUES (NOW(), :booking1)
        ");
        $stmt->execute([
            'booking1' => $bookingValue
        ]);

        $booking_id = $pdo2->lastInsertId();
        $_SESSION['booking_id'] = $booking_id;

    } else {

        // Update existing booking
        $booking_id = $_SESSION['booking_id'];

        // Find next available booking slot
        // Includes booking1 incase its removed
        $stmt = $pdo2->prepare("
            SELECT booking1_id, booking2_id, booking3_id, booking4_id
            FROM bookinglist
            WHERE booking_id = :booking_id
        ");
        $stmt->execute(['booking_id' => $booking_id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            // Reset session if booking row vanished
            unset($_SESSION['booking_id']);
            header("Location: cardetails.php?id=" . $car_id);
            exit;
        }

        // Determine next empty column to insert
        if (empty($booking['booking2_id'])) {
            $column = 'booking2_id';
        } elseif (empty($booking['booking3_id'])) {
            $column = 'booking3_id';
        } elseif (empty($booking['booking4_id'])) {
            $column = 'booking4_id';
        } else {
            // Max cars reached
            header("Location: bookingpreview.php?error=limit");
            exit;
        }

        // Update the correct column
        $stmt = $pdo2->prepare("
            UPDATE bookinglist
            SET $column = :value
            WHERE booking_id = :booking_id
        ");
        $stmt->execute([
            'value' => $bookingValue,
            'booking_id' => $booking_id
        ]);
    }

    // Redirect after success
    header("Location: bookingpreview.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($car['Model']) ?> Details</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/stylesform.css">
</head>
<body>

    <div class="navbar"></div>
        <?php
            if (isset($_SESSION['role'])) {
                if ($_SESSION['role'] != 'user') {
                    include "sidebara.php";
                } else {
                    include "sidebarout.php";
                }
            } else {
                include "sidebar.php";
            }
        ?>
    </div>
    <div class="container">
        <h2>Car Details</h2>
        <div class="containerinner">
        <table>
            <tr>
                <th>Car</th>
                <td>
                    <img 
                        src="uploads/<?= htmlspecialchars($car['Car']) ?>" 
                        alt="<?= htmlspecialchars($car['Model']) ?>" 
                        width="300">
                </td>
            <th rowspan="5">Description</th>
                <td rowspan="5">
                    <strong>Number of Seats:</strong> <?= htmlspecialchars($car['Seat']) ?><br>
                    <strong>Mileage(KM/L):</strong> <?= htmlspecialchars($car['KMperL']) ?><br>
                    <strong>Fuel Type:</strong> <?= htmlspecialchars($car['FuelType']) ?><br>
                    <strong>Transmission:</strong><?= htmlspecialchars($car['Transmission']) ?><br>
                    <strong>Description:</strong><?= htmlspecialchars($car['Others']) ?><br>
                </td>
            </tr>

            <tr>
                <th>Model</th>
                <td><?= htmlspecialchars($car['Model']) ?></td>
            </tr>

            <tr>
                <th>Year</th>
                <td><?= htmlspecialchars($car['Year']) ?></td>
            </tr>

            <tr>
                <th>Price per day</th>
                <td>$<?= htmlspecialchars($car['Price']) ?></td>
            </tr>
            <tr>
                <th>Availability</th>
                <td><?= ($car['Stock'] <= 0) ? 'No' : 'Yes' ?></td>
            </tr>

        </table>
        <td rowspan="5">

            <form method="POST">
                <input type="hidden" name="car_id" value="<?= (int)$id ?>">
                <button type="submit" name="book_now">Book Now</button>
            </form>
        

            <form method="POST" style="margin-top:10px;">
                <input type="hidden" name="car_id" value="<?= (int)$id ?>">
                <button type="submit" name="remove_car">Remove from Booking</button>
            </form>
        </td>

</body>
</html>
