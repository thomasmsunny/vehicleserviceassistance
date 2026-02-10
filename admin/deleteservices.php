<?php
require('../config/autoload.php');

$dao = new DataAccess();

// Check if ID is provided
if (!isset($_GET['id'])) {
    echo "<script>alert('Invalid service ID'); window.location.href='viewservices1.php';</script>";
    exit();
}

$service_id = $_GET['id'];

// Delete the service using DataAccess
if ($dao->delete('services', 'sid=' . $service_id)) {
    echo "<script>alert('Service deleted successfully!'); window.location.href='viewservices1.php';</script>";
} else {
    echo "<script>alert('Failed to delete service'); window.location.href='viewservices1.php';</script>";
}

exit();
?>
