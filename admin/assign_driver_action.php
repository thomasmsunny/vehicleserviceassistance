<?php
session_start();
require('../config/autoload.php');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') die("Access Denied");

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = (int)$_POST['booking_id'];
    $driver_id = (int)$_POST['driver_id'];

    if($booking_id && $driver_id){
        $stmt = $conn->prepare("UPDATE bookings SET driver_id=? WHERE booking_id=?");
        $stmt->bind_param("ii", $driver_id, $booking_id);
        if($stmt->execute()){
            echo "Driver assigned successfully!";
        } else {
            echo "Error: ".$stmt->error;
        }
        $stmt->close();
    } else {
        echo "Please select a driver.";
    }
}
$conn->close();
?>
