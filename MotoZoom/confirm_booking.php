<?php
include 'db.php';
session_start();

// Validate request
if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['confirm_booking']) ||
    empty($_SESSION['booking_id'])
) {
    header('Location: bookingpreview.php');
    exit;
}

// User explicitly confirmed unavailable cars
if (!empty($_POST['confirm_unavailable'])) {
    $_SESSION['skip_unavailable_check'] = true;
}


$booking_id = $_SESSION['booking_id'];

// Load booking info
$stmt = $pdo2->prepare("
    SELECT 
        start_date,
        end_date,
        pickup_location,
        dropoff_location,
        customer_name,
        customer_email,
        notify_status,
        booking1_id,
        booking2_id,
        booking3_id,
        booking4_id
    FROM bookinglist
    WHERE booking_id = :id
");
$stmt->execute(['id' => $booking_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {    // Most likely if the user entered the page into URL
    header('Location: booking.php');
    exit;
}

// Validate booking start/end time similar to JS getTimeValidation()
$startDate = new DateTime($booking['start_date']);
$endDate   = new DateTime($booking['end_date']);
$now       = new DateTime('now', new DateTimeZone('UTC'));

// 2 hours ahead
$minStart = clone $now;
$minStart->modify('+2 hours');

// 7 days ahead
$maxStart = clone $now;
$maxStart->modify('+7 days');

// Duration
$diffMs = $endDate->getTimestamp() - $startDate->getTimestamp();
$maxDiff = 7 * 24 * 60 * 60; // 7 days in seconds

$timeError = '';

// Checked incase of bypass in bookingpreview
if ($startDate < $minStart) {
    $timeError = "Start time must be at least 2 hours from now.";
} elseif ($startDate > $maxStart) {
    $timeError = "Start time cannot be more than 7 days from now.";
} elseif ($endDate <= $startDate) {
    $timeError = "End time must be after start time.";
} elseif ($diffMs > $maxDiff) {
    $timeError = "Booking duration cannot exceed 7 days.";
}

if ($timeError) {   // Redirect to modify and resubmit booking
    $_SESSION['popup_message'] = $timeError;
    header('Location: bookingpreview.php');
    exit;
}


// Collect cars and check stock
$bookings = [];
$hasUnavailable = false;

// Check if user already confirmed unavailable cars
$skipUnavailableCheck = !empty($_SESSION['skip_unavailable_check']);

foreach (['booking1_id','booking2_id','booking3_id','booking4_id'] as $col) {
    if (!empty($booking[$col])) {
        $bookings[] = $booking[$col];

        if (!$skipUnavailableCheck) {
            // Check stock
            $car_id = (int) trim(strtok($booking[$col], '-'));
            if ($car_id > 0) {
                $stmt = $pdo->prepare("SELECT Stock FROM cars WHERE id = :id");
                $stmt->execute(['id' => $car_id]);
                $car = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($car && (int)$car['Stock'] === 0) {
                    $hasUnavailable = true;
                }
            }
        }
    }
}

// Warning for 0 stock cars
if ($hasUnavailable) {
    $_SESSION['popup_message'] = "One of the cars you selected is unavailable. Do you want to proceed?";
    $_SESSION['confirm_redirect_booking_id'] = $booking_id; 
    header('Location: bookingpreview.php');
    exit;
}

if (empty($bookings)) {
    header('Location: booking.php');
    exit;
}

// Calculate total car price
$totalCarPrice = 0;
foreach ($bookings as $bookingValue) {
    $car_id = (int) trim(strtok($bookingValue, '-'));
    if ($car_id > 0) {
        $stmt = $pdo->prepare("SELECT Price FROM cars WHERE id = :id");
        $stmt->execute(['id' => $car_id]);
        $car = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($car) $totalCarPrice += (float)$car['Price'];
    }
}

// Read POST values for add-ons and totals
$selectedAddons = $_POST['addons'] ?? [];
$addonTotal     = isset($_POST['addon_total']) ? (float)$_POST['addon_total'] : 0;
$carTotal       = isset($_POST['car_total']) ? (float)$_POST['car_total'] : $totalCarPrice;
$grandTotal     = isset($_POST['grand_total']) ? (float)$_POST['grand_total'] : ($carTotal + $addonTotal);

// Build selected_details JSON
$selectedDetails = [
    'cars'           => $bookings,
    'start_date'     => $booking['start_date'],
    'end_date'       => $booking['end_date'],
    'pickup'         => $booking['pickup_location'],
    'dropoff'        => $booking['dropoff_location'],
    'customer_name'  => $booking['customer_name'],
    'customer_email' => $booking['customer_email'],
    'notify_status'  => (int)$booking['notify_status'],
    'selected_addons'=> $selectedAddons,
    'car_total'      => $carTotal,
    'addon_total'    => $addonTotal,
    'grand_total'    => $grandTotal
];

$selectedDetailsJson = json_encode($selectedDetails, JSON_PRETTY_PRINT);

// Determine updated_time (+8 hours) to GMT+8
$dt = new DateTime('now', new DateTimeZone('UTC'));
$dt->modify('+8 hours');
$updatedTime = $dt->format('Y-m-d H:i:s');

// Check for existing pend_booking row (including status)
$stmt = $pdo2->prepare("
    SELECT pend_booking_id, status 
    FROM pend_booking 
    WHERE pend_booking_id = :id
");
$stmt->execute(['id' => $booking_id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {

    // If booking exists but is NOT pending → block update
    // Checked incase of bypass in bookingpreview
    if ($existing['status'] !== 'pending') {
        $_SESSION['popup_message'] = "This booking can no longer be modified because it is already processed.";
        header('Location: bookingpreview.php');
        exit;
    }


    // Status is pending → allow update
    $stmt = $pdo2->prepare("
        UPDATE pend_booking
        SET
            selected_details = :details,
            updated_time     = :updated_time
        WHERE pend_booking_id = :booking_id
    ");

    $stmt->execute([
        'details'      => $selectedDetailsJson,
        'updated_time' => $updatedTime,
        'booking_id'   => $booking_id
    ]);

} else {

    // Insert new row (default status is pending in database)
    $stmt = $pdo2->prepare("
        INSERT INTO pend_booking (
            pend_booking_id,
            selected_details,
            updated_time
        ) VALUES (
            :booking_id,
            :details,
            :updated_time
        )
    ");

    $stmt->execute([
        'booking_id'   => $booking_id,
        'details'      => $selectedDetailsJson,
        'updated_time' => $updatedTime
    ]);
}


/* SEND HTML EMAIL TO CUSTOMER */
$emailDetails = $selectedDetails;
unset($emailDetails['customer_name'], $emailDetails['customer_email'], $emailDetails['notify_status']);

$receiver = $booking['customer_email'];
$subject  = "Your Booking #$booking_id Details";

// Build HTML table
$tableRows = '';
foreach ($emailDetails as $key => $value) {
    if (is_array($value)) $value = implode(', ', $value);
    $tableRows .= "<tr><td style='padding:5px; border:1px solid #ccc;'><b>" . htmlspecialchars(ucwords(str_replace('_',' ',$key))) . "</b></td><td style='padding:5px; border:1px solid #ccc;'>" . htmlspecialchars($value) . "</td></tr>";
}

$body = "
<html>
<head>
    <title>MotoZoom Booking Details</title>
</head>
<body>
    <p>Hello " . htmlspecialchars($booking['customer_name']) . ",</p>
    <p>Your booking (ID: $booking_id) has been confirmed with the following details:</p>
    <table style='border-collapse: collapse; width: 100%;'>
        $tableRows
    </table>
    <p>Thank you for choosing our service.</p>
</body>
</html>
";

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";
$headers .= "From: no-reply@yourdomain.com\r\n";

mail($receiver, $subject, $body, $headers);

// Clear session variables
unset($_SESSION['booking_id']); 
unset($_SESSION['pend_booking_id']); 
unset($_SESSION['skip_unavailable_check']);

// Redirect with success popup
header('Location: index.php?booking=success');
exit;
