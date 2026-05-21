<?php
include 'db.php';
session_start();

// Check if the user is an admin, as only admins should access the logs page
if ($_SESSION['role'] != 'admin') {
    header('Location: index.php'); // Redirect to home if not an admin
    exit;
}
include 'sidebara.php'; // only admin access anyway

// Hard-coded options
$seatOptions   = [2, 4, 5, 6, 7, 8];
$fuelOptions   = ["Electric", "Petrol"];
$transOptions  = ["Manual", "Auto"];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect form data
    $model        = substr(trim($_POST['model'] ?? ''), 0, 100);
    $brand        = substr(trim($_POST['brand'] ?? ''), 0, 100);
    $year         = substr(trim($_POST['year'] ?? ''), 0, 100);
    $price        = substr(trim($_POST['price'] ?? ''), 0, 100);
    $others       = substr(trim($_POST['others'] ?? ''), 0, 100);
    $stock        = substr(trim($_POST['stock'] ?? ''), 0, 5); //Cap limit
    $seat         = substr(trim($_POST['seat'] ?? ''), 0, 2); //Support 0-99 as input
    $kmperL       = substr(trim($_POST['kmperL'] ?? ''), 0, 100);
    $transmission = substr(trim($_POST['transmission'] ?? ''), 0, 100);
    $fuelType     = substr(trim($_POST['fuelType'] ?? ''), 0, 100);

    $errors = [];

    /* Image upload and verification */
    if (isset($_FILES['car']) && $_FILES['car']['error'] === 0) {

        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $fileType = mime_content_type($_FILES['car']['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = "Only JPG and PNG images are allowed.";
        }

        if ($_FILES['car']['size'] > 2 * 1024 * 1024) {
            $errors[] = "Image size must be less than 2MB.";
        }

        if (empty($errors)) {
            $uploadDir = "uploads/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExt  = pathinfo($_FILES['car']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid("car_", true) . "." . $fileExt;

            // Move file into uploads folder
            move_uploaded_file($_FILES['car']['tmp_name'], $uploadDir . $fileName);

            // Store ONLY the filename in database
            $car = $fileName;
        }


    } else {
        $errors[] = "Car image is required.";
    }

    /* Validation */
    if (!preg_match('/^\d{4}$/', $year)) {
        $errors[] = "Year must be a 4-digit number.";
    }

    if (!is_numeric($price)) {
        $errors[] = "Price must be numeric.";
    }

    if (!is_numeric($stock)) {
        $errors[] = "Stock must be numeric.";
    }

    if (!in_array($fuelType, $fuelOptions)) {
        $errors[] = "Invalid fuel type selection.";
    }

    if (!in_array($transmission, $transOptions)) {
        $errors[] = "Invalid transmission selection.";
    }

    if (!in_array((int)$seat, $seatOptions)) {
        $errors[] = "Invalid seat selection.";
    }

    if (!is_numeric($kmperL)) {
        $errors[] = "KM per Liter must be numeric.";
    }

    /* Database insert */
    if (empty($errors)) {

        $stmt = $pdo->prepare("
            INSERT INTO cars 
            (car, model, year, price, others, stock, seat, kmperL, transmission, fuelType, brand)
            VALUES 
            (:car, :model, :year, :price, :others, :stock, :seat, :kmperL, :transmission, :fuelType, :brand)
        ");

        $stmt->execute([
            ':car'          => $car,
            ':model'        => $model,
            ':year'         => $year,
            ':price'        => $price,
            ':others'       => $others,
            ':stock'        => $stock,
            ':seat'         => $seat,
            ':kmperL'       => $kmperL,
            ':transmission' => $transmission,
            ':fuelType'     => $fuelType,
            ':brand'        => $brand
        ]);

        echo "<p style='color:white;'>Car added successfully!</p>";
    } else {
        foreach ($errors as $error) {
            echo "<p style='color:red;'>$error</p>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Upload</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/stylesform.css">
</head>
<body>

<div class="containercenter">



<h1>Car Upload</h1>

<form method="POST" action="" enctype="multipart/form-data">
    <label for="car">Upload car image</label>
    <input type="file" id="car" name="car" accept="image/*" required>


    <input type="text" name="model" placeholder="Model" maxlength="100" required>
    <input type="text" name="brand" placeholder="Brand" maxlength="100" required>
    <input type="text" name="year" placeholder="Year (YYYY)" maxlength="100" required>
    <input type="text" name="price" placeholder="Price ($)" maxlength="100" required>
    <input type="text" name="stock" placeholder="Stock" maxlength="5" required>
    <select name="seat" required>
        <option value="">Select Seats</option>
        <?php foreach ($seatOptions as $seat): ?>
            <option value="<?= $seat ?>">
                <?= $seat ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="text" name="kmperL" placeholder="KM per Liter" maxlength="100" required>
    <select name="transmission" required>
        <option value="">Select Transmission</option>
        <?php foreach ($transOptions as $trans): ?>
            <option value="<?= htmlspecialchars($trans) ?>">
                <?= htmlspecialchars($trans) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="fuelType" required>
        <option value="">Select Fuel Type</option>
        <?php foreach ($fuelOptions as $fuel): ?>
            <option value="<?= htmlspecialchars($fuel) ?>">
                <?= htmlspecialchars($fuel) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <textarea name="others" placeholder="Other Details" maxlength="100"></textarea>

    <button type="submit">Add Car</button>
</form>

</div>
</body>
</html>
