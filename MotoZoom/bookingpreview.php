<?php
// Most used page
// All booking view will loop back here to display

include 'db.php';
session_start();

// Check for popup message
$popupMessage = $_SESSION['popup_message'] ?? null;
unset($_SESSION['popup_message']); // only show once
$booking_id = $_SESSION['confirm_redirect_booking_id'] ?? null;
unset($_SESSION['confirm_redirect_booking_id']);


// Initialize variables
$start_date_value = '';
$end_date_value   = '';
$pickup_value     = '';
$dropoff_value    = '';
$customer_name    = '';
$customer_email   = '';
$notify_status    = 0;
$bookings         = []; // array to hold valid bookings

// Hardcoded add-ons
$addons = [
    'phone_mount' => ['label' => 'Phone Mount', 'price' => 15],
    'child_seat'  => ['label' => 'Child Seat', 'price' => 20],
    'insurance'   => ['label' => 'Premium Insurance', 'price' => 35],
    'prepaid'     => ['label' => 'Prepaid Fuel Card', 'price' => 10],
    'driver'      => ['label' => 'Additional Driver', 'price' => 25],
];


// Determine booking ID to load
$booking_id = null;
if (isset($_SESSION['pend_booking_id'])) {
    $booking_id = $_SESSION['pend_booking_id'];
} elseif (isset($_SESSION['booking_id'])) {
    $booking_id = $_SESSION['booking_id'];
}


// Load booking details if available
$totalCarPrice = 0;
$selectedAddons = [];
$addonTotal = 0;
$grandTotal = 0;
$canConfirm = false;

// Prepare to update temporary booking database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['booking_id'])) {
    $booking_id = $_SESSION['booking_id'];
    $stmt = $pdo2->prepare("
        UPDATE bookinglist
        SET start_date = :start_date,
            end_date = :end_date,
            pickup_location = :pickup,
            dropoff_location = :dropoff,
            customer_name = :customer_name,
            customer_email = :customer_email,
            notify_status = :notify
        WHERE booking_id = :booking_id
    ");

    $stmt->execute([
        'start_date'    => $_POST['start_date'] ?? '',
        'end_date'      => $_POST['end_date'] ?? '',
        'pickup'        => $_POST['pickup'] ?? '',
        'dropoff'       => $_POST['dropoff'] ?? '',
        'customer_name' => $_POST['customer_name'] ?? '',
        'customer_email'=> $_POST['customer_email'] ?? '',
        'notify'        => isset($_POST['notify_status']) ? 1 : 0,  // Either active or not
        'booking_id'    => $booking_id
    ]);
}
if ($booking_id) {
    // Load main booking info
    $stmt = $pdo2->prepare("
        SELECT *
        FROM bookinglist
        WHERE booking_id = :booking_id
    ");
    $stmt->execute(['booking_id' => $booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking) {
        // Fill date, time and locations
        $start_date_value = date('Y-m-d\TH', strtotime($booking['start_date'])) . ":00";
        $end_date_value   = date('Y-m-d\TH', strtotime($booking['end_date'])) . ":00";
        $pickup_value     = $booking['pickup_location'] ?? '';
        $dropoff_value    = $booking['dropoff_location'] ?? '';

        // Load Customer info
        $customer_name    = $booking['customer_name'] ?? '';
        $customer_email   = $booking['customer_email'] ?? '';
        $notify_status    = $booking['notify_status'] ?? 0;

        // Load cars
        for ($i = 1; $i <= 4; $i++) {
            $slot = $booking["booking{$i}_id"] ?? null;
            if (!empty($slot)) $bookings[] = $slot;
        }

        // Load selected add-ons
        if (!empty($booking['selected_addons'])) {
            $selectedAddons = json_decode($booking['selected_addons'], true);
        }

        // Calculate car total
        foreach ($bookings as $bookingValue) {
            $car_id = (int) trim(strtok($bookingValue, '-'));
            if ($car_id > 0) {
                $stmt = $pdo->prepare("SELECT Car, Stock, Price FROM cars WHERE id = :car_id");
                $stmt->execute(['car_id' => $car_id]);
                $car = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($car && isset($car['Price'])) {
                    $totalCarPrice += (float)$car['Price'];
                }
            }
        }

        // Calculate add-on total
        foreach ($selectedAddons as $addonKey) {
            if (isset($addons[$addonKey])) {
                $addonTotal += $addons[$addonKey]['price'];
            }
        }

        $grandTotal = $totalCarPrice + $addonTotal;

        // Enable confirm button only if booking has at least 1 car + required info
        $canConfirm = !empty($bookings)
            && !empty($start_date_value)
            && !empty($end_date_value)
            && !empty($pickup_value)
            && !empty($dropoff_value);
    }
}

$now = new DateTime();  // Initialise variable

// Convert start/end strings to DateTime objects
$startDT = $start_date_value ? new DateTime($start_date_value) : null;
$endDT   = $end_date_value ? new DateTime($end_date_value) : null;

// Flag to determine if the confirm button can be enabled
$now = new DateTime();
$minStart = (clone $now)->modify('+2 hours'); // start must be at least 2 hours ahead
$maxStart = (clone $now)->modify('+7 days'); // start must not be more than 7 days

$datesAreValid = false;
if ($startDT && $endDT) {
    $intervalSeconds = $endDT->getTimestamp() - $startDT->getTimestamp();
    $minDuration = 24 * 60 * 60; // 24 hours in seconds
    $maxDuration = 7 * 24 * 60 * 60; // 7 days in seconds

    if (
        $startDT >= $minStart &&
        $startDT <= $maxStart &&
        $intervalSeconds >= $minDuration &&
        $intervalSeconds <= $maxDuration
    ) {
        $datesAreValid = true;
    }
}


// Update $canConfirm to also check dates
$canConfirm = $canConfirm && $datesAreValid;


// Error message
$errorMsg = '';
if (isset($_GET['error']) && $_GET['error'] === 'limit') {
    $errorMsg = "You cannot add more than 4 cars to a single booking.";
}

// Handle remove booking slot
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_booking_slot']) && $booking_id) {
    $slotIndex = (int)$_POST['remove_booking_slot']; // 0 to 3
    $slotField = "booking" . ($slotIndex + 1) . "_id";

    $stmt = $pdo2->prepare("UPDATE bookinglist SET $slotField = NULL WHERE booking_id = :booking_id");
    $stmt->execute(['booking_id' => $booking_id]);

    // Remove from current $bookings array so table updates immediately
    // In the event to remove placeholder bookings
    if (isset($bookings[$slotIndex])) {
        unset($bookings[$slotIndex]);
        $bookings = array_values($bookings); // reindex array
    }

    
    header("Location: " . $_SERVER['REQUEST_URI']); // Refresh the page to reflect changes
    exit;
}

// Initialize a flag to disable confirmation
$disableConfirm = false;
$confirmMessage = '';

// Check if there's a pending booking ID and if the booking is active
if (isset($_SESSION['pend_booking_id'])) {
    $pendBookingId = $_SESSION['pend_booking_id'];

    // Query to check the status of the pending booking
    $stmt = $pdo2->prepare("SELECT status FROM pend_booking WHERE pend_booking_id = :pendBookingId");
    $stmt->execute(['pendBookingId' => $pendBookingId]);
    $bookingStatus = $stmt->fetchColumn();

    if (in_array($bookingStatus, ['active', 'inactive', 'overdue', 'returned'], true)) {
        $disableConfirm = true;
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Preview</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/stylesform.css">
    <style>
        #booking-form {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-right: 250px; /* Add right margin */
        }

        #booking-form .form-group {
            flex: 1 1 45%;
            display: flex;
            flex-direction: column;
        }

        #booking-form button {
            flex-basis: 100%;
            margin-top: 10px;
        }
    </style>
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

<div class="layout">

    <div class="container">
        <h1>Booking Page</h1>
        <div class="fixed-textbox">
            <h2>Add-On Features</h2>

            <table width="100%">
                <?php foreach ($addons as $key => $addon): ?>
                    <tr>
                        <td>
                            <label>
                                <input type="checkbox"
                                    class="addon-checkbox"
                                    name="addons[]"
                                    value="<?= $key ?>"
                                    data-price="<?= $addon['price'] ?>">
                                <?= htmlspecialchars($addon['label']) ?>
                            </label>
                        </td>
                        <td style="text-align:right;">
                            $<?= number_format($addon['price'], 2) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>


            <hr>
                <p><strong>Car Total:</strong> $<span id="carTotal">
                    <?= number_format($totalCarPrice, 2, '.', '') ?>
                </span>
                </p>

                <p><strong>Add-Ons Total:</strong> $<span id="addonTotal">0.00</span></p>
            <hr>

            <p style="font-size:18px;">
                <strong>Total Cost:</strong>
                $<span id="grandTotal">
                    <?= number_format($totalCarPrice, 2, '.', '') ?>
                </span>
            </p>

        <form method="post" action="confirm_booking.php" id="confirmForm">
            <input type="hidden" name="confirm_booking" value="1">
            
            <!-- Hidden inputs for totals & addons -->
            <div id="confirmHiddenFields"></div>
            <?php if ($disableConfirm): ?>
                <!-- Display a message if the confirm button is disabled -->
                <p style="color: #842029; font-size:14px; margin-top:5px;">
                    <?= htmlspecialchars($confirmMessage) ?>
                </p>
            <?php endif; ?>
            <button type="submit" id="confirmBtn" <?= $disableConfirm || !$canConfirm ? 'disabled' : '' ?>>
                Confirm Booking
            </button>
        </form>

        <?php if (!$canConfirm): ?>
            <p style="color:#842029; font-size:14px; margin-top:5px;">
                Please select at least one car and complete booking details to confirm.
            </p>
        <?php endif; ?>

        </div>

        <?php if (!empty($errorMsg)): ?>
            <div style="padding:10px; background-color:#f8d7da; color:#842029; border:1px solid #f5c2c7; margin-bottom:15px; border-radius:5px;">
                <?= htmlspecialchars($errorMsg) ?>
            </div>
        <?php endif; ?>


        <h2>Current Booking</h2>
        <table border="1">
            <tbody>
            <?php
            $totalSlots = 4; // Always show 4 options
            $fields = [
                'Car' => 'Car',
                'Model' => 'Model',
                'Availability' => 'Availability'
            ];

            foreach ($fields as $label => $key) {
                echo "<tr><th>$label</th>";

                // Loop through 4 booking slots
                for ($i = 0; $i < $totalSlots; $i++) {
                    $bookingValue = $bookings[$i] ?? null; // Get booking or null if empty

                    if ($bookingValue) {
                        // Explode JSON value
                        if (strpos($bookingValue, ' - ') !== false) {
                            list($car_id, $Model) = explode(' - ', $bookingValue, 2);
                        } else {
                            $car_id = $bookingValue;
                            $Model = 'Unknown Model';
                        }

                        $car_id = (int)$car_id;
                        if ($car_id <= 0) {
                            $bookingValue = null; // Treat invalid ID as empty
                        }
                    }

                    if ($bookingValue) {
                        // Fetch car info from database
                        $stmt = $pdo->prepare("SELECT Car, Stock, Price FROM cars WHERE id = :car_id");
                        $stmt->execute(['car_id' => $car_id]);
                        $car = $stmt->fetch(PDO::FETCH_ASSOC);
                        $carImage = $car['Car'] ?? 'placeholder.png';
                        $stock    = (int)($car['Stock'] ?? 0);
                        $available = $stock >= 1 ? 'Yes' : 'No';

                        if ($key === 'Car') {
                            echo "<td>
                                    <a href='cardetails.php?id=" . $car_id . "'>
                                        <img src='uploads/" . htmlspecialchars($carImage) . "' 
                                            alt='" . htmlspecialchars($Model) . "' width='250'>
                                    </a>";

                            if ($carImage === 'placeholder.png') {
                                echo "<form method='post' style='margin-top:5px;'>
                                        <input type='hidden' name='remove_booking_slot' value='{$i}'>
                                        <button type='submit'>Remove</button>
                                    </form>";
                            }

                            echo "</td>";

                        } elseif ($key === 'Model') {
                            echo "<td>" . htmlspecialchars($Model) . "</td>";

                        } elseif ($key === 'Availability') {
                            echo "<td style='font-weight:bold; background-color:" . ($available === 'Yes' ? 'lightgreen' : 'lightcoral') . ";'>
                                    $available
                                </td>";
                        }

                    } else {
                        // Empty slot → show "Add Car" button
                        if ($key === 'Car') {
                            echo "<td>
                                    <a href='carlist.php'>
                                        <button type='button'>Add Car</button>
                                    </a>
                                </td>";
                        } else {
                            echo "<td>Empty</td>";
                        }
                    }
                }
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
        <h3>Your Booking Time</h3>

        <form method="post" id="booking-form" style="display:flex; flex-wrap:wrap; gap:20px;">
            <!-- Booking Time -->
            <div style="flex:1 1 45%; display:flex; flex-direction:column;">
                <label for="start_date">Start Time:</label>
                <input type="datetime-local" name="start_date" id="start_date" step="3600" required
                    value="<?= htmlspecialchars($start_date_value) ?>">
            </div>

            <div style="flex:1 1 45%; display:flex; flex-direction:column;">
                <label for="end_date">End Time:</label>
                <input type="datetime-local" name="end_date" id="end_date" step="3600" required
                    value="<?= htmlspecialchars($end_date_value) ?>">
            </div>

            <div style="flex:1 1 45%; display:flex; flex-direction:column;">
                <label for="pickup">Pickup Location:</label>
                <input type="text" name="pickup" id="pickup" required
                    value="<?= htmlspecialchars($pickup_value) ?>">
            </div>

            <div style="flex:1 1 45%; display:flex; flex-direction:column;">
                <label for="dropoff">Dropoff Location:</label>
                <input type="text" name="dropoff" id="dropoff" required
                    value="<?= htmlspecialchars($dropoff_value) ?>">

            <!-- Customer Info -->
            <div style="flex:1 1 45%; display:flex; flex-direction:column;">
                <label for="customer_name">Name:</label>
                <input type="text" name="customer_name" id="customer_name" required
                    value="<?= htmlspecialchars($customer_name ?? '') ?>">
            </div>

            <div style="flex:1 1 45%; display:flex; flex-direction:column;">
                <label for="customer_email">Email:</label>
                <input type="email" name="customer_email" id="customer_email" required
                    value="<?= htmlspecialchars($customer_email ?? '') ?>">
            </div>

            <div style="flex-basis:100%; margin-top:10px;">
                <label>
                    <input type="checkbox" name="notify_status" value="1"
                        <?= !empty($notify_status) ? 'checked' : '' ?>>
                    Notify car status
                </label>
            </div>

            <div style="flex-basis:100%; margin-top:15px;">
                <button type="submit" id="updateBtn">Update Booking</button>
            </div>
        </form>


        <!-- Display value thats stored -->
        <?php if ($start_date_value && $end_date_value): ?>
            <p>
                <strong>
                    <?= date('M d, Y H:00', strtotime($start_date_value)) ?>
                    →
                    <?= date('M d, Y H:00', strtotime($end_date_value)) ?>
                </strong>
                <br>
                <strong>Pickup:</strong> <?= htmlspecialchars($pickup_value) ?><br>
                <strong>Dropoff:</strong> <?= htmlspecialchars($dropoff_value) ?>
            </p>
        <?php endif; ?>
    </div>
</div>





<script>
// Various checks to ensure Confirm Button is disabled unless meet requirements
// PHP flag to indicate if the booking is active
const isBookingActive = <?php echo $disableConfirm ? 'true' : 'false'; ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const confirmBtn = document.getElementById('confirmBtn');
    const updateBtn = document.getElementById('updateBtn');
    const requiredFields = ['start_date', 'end_date', 'pickup', 'dropoff', 'customer_name', 'customer_email'];
    const addonCheckboxes = document.querySelectorAll('.addon-checkbox');
    const addonTotalEl = document.getElementById('addonTotal');
    const carTotalVal = parseFloat(document.getElementById('carTotal').innerText);
    const grandTotalEl = document.getElementById('grandTotal');
    const confirmForm = document.getElementById('confirmForm');
    const hiddenContainer = document.getElementById('confirmHiddenFields');

    // Error message container
    const timeErrorEl = document.getElementById('timeError') || (() => {
        const el = document.createElement('p');
        el.id = 'timeError';
        el.style.color = '#842029';
        el.style.fontSize = '14px';
        el.style.marginTop = '5px';
        confirmBtn.parentNode.insertBefore(el, confirmBtn);
        return el;
    })();

    // Force minutes to :00 on submission
    document.getElementById('booking-form')?.addEventListener('submit', function() {
        ['start_date', 'end_date'].forEach(id => {
            const el = document.getElementById(id);
            if (el?.value) el.value = el.value.substr(0, 13) + ":00";
        });
    });

    // Check if start/end times are valid, returns {valid, message}
    function getTimeValidation() {
        const startInput = document.getElementById('start_date');
        const endInput = document.getElementById('end_date');
        if (!startInput.value || !endInput.value) return { valid: false, message: "Start and end dates are required." };

        const startTime = new Date(startInput.value);
        const endTime = new Date(endInput.value);
        const now = new Date();

        const minStart = new Date(now.getTime() + 2 * 60 * 60 * 1000); // 2 hours ahead
        const maxStart = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000); // 7 days ahead
        const diffTime = endTime - startTime;
        const maxDiff = 7 * 24 * 60 * 60 * 1000; // 7 days in ms
        const minDuration = 24 * 60 * 60 * 1000; // 24 hours in ms

        if (startTime < minStart) return { valid: false, message: "Start time must be at least 2 hours from now." };
        if (startTime > maxStart) return { valid: false, message: "Start time cannot be more than 7 days from now." };
        if (diffTime < minDuration) return { valid: false, message: "Booking must be at least 24 hours long." };
        if (diffTime > maxDiff) return { valid: false, message: "Booking duration cannot exceed 7 days." };

        return { valid: true, message: "" };
    }

    // Update addon and grand totals
    function updateTotals() {
        let addonTotal = 0;
        addonCheckboxes.forEach(cb => {
            if (cb.checked) addonTotal += parseFloat(cb.dataset.price);
        });
        addonTotalEl.innerText = addonTotal.toFixed(2);
        grandTotalEl.innerText = (carTotalVal + addonTotal).toFixed(2);
    }
    addonCheckboxes.forEach(cb => cb.addEventListener('change', updateTotals));
    updateTotals(); // Initial calculation

    // Enable/disable Confirm button with error messages
    function checkCanConfirm() {
        const allFilled = requiredFields.every(id => document.getElementById(id)?.value.trim() !== '');
        const { valid, message } = getTimeValidation();
        confirmBtn.disabled = !(allFilled && carTotalVal > 0 && valid && !isBookingActive);
        timeErrorEl.innerText = valid ? "" : message;
    }

    // Enable/disable Update button with error messages
    function checkUpdateButton() {
        const allFilled = requiredFields.every(id => document.getElementById(id)?.value.trim() !== '');
        const { valid, message } = getTimeValidation();
        updateBtn.disabled = !allFilled || !valid;
        timeErrorEl.innerText = valid ? "" : message;
    }

    // Add listeners for input changes
    requiredFields.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', () => {
                checkCanConfirm();
                checkUpdateButton();
            });
        }
    });

    // Initial check
    checkCanConfirm();
    checkUpdateButton();

    // Disable confirm if booking is not pending and show message
    if (isBookingActive) {
        confirmBtn.disabled = true;
        const messageContainer = document.createElement('p');
        messageContainer.style.color = '#842029';
        messageContainer.style.fontSize = '14px';
        messageContainer.style.marginTop = '5px';
        messageContainer.innerText = "This booking is already processed. You cannot confirm this booking anymore.";
        confirmBtn.parentNode.insertBefore(messageContainer, confirmBtn);
    }

    // Form submission: add hidden fields for addons and totals
    confirmForm?.addEventListener('submit', function() {
        hiddenContainer.innerHTML = '';

        addonCheckboxes.forEach(cb => {
            if (cb.checked) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'addons[]';
                input.value = cb.value;
                hiddenContainer.appendChild(input);
            }
        });

        const totals = [
            { name: 'car_total', value: carTotalVal.toFixed(2) },
            { name: 'addon_total', value: parseFloat(addonTotalEl.innerText).toFixed(2) },
            { name: 'grand_total', value: parseFloat(grandTotalEl.innerText).toFixed(2) }
        ];

        totals.forEach(t => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = t.name;
            input.value = t.value;
            hiddenContainer.appendChild(input);
        });
    });

    // Optional PHP popup confirmation
    <?php if ($popupMessage && $booking_id): ?>
    const proceed = confirm("<?= addslashes($popupMessage) ?>");
    if (proceed) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'confirm_booking.php';
        form.innerHTML = `
            <input type="hidden" name="confirm_booking" value="1">
            <input type="hidden" name="confirm_unavailable" value="1">
        `;
        document.body.appendChild(form);
        form.submit();
    } else {
        window.location.href = 'bookingpreview.php';
    }
    <?php endif; ?>
});
</script>

</body>
</html>
