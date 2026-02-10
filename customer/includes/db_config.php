<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'vehicleservice');

// Create connection
function getDBConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Test database connection
function testDBConnection() {
    try {
        $pdo = getDBConnection();
        return true;
    } catch(Exception $e) {
        return false;
    }
}

// Get customer by email
function getCustomerByEmail($email) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM customerreg WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return false;
    }
}

// Get customer by ID
function getCustomerById($customer_id) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM customerreg WHERE customer_id = ?");
        $stmt->execute([$customer_id]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return false;
    }
}

// Get bookings for customer
function getBookingsByCustomerId($customer_id) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE customer_id = ? ORDER BY booking_date DESC, booking_time DESC");
        $stmt->execute([$customer_id]);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Get services
function getAllServices() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM services WHERE status = 'active' OR status = '1'");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Get booking status counts
function getBookingStatusCounts($customer_id) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT 
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN status IN ('Confirmed', 'In Progress', 'Quoted') THEN 1 ELSE 0 END) as progress_count,
            SUM(CASE WHEN status IN ('Delivered', 'Payment Done', 'Payment Done') THEN 1 ELSE 0 END) as completed_count
            FROM bookings WHERE customer_id = ?");
        $stmt->execute([$customer_id]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return ['pending_count' => 0, 'progress_count' => 0, 'completed_count' => 0];
    }
}
?>