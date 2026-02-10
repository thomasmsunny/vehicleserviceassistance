<?php
require('../config/autoload.php');

// Check if driver is logged in
if(!isset($_SESSION['driver_id'])){
    header("Location: driverlogin.php");
    exit();
}

$driver_id = $_SESSION['driver_id'];

// Database connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if($conn->connect_error){
    die("Connection Failed: " . $conn->connect_error); 
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id']) && isset($_POST['new_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $new_status = $conn->real_escape_string($_POST['new_status']);
    
    // Validate status transitions based on current status
    // First, get the current status of the booking
    $current_status_sql = "SELECT status FROM bookings WHERE booking_id = ?";
    $current_status_stmt = $conn->prepare($current_status_sql);
    $current_status_stmt->bind_param("i", $booking_id);
    $current_status_stmt->execute();
    $current_status_result = $current_status_stmt->get_result();
    
    if ($current_status_result->num_rows === 0) {
        header("Location: current_working.php?error=" . urlencode("Booking not found"));
        exit();
    }
    
    $current_booking = $current_status_result->fetch_assoc();
    $current_status = $current_booking['status'];
    
    // Define valid status transitions for drivers
    $valid_transitions = [
        'Pending' => ['Picked Up'],
        'Picked Up' => ['In Progress', 'Delivered'],
        'In Progress' => ['Delivered'],
        'Payment Done' => ['Delivered']
    ];
    
    // Check if the transition is valid
    if (!isset($valid_transitions[$current_status]) || !in_array($new_status, $valid_transitions[$current_status])) {
        header("Location: current_working.php?error=" . urlencode("Invalid status transition from '$current_status' to '$new_status'"));
        exit();
    }
    
    // Verify that this booking belongs to the logged-in driver
    $verify_sql = "SELECT booking_id FROM bookings WHERE booking_id = ? AND driver_id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $booking_id, $driver_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows === 0) {
        header("Location: current_working.php?error=" . urlencode("You don't have permission to update this booking"));
        exit();
    }
    
    // Update the booking status
    $update_sql = "UPDATE bookings SET status = ? WHERE booking_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $new_status, $booking_id);
    
    if ($update_stmt->execute()) {
        // Success message
        $message = "Booking status updated to '$new_status' successfully";
        header("Location: current_working.php?message=" . urlencode($message));
        exit();
    } else {
        // Error message
        header("Location: current_working.php?error=" . urlencode("Failed to update booking status"));
        exit();
    }
} else {
    // If not a valid POST request, redirect back
    header("Location: current_working.php");
    exit();
}

$conn->close();
?>