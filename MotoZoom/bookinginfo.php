<?php
// Admin view of bookings with different status, and to update the status
include 'db.php';
session_start();

// Check if the user is logged in and an admin
if ($_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

// Check if an ID is provided, includes modified ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "No booking selected.";            // Incase redirecting failed somehow
    header('Location: index.php');  // In the event the page name was added to the URL
    exit;
}

$bookingId = intval($_GET['id']); // sanitize input

// Fetch the booking from the database
$sql = "SELECT * FROM pend_booking WHERE id = :id LIMIT 1";
$stmt = $pdo2->prepare($sql);
$stmt->execute(['id' => $bookingId]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    echo "Booking not found.";

}

// Include sidebar for admin
include "sidebara.php";

$status = $booking['status'];  // Get the status of the booking
$details = json_decode($booking['selected_details'], true); // Decode selected details JSON

// Recursive function to convert arrays to string
function displayValue($value) {
    if (is_array($value)) {
        $result = [];
        foreach ($value as $k => $v) {
            if (is_array($v)) {
                $result[] = "$k: [" . displayValue($v) . "]";
            } else {
                $result[] = "$k: $v";
            }
        }
        return implode(', ', $result);
    } else {
        return $value;
    }
}

// Handle "Confirm" button click to update the status
if (isset($_POST['confirm'])) {
    // Update status to 'active'
    $updateSql = "UPDATE pend_booking SET status = 'active' WHERE id = :id";
    $updateStmt = $pdo2->prepare($updateSql);
    $updateStmt->execute(['id' => $bookingId]);

    // Update stock for each car in selected details
    if (isset($details['cars']) && is_array($details['cars'])) {
        foreach ($details['cars'] as $car) {
            // Expected format from JSON: "id - Model Name"
            list($carId, $carModel) = explode(' - ', $car);

            // Decrease stock for the car that matches the ID and model
            $stockUpdateSql = "UPDATE cars SET stock = stock - 1 WHERE id = :id AND model = :model";
            $stockUpdateStmt = $pdo->prepare($stockUpdateSql);
            $stockUpdateStmt->execute(['id' => $carId, 'model' => $carModel]);
        }
    }

    header("Location: admin.php"); // Redirect back to admin view
    exit;
}

// Handle "Overdue" button click to update the status
if (isset($_POST['overdue'])) {
    // Update status to 'overdue'
    $updateSql = "UPDATE pend_booking SET status = 'overdue' WHERE id = :id";
    $updateStmt = $pdo2->prepare($updateSql);
    $updateStmt->execute(['id' => $bookingId]);

    header("Location: admin.php"); // Redirect back to admin view
    exit;
}

$carStocks = [];

// Get stock info for selected cars
if (isset($details['cars']) && is_array($details['cars'])) {
    foreach ($details['cars'] as $car) {
        // Expected format from JSON: "id - Model Name"
        list($carId, $carModel) = explode(' - ', $car, 2);

        $stockSql = "SELECT stock FROM cars WHERE id = :id AND model = :model LIMIT 1";
        $stockStmt = $pdo->prepare($stockSql);
        $stockStmt->execute([
            'id' => (int)$carId,
            'model' => $carModel
        ]);

        $stockRow = $stockStmt->fetch(PDO::FETCH_ASSOC);

        $carStocks[] = [
            'id' => $carId,
            'model' => $carModel,
            'stock' => $stockRow ? $stockRow['stock'] : 'N/A'
        ];
    }
}

$canShowOverdue = false;

if (
    $status === 'active' &&
    isset($details['end_date']) &&
    time() > strtotime($details['end_date'])
) {
    $canShowOverdue = true;
}

?>
</div>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/stylesform.css">
    <style>
        /* Forced styling for this page to display table in a specific way*/
        table {
            border-collapse: collapse;
            width: 60%;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Booking Details</h2>
        <?php if ($details): ?>
            <table>
                <tr>
                    <th>Field</th>
                    <th>Value</th>
                </tr>

                <?php foreach ($details as $key => $value): ?>
                <tr>
                    <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?></td>
                    <td>
                        <?php
                        // Loop to display car info relevant for booking
                        if ($key === 'cars' && is_array($value)) {
                            foreach ($value as $car) {
                                // Expected format from JSON: "id - Model Name"
                                list($carId, $carModel) = explode(' - ', $car, 2);

                                // Fetch stock from database
                                $stockSql = "SELECT stock FROM cars WHERE id = :id AND model = :model LIMIT 1";
                                $stockStmt = $pdo->prepare($stockSql);
                                $stockStmt->execute([
                                    'id' => (int)$carId,
                                    'model' => $carModel
                                ]);

                                $stock = $stockStmt->fetchColumn(); // Display remaining stock

                                echo htmlspecialchars($carModel) .
                                    " <strong>(Stock left: " .
                                    htmlspecialchars($stock !== false ? $stock : 'N/A') .
                                    ")</strong><br>";
                            }

                        // Convert notify_status from 0/1 to No/Yes
                        } elseif ($key === 'notify_status') {
                            echo $value ? "Yes" : "No";

                        // For selected_addons, show values without numeric indices
                        } elseif ($key === 'selected_addons' && is_array($value)) {
                            echo htmlspecialchars(implode(', ', $value));

                        // Default: use recursive displayValue function. For remaining parts of JSON
                        } else {
                            echo htmlspecialchars(displayValue($value));
                        }
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td>
                        <strong>Status</strong>
                    </td>
                    <td>
                        <?php echo htmlspecialchars(ucwords($status)); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Last Updated</strong>
                    </td>
                    <td>
                        <?php 
                        echo isset($booking['updated_time']) 
                            ? htmlspecialchars(date("d M Y, H:i", strtotime($booking['updated_time']))) 
                            : 'N/A'; 
                        ?>
                    </td>
                </tr>
                
            </table>

        <?php else: ?>
            <p>No details available for this booking.</p>
        <?php endif; ?>

        <div class="navbar navbar-admin">
            <p><a href="admin.php">← Back to Admin Panel</a></p>
        </div>

        <!-- Show Confirm Button only if status is 'pending' -->
        <?php if ($status === 'pending'): ?>
        <form method="POST">
            <button type="submit" name="confirm" class="button">Confirm</button>
        </form>
        <?php endif; ?>

        <!-- Show Overdue Button only if status is 'active' -->
        <?php if ($canShowOverdue): ?>
        <form method="POST">
            <button type="submit" name="overdue" class="button">Overdue Return</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
