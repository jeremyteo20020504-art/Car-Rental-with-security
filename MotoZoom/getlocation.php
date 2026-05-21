<?php
// Vehicle return for user when booking ends
include 'db.php';
session_start();


// AJAX POST HANDLER
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_SERVER['CONTENT_TYPE']) &&
    strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
) {
    header("Content-Type: application/json; charset=UTF-8");

    function json_response($message, $success = true, $redirect = null) {
        $response = ['success' => $success, 'message' => $message];
        if ($redirect) $response['redirect'] = $redirect;
        echo json_encode($response);
        exit;
    }

    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        json_response("Invalid JSON structure.", false);
    }

    $id    = trim($data['id'] ?? '');
    $email = trim($data['email'] ?? '');
    $lat   = $data['latitude'] ?? '';
    $lng   = $data['longitude'] ?? '';
    $renew = !empty($data['renew']);

    // Ensure the input is within 100 character
    $id = substr(trim($data['id'] ?? ''), 0, 100);
    $email = substr(trim($data['email'] ?? ''), 0, 100);


    if (!$id || !$email || !$lat || !$lng) {
        json_response("Missing data.", false);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response("Invalid email format.", false);
    }

    try {   // Load database

        try {
            // Fetch bookings for the given ID
            $stmt = $pdo2->prepare("
                SELECT pend_booking_id, selected_details, status
                FROM pend_booking
                WHERE pend_booking_id = :id
                LIMIT 1
            ");
            $stmt->execute([':id' => $id]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Filter rows by provided email in JSON
            $rows = array_filter($rows, function($row) use ($email) {
                $details = json_decode($row['selected_details'], true);
                return isset($details['customer_email']) && $details['customer_email'] === $email;
            });

            if (empty($rows)) {
                json_response("Invalid ID or Email.", false);
            }

            // Only one booking expected due to unique ID provided
            $booking = reset($rows);

            if (!in_array($booking['status'], ['active', 'overdue'], true)) {
                json_response("Booking is not active or overdue.", false);
            }

            // Decode JSON for easier access
            $details = json_decode($booking['selected_details'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                json_response("Booking details are corrupted.", false);
            }

            // Extract relevant details from JSON
            $bookingCars       = $details['cars'] ?? [];
            $startDate         = $details['start_date'] ?? null;
            $endDate           = $details['end_date'] ?? null;
            $pickupLocation    = $details['pickup'] ?? null;
            $dropoffLocation   = $details['dropoff'] ?? null;
            $customerName      = $details['customer_name'] ?? null;
            $customerEmailJson = $details['customer_email'] ?? null;
            $notifyStatus      = (int)($details['notify_status'] ?? 0);
            $selectedAddons    = $details['selected_addons'] ?? [];
            $carTotal          = $details['car_total'] ?? 0;
            $addonTotal        = $details['addon_total'] ?? 0;
            $grandTotal        = $details['grand_total'] ?? 0;

        } catch (PDOException $e) {
            error_log($e->getMessage());
            json_response("Database error.", false);
        }


                if (!$booking) {
                    json_response("Invalid ID or Email.", false);
                }

                // Update booking
                $stmt = $pdo2->prepare("
                    UPDATE pend_booking
                    SET latitude = :lat,
                        longitude = :lng,
                        returned_at = NOW(),
                        status = 'returned'
                    WHERE pend_booking_id = :id
                    LIMIT 1
                ");
                $stmt->execute([
                    ':lat' => $lat,
                    ':lng' => $lng,
                    ':id'  => $id
                ]);

                // Process JSON details
                $details = json_decode($booking['selected_details'], true);

                // Check if stock was 0
                $wasOutOfStock = false;

                if (!empty($details['cars']) && is_array($details['cars'])) {
                    $checkStmt = $pdo->prepare("
                        SELECT stock FROM cars
                        WHERE id = :id AND model = :model
                        LIMIT 1
                    ");

                    foreach ($details['cars'] as $car) {
                        [$carId, $carModel] = array_map('trim', explode(' - ', $car, 2));

                        if ($carId && $carModel) {
                            $checkStmt->execute([
                                ':id' => (int)$carId,
                                ':model' => $carModel
                            ]);

                            $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
                            if ($row && (int)$row['stock'] === 0) {
                                $wasOutOfStock = true;
                                break;
                            }
                        }
                    }
                }

                // Increase stock
                if (!empty($details['cars']) && is_array($details['cars'])) {
                    $updateStmt = $pdo->prepare("
                        UPDATE cars
                        SET stock = stock + 1
                        WHERE id = :id AND model = :model
                        LIMIT 1
                    ");

                    foreach ($details['cars'] as $car) {
                        [$carId, $carModel] = array_map('trim', explode(' - ', $car, 2));
                        if ($carId && $carModel) {
                            $updateStmt->execute([
                                ':id' => (int)$carId,
                                ':model' => $carModel
                            ]);
                        }
                    }
                }

                // Email notification for those notified and relevant cars that was at 0 stock
                if (
                    !empty($details['notify_status']) &&
                    (int)$details['notify_status'] === 1 &&
                    $wasOutOfStock === true
                ) {
                    $receiver = $booking['customer_email'];
                    $subject  = "MotoZoom Vehicle Available";

                    $carList = implode(", ", $details['cars'] ?? []);

                    $body = "
        Hello,

        The following vehicle(s) were previously unavailable and are now back in stock:

        $carList

        Thank you for choosing our service.
        ";

                    $headers  = "From: no-reply@yourdomain.com\r\n";
                    $headers .= "Content-Type: text/plain; charset=UTF-8";

                    mail($receiver, $subject, $body, $headers);
                }

                // Renew booking
                if ($renew) {
                    $_SESSION['booking_id'] = $id;

                    $stmt = $pdo2->prepare("SELECT * FROM bookinglist WHERE booking_id = :id LIMIT 1");
                    $stmt->execute([':id' => $id]);
                    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($existing) {
                        unset($existing['booking_id'], $existing['created_at'], $existing['updated_at']);

                        $cols = array_keys($existing);
                        $sql  = "INSERT INTO bookinglist (" . implode(',', $cols) . ")
                                VALUES (:" . implode(',:', $cols) . ")";
                        $stmt = $pdo2->prepare($sql);

                        foreach ($existing as $k => $v) {
                            $stmt->bindValue(":$k", $v);
                        }

                        $stmt->execute();
                        $_SESSION['booking_id'] = $pdo2->lastInsertId();

                        json_response("Booking renewed.", true, "bookingpreview.php");
                    }
                }

                json_response("Location uploaded and booking returned successfully.");

    } catch (PDOException $e) {
        error_log($e->getMessage());
        json_response("Database error.", false);
    }
}

// Placed at end to not interrupt JSON
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Return Rental</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/stylesform.css">
</head>
<body>


<div class="containercenter">
    <h2>Return with GPS, ID and Email</h2>

    <form id="returnForm">
        <label>Booking number</label><br>
        <input type="text" id="user_id" maxlength="100"><br><br>

        <label>Email</label><br>
        <input type="email" id="email" maxlength="100"><br><br>

        <label>
            <input type="checkbox" id="renew_booking"> Renew this booking
        </label><br><br>

        <button type="button" id="submitBtn">Upload Location & Return Car</button>
    </form>

    <p id="status"></p>
    <p>Latitude: <span id="lat">-</span></p>
    <p>Longitude: <span id="lng">-</span></p>
</div>

<script>
document.getElementById("submitBtn").addEventListener("click", () => {
    const id = user_id.value.trim();
    const email = document.getElementById("email").value.trim();
    const renew = document.getElementById("renew_booking").checked;
    const status = document.getElementById("status");

    if (!id || !email) {
        status.innerText = "Please enter ID and Email.";
        status.style.color = "#a61300";
        status.style.fontWeight = "bold";
        return;
    }

    navigator.geolocation.getCurrentPosition(pos => {
        fetch("getlocation.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                id,
                email,
                latitude: pos.coords.latitude,
                longitude: pos.coords.longitude,
                renew
            })
        })
        .then(r => r.json())
        .then(d => {
            status.innerText = d.message;
            status.style.color = d.success ? "green" : "#a61300";
            status.style.fontWeight = "bold";
            if (d.redirect) setTimeout(() => location.href = d.redirect, 1000);
        });
    });
});
</script>

</body>
</html>
