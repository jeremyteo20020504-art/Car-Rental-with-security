<?php
// db.php
$host = 'localhost';
$dbname = 'carsallofthem';// Contains car info, user account and admin login logs
$dbname2 = 'bookingall';  // Contains temporary booking and permanent booking after confirmation of former
$user = 'root';  
$pass = '';  

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo2 = new PDO("mysql:host=$host;dbname=$dbname2", $user, $pass);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
