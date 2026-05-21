<?php
include 'db.php';
session_start();

// Handle the delete request if action is "delete_car"
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_car' && isset($_POST['id'])) {
    $carId = $_POST['id'];
    try {
        // Prepare the SQL statement to delete the car
        $stmt = $pdo->prepare('DELETE FROM cars WHERE id = :id');
        $stmt->bindParam(':id', $carId, PDO::PARAM_INT);
        
        // Execute the statement
        if ($stmt->execute()) {
            echo 'success'; // Return success message
        } else {
            echo 'failure'; // Return failure message if deletion fails
        }
    } catch (PDOException $e) {
        echo 'failure'; // Return failure message on error
    }
    exit; // Stop further execution of the page
}

// Check if the user is an admin, as only admins should access the logs page
if ($_SESSION['role'] != 'admin') {
    header('Location: index.php'); // Redirect to home if not an admin
    exit;
}
include 'sidebara.php'; // Only admin access anyway
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

try {
    // Fetch all rows from the 'cars' table
    $stmt = $pdo->query('SELECT * FROM cars ORDER BY id ASC');
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all rows as an associative array
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database error.');
}
?>
</div>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Cars</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Car List</h2>
        <p class="small">Showing all cars (do not expose this page publicly).</p>
        <div class="containerinner">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Car</th>
                    <th>Model</th>
                    <th>Year</th>
                    <th>Price</th>
                    <th>Others</th>
                    <th>Stock</th>
                    <th>Seat</th>
                    <th>KM/L</th>
                    <th>Transmission</th>
                    <th>Fuel Type</th>
                    <th>Brand</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cars as $car): ?>
                <tr>
                    <td><?php echo e($car['id']); ?></td>
                    <td>
                        <!-- Display Image for Car from 'uploads/' folder -->
                        <?php if (!empty($car['Car'])): ?>
                            <img src="uploads/<?php echo e(htmlspecialchars($car['Car'])); ?>" alt="<?php echo e($car['Car']); ?>" width="100">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($car['Model']); ?></td>
                    <td><?php echo e($car['Year']); ?></td>
                    <td><?php echo e($car['Price']); ?></td>
                    <td><?php echo e($car['Others']); ?></td>
                    <td><?php echo e($car['Stock']); ?></td>
                    <td><?php echo e($car['Seat']); ?></td>
                    <td><?php echo e($car['KMperL']); ?></td>
                    <td><?php echo e($car['Transmission']); ?></td>
                    <td><?php echo e($car['FuelType']); ?></td>
                    <td><?php echo e($car['Brand']); ?></td>
                    <td>
                        <!-- Delete Button -->
                        <button class="delete-btn" data-id="<?php echo e($car['id']); ?>">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <div class="navbar navbar-admin">
            <p><a href="admin.php">← Back to Admin Panel</a></p>
        </div>
    </div>
</body>
</html>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).on('click', '.delete-btn', function() {
        var carId = $(this).data('id'); // Get the car ID from the data-id attribute
        var row = $(this).closest('tr'); // Get the row of the car

        if (confirm("Are you sure you want to delete this car?")) {
            $.ajax({
                url: '', // The current page URL
                method: 'POST',
                data: { id: carId, action: 'delete_car' }, // Send car ID and an action key to indicate it's a delete request
                success: function(response) {
                    if (response === 'success') {
                        row.fadeOut(); // Fade out the row for smooth removal
                    } else {
                        alert('Failed to delete the car. Please try again.');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        }
    });
</script>
