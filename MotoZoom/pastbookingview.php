<?php
// User version of booking detail, to see everything assigned to the email
include 'db.php';
session_start();

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

// Require login
if (!isset($_SESSION['username']) && !isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('You must be logged in to access this page.');
}

// Handle "View" button click safely
if (isset($_GET['view_booking_id']) && is_numeric($_GET['view_booking_id'])) {
    $booking_id = (int)$_GET['view_booking_id'];

    try {
        // Fetch the booking JSON from the database
        $stmt = $pdo2->prepare("
            SELECT pend_booking_id, selected_details
            FROM pend_booking
            WHERE pend_booking_id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $booking_id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($booking) {
            // Decode JSON
            $details = json_decode($booking['selected_details'], true);

            // Check that the booking belongs to the logged-in user
            // Stops manipulation on client side
            if (isset($details['customer_email']) && $details['customer_email'] === $_SESSION['email']) {
                // Booking exists and belongs to user → store in session
                $_SESSION['pend_booking_id'] = $booking['pend_booking_id'];
                $_SESSION['booking_id'] = $_SESSION['pend_booking_id'];
                header('Location: bookingpreview.php');
                exit;
            } else {
                http_response_code(403); // forbidden
                exit('You are not authorized to view this booking.');
            }
        } else {
            http_response_code(404);
            exit('Booking not found.');
        }
    } catch (PDOException $e) {
        exit('Database error: ' . $e->getMessage());
    }
}


// Initialize messages
$error_message = null;
$success_message = null;
$rows = [];

// Pagination setup
$limit = 5; // bookings per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch bookings for logged-in user
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    try {
        $stmt = $pdo2->prepare("
            SELECT pend_booking_id, selected_details, status
            FROM pend_booking
            ORDER BY pend_booking_id DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Filter rows by logged-in user's email
        $rows = array_filter($rows, function($row) use ($email) {
            $details = json_decode($row['selected_details'], true);
            return isset($details['customer_email']) && $details['customer_email'] === $email;
        });

        // Decode JSON for easier access in table
        foreach ($rows as &$row) {
            $row['details'] = json_decode($row['selected_details'], true);
            unset($row['selected_details']);
        }

        $totalRows = count($rows);
        $totalPages = ceil($totalRows / $limit);

    } catch (PDOException $e) {
        $error_message = "Failed to fetch bookings: " . $e->getMessage();
    }
}

// Handle "New Booking" button click
if (isset($_POST['new_booking'])) {
    unset($_SESSION['booking_id']);
    unset($_SESSION['pend_booking_id']);

    header('Location: bookingpreview.php');
    exit;
}

// Helper function to escape output
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Past Bookings</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/stylesform.css">
</head>
<body>
<div class="container">
    <h2>Past Bookings</h2>

    <?php if ($success_message): ?>
        <p class="success"><?= e($success_message) ?></p>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <p class="error"><?= e($error_message) ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Booking Date</th>
                <th>Number of Cars</th>
                <th>Add-Ons</th>
                <th>Total Cost</th>
                <th>Status</th>
                <th>View Booking</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($rows)): ?>
            <?php foreach ($rows as $row): 
                $details = $row['details'] ?? [];

                $cars = isset($details['cars']) ? implode(', ', $details['cars']) : 'N/A';
                $start_date = $details['start_date'] ?? 'N/A';
                $end_date = $details['end_date'] ?? 'N/A';
                $addons = !empty($details['selected_addons']) ? implode(', ', $details['selected_addons']) : 'None';
                $grand_total = $details['grand_total'] ?? 'N/A';
                $status = $row['status'] ?? 'Unknown';
            ?>
            <tr>
                <td>
                    Start: <?= e(date('Y-m-d H:i', strtotime($start_date))) ?><br>
                    End: <?= e(date('Y-m-d H:i', strtotime($end_date))) ?>
                </td>
                <td><?= e(count($details['cars'] ?? [])) ?></td>
                <td><?= e($addons) ?></td>
                <td>$<?= e($grand_total) ?></td>
                <td><?= e(ucfirst($status)) ?></td>
                <td><a href="?view_booking_id=<?= e($row['pend_booking_id']) ?>">View</a></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No bookings found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <form method="post" style="margin-bottom: 20px;">
        <button type="submit" name="new_booking">New Booking</button>
    </form>

    <!-- Page -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&laquo; Prev</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
