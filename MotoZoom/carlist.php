<?php
session_start();
include 'db.php';

// Hard-coded variables
$seatOptions = [2, 4, 5, 6, 7, 8];
$fuelType = ["Electric", "Petrol"];

// Page setup
$limit = 8; // cars per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Collect search/filter inputs
$search = substr(trim($_GET['search'] ?? ''), 0, 100);
$carbrand = trim($_GET['carbrand'] ?? '');
$carseats = trim($_GET['carseats'] ?? '');
$fuelTypeSelected = trim($_GET['FuelType'] ?? '');

// Base query
$query = "SELECT * FROM cars WHERE 1";
$params = [];

// Apply filters dynamically
if (!empty($search)) {
    $query .= " AND Model LIKE :search";
    $params[':search'] = "%$search%";
}
if (!empty($carbrand)) {
    $query .= " AND Brand = :brand";
    $params[':brand'] = $carbrand;
}
if (!empty($carseats)) {
    $query .= " AND Seat = :seats";
    $params[':seats'] = $carseats;
}
if (!empty($fuelTypeSelected)) {
    $query .= " AND FuelType = :fuel";
    $params[':fuel'] = $fuelTypeSelected;
}

// Add pagination
$query .= " LIMIT :limit OFFSET :offset";

// Prepare and execute main query
$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->execute();

// Fetch cars
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total matching cars for pagination
$countQuery = "SELECT COUNT(*) FROM cars WHERE 1";
if (!empty($search)) $countQuery .= " AND Model LIKE :search";
if (!empty($carbrand)) $countQuery .= " AND Brand = :brand";
if (!empty($carseats)) $countQuery .= " AND Seat = :seats";
if (!empty($fuelTypeSelected)) $countQuery .= " AND FuelType = :fuel";

$countStmt = $pdo->prepare($countQuery);
foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}
$countStmt->execute();
$totalcars = $countStmt->fetchColumn();

$totalPages = ceil($totalcars / $limit);    // Page count
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car List</title>
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
        <h1>MotoZoom car rental</h1>

        <!-- Search form -->
        <form method="GET" action="carlist.php" class="booking-form">
            <input 
                type="text" 
                name="search" 
                placeholder="Search for Car model"
                maxlength="100"
                value="<?= htmlspecialchars($search) ?>"
            >

            <?php
            // Fetch all car brands
            $stmt2 = $pdo->query("SELECT DISTINCT Brand FROM cars ORDER BY Brand ASC");
            $brands = $stmt2->fetchAll(PDO::FETCH_COLUMN);
            ?>
            <select name="carbrand">
                <option value="" <?= empty($carbrand) ? 'selected' : '' ?>>Select Car Brand</option>
                <?php foreach ($brands as $brand): ?>
                    <option value="<?= htmlspecialchars($brand) ?>" <?= ($carbrand === $brand) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($brand) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Hardcoded seat count -->
            <select name="carseats">
                <option value="" <?= empty($carseats) ? 'selected' : '' ?>>Select Number of Seats</option>
                <?php foreach ($seatOptions as $seat): ?>
                    <option value="<?= $seat ?>" <?= ($carseats == $seat) ? 'selected' : '' ?>>
                        <?= $seat ?> Seats
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Hardcoded fuel type -->
            <select name="FuelType">
                <option value="" <?= empty($fuelTypeSelected) ? 'selected' : '' ?>>Select fuel type</option>
                <?php foreach ($fuelType as $fuel): ?>
                    <option value="<?= $fuel ?>" <?= ($fuelTypeSelected === $fuel) ? 'selected' : '' ?>>
                        <?= $fuel ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Search</button>
        </form>

        <div class="containerinner">
            <h2>All rental options!</h2>

            <table border="1">
                <tbody>
                    <?php
                    if (count($cars) > 0) {
                        $chunks = array_chunk($cars, 4);
                        $fields = [
                            'Car' => 'Car',
                            'Model' => 'Model',
                            'Price per day' => 'Price',
                            'In Stock?' => 'Stock',
                        ];

                        foreach ($chunks as $group) {
                            foreach ($fields as $label => $key) {
                                echo "<tr><th>$label</th>";
                                foreach ($group as $row) {
                                    if ($key === 'Car') {
                                        $imagePath = 'uploads/' . htmlspecialchars($row[$key]);
                                        $carId = (int)$row['id'];
                                        echo "<td>
                                                <a href='cardetails.php?id=$carId' target='_blank'>
                                                    <img src='$imagePath' alt='" . htmlspecialchars($row['Car']) . "' width='250' style='cursor:pointer;'>
                                                </a>
                                              </td>";
                                    } elseif ($key === 'Stock') {
                                        $availability = ($row[$key] <= 0) ? 'No' : 'Yes';
                                        echo "<td>" . htmlspecialchars($availability) . "</td>";
                                    } else {
                                        $value = ($key === 'Price') ? '$' . $row[$key] : $row[$key];
                                        echo "<td>" . htmlspecialchars($value) . "</td>";
                                    }
                                }
                                echo "</tr>";
                            }
                            echo "<tr><td colspan='5' style='height:20px'></td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No cars found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Page change -->
        <div class="page_change">
            <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">&laquo; Prev</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])); ?>" <?= ($i == $page) ? 'style="font-weight:bold;"' : '' ?>>
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
