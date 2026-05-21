<?php
// Default page
include 'db.php';
session_start();


// Page setup
$limit = 4; // books per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;



    $stmt = $pdo->prepare("SELECT * FROM cars LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    // Count total books for each page
    $countStmt = $pdo->query("SELECT COUNT(*) FROM cars");
    $totalBooks = $countStmt->fetchColumn();


$totalPages = ceil($totalBooks / $limit);

// Handle booking creation / start-end dates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $start_date = $_POST['start_date'] ?? null;
    $end_date   = $_POST['end_date'] ?? null;

    if ($start_date && $end_date) {
        // Convert "YYYY-MM-DDTHH:MM" → "YYYY-MM-DD HH:MM:SS"
        $start_date_mysql = str_replace('T', ' ', $start_date);
        $end_date_mysql   = str_replace('T', ' ', $end_date);

        if (!isset($_SESSION['booking_id'])) {
            // Create new booking only if none exists
            $stmt = $pdo2->prepare("
                INSERT INTO bookinglist (booking_date, start_date, end_date)
                VALUES (NOW(), :start_date, :end_date)
            ");
            $stmt->execute([
                'start_date' => $start_date_mysql,
                'end_date'   => $end_date_mysql
            ]);

            // Store new booking_id in session
            $_SESSION['booking_id'] = $pdo2->lastInsertId();

        } else {
            // Update existing booking
            $stmt = $pdo2->prepare("
                UPDATE bookinglist
                SET start_date = :start_date, end_date = :end_date
                WHERE booking_id = :booking_id
            ");
            $stmt->execute([
                'start_date'  => $start_date_mysql,
                'end_date'    => $end_date_mysql,
                'booking_id'  => $_SESSION['booking_id']
            ]);
        }

        // Redirect to booking preview after saving the date
        header("Location: bookingpreview.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoZoom</title>
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
        <h2>MotoZoom car rental</h2>

        <div class="containerwide">
            Select booking time. Time chosen has to be at the start of the hour (eg: 1:00pm)
            <form method="post" class="booking-form">
                <input type="datetime-local" name="start_date" step="3600" required>
                <input type="datetime-local" name="end_date" step="3600" required>
                <button type="submit">Continue Booking</button>
            </form>
        </div>
        
        <script>    // Modifies starting input to be at 00, thus requiring user input to change it
        document.getElementById('booking-form').addEventListener('submit', function(e) {
            let start = document.getElementById('start_date');
            let end = document.getElementById('end_date');

            // Round minutes down to 00
            if (start.value) start.value = start.value.substr(0, 13) + ":00";
            if (end.value)   end.value   = end.value.substr(0, 13) + ":00";
        });
        </script>

        <div class="containerinner">
            <h2>Popular rental options!</h2>
            <table border="1">
                <tbody>
                    <?php
                    $rows = $stmt->fetchAll();

                    if (count($rows) > 0) {
                        $fields = [
                            'Car' => 'Car', # Output image
                            'Model' => 'Model', # Output name
                            'Price' => 'Price',
                        ];

                        foreach ($fields as $label => $key) {
                            echo "<tr><th>$label</th>";
                            foreach ($rows as $row) {
                            if ($key === 'Car') {
                                $imagePath = 'uploads/' . htmlspecialchars($row[$key]); 
                                $carId = (int)$row['id'];

                                echo "<td>
                                        <a href='cardetails.php?id=$carId'>
                                            <img src='$imagePath' 
                                                alt='" . htmlspecialchars($row['Car']) . "' 
                                                width='309' 
                                                style='cursor:pointer;'>
                                        </a>
                                    </td>";
                            }   else{
                                $value = ($key === 'price') ? '$' . $row[$key] : $row[$key];
                                echo "<td>" . htmlspecialchars($value) . "</td>";
                            }
                            }
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td>No cars in stock</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        
        <div class="navbar navbar-center">
            <a href="carlist.php">See Full List</a>
        </body>
        </div>
        </div>
</body>

<?php if (isset($_GET['booking']) && $_GET['booking'] === 'success'): ?>
    <div id="successModal">
        <div class="modal-content">
            <h2>Booking Confirmed</h2>
            <p>Your booking has been successfully submitted.</p>
            <p>Check your email to view booking number sent</p>
            <button onclick="closeModal()">OK</button>
        </div>
    </div>

    <script>
    function closeModal() {
        const modal = document.getElementById('successModal');
        modal.remove();
        // Remove query string so popup won't reappear on refresh
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    </script>
<?php endif; ?>

</html>
