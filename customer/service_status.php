<?php
session_start();

// Database connection
require_once 'includes/db_config.php';

// Redirect if not logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: customerlogin.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Handle delete request
$delete_message = '';
$delete_error = '';

if (isset($_POST['delete_booking']) && is_numeric($_POST['delete_booking'])) {
    $booking_id = $_POST['delete_booking'];
    
    try {
        $pdo = getDBConnection();
        
        // Verify this booking belongs to the customer
        $stmt = $pdo->prepare("SELECT status FROM bookings WHERE booking_id = ? AND customer_id = ?");
        $stmt->execute([$booking_id, $customer_id]);
        $booking = $stmt->fetch();
        
        if ($booking) {
            // Check if customer can delete based on status
            $status = strtolower($booking['status']);
            $can_delete = false;
            
            // Customer can delete in these statuses:
            // 1. Pending
            // 2. In Progress (optional)
            // 3. Quoted (last possible stage)
            if ($status === 'pending' || $status === 'quoted') {
                $can_delete = true;
            } else if ($status === 'in progress') {
                // Optional: Allow deletion in "In Progress" status
                $can_delete = true; // Set to false if you want to restrict this
            }
            
            if ($can_delete) {
                // Delete the booking
                $stmt = $pdo->prepare("DELETE FROM bookings WHERE booking_id = ? AND customer_id = ?");
                $stmt->execute([$booking_id, $customer_id]);
                
                if ($stmt->rowCount() > 0) {
                    $delete_message = "Booking request deleted successfully.";
                } else {
                    $delete_error = "Failed to delete booking request.";
                }
            } else {
                $delete_error = "You can only delete bookings with status: Pending, In Progress, or Quoted.";
            }
        } else {
            $delete_error = "Booking not found or you don't have permission to delete it.";
        }
    } catch (PDOException $e) {
        $delete_error = "An error occurred while deleting the booking.";
    }
}

// Fetch bookings with service and vehicle details
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT b.*, q.quote_id, q.grand_total as quote_amount FROM bookings b LEFT JOIN quotations q ON b.booking_id = q.booking_id WHERE b.customer_id = ? ORDER BY b.booking_date DESC, b.booking_time DESC");
    $stmt->execute([$customer_id]);
    $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    $bookings = [];
}

// Fetch statistics
$stats = getBookingStatusCounts($customer_id);

// Handle quote viewing
$show_quote = false;
$quote_data = null;
$quote_items = [];

if (isset($_GET['view_quote']) && is_numeric($_GET['view_quote'])) {
    $booking_id = $_GET['view_quote'];
    
    // Verify this booking belongs to the customer
    try {
        $pdo = getDBConnection();
        
        // Get quote details
        $stmt = $pdo->prepare("SELECT q.*, b.vehicle_make, b.vehicle_model, b.vehicle_number, b.service_type FROM quotations q JOIN bookings b ON q.booking_id = b.booking_id WHERE q.booking_id = ? AND b.customer_id = ?");
        $stmt->execute([$booking_id, $customer_id]);
        $quote_data = $stmt->fetch();
        
        if ($quote_data) {
            // Get quote items
            $stmt = $pdo->prepare("SELECT * FROM quotation_items WHERE booking_id = ?");
            $stmt->execute([$booking_id]);
            $quote_items = $stmt->fetchAll();
            
            $show_quote = true;
        }
    } catch (PDOException $e) {
        // Handle error
    }
}

// Handle booking details viewing
$show_details = false;
$booking_details = null;

if (isset($_GET['view_details']) && is_numeric($_GET['view_details'])) {
    $booking_id = $_GET['view_details'];
    
    // Verify this booking belongs to the customer
    try {
        $pdo = getDBConnection();
        
        // Get booking details
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_id = ? AND customer_id = ?");
        $stmt->execute([$booking_id, $customer_id]);
        $booking_details = $stmt->fetch();
        
        if ($booking_details) {
            $show_details = true;
        }
    } catch (PDOException $e) {
        // Handle error
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Status - AutoFix</title>
    <style>
        :root {
            --primary: #ff4a17;
            --primary-dark: #e04010;
            --secondary: #2c3e50;
            --accent: #3498db;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --success: #28a745;
            --warning: #ffc107;
            --info: #17a2b8;
            --danger: #dc3545;
            --border-radius: 8px;
            --box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.6;
        }
        
        /* Header Styles */
        .header {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 5%;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        
        .logo-icon {
            font-size: 28px;
            color: var(--primary);
            margin-right: 10px;
        }
        
        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: var(--secondary);
            letter-spacing: -0.5px;
        }
        
        .logo-text span {
            color: var(--primary);
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
        }
        
        .nav-menu li {
            margin-left: 25px;
        }
        
        .nav-menu a {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: var(--transition);
            position: relative;
            padding: 5px 0;
        }
        
        .nav-menu a:hover {
            color: var(--primary);
        }
        
        .nav-menu a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: var(--transition);
        }
        
        .nav-menu a:hover::after {
            width: 100%;
        }
        
        .cta-button {
            background: var(--primary);
            color: white;
            padding: 10px 25px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            border: 2px solid var(--primary);
        }
        
        .cta-button:hover {
            background: transparent;
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 74, 23, 0.3);
        }
        
        .main {
            margin-top: 80px;
            padding: 30px 5%;
        }
        
        .status-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .status-header {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .status-header h1 {
            color: var(--secondary);
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .status-header p {
            color: var(--gray);
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            text-align: center;
            transition: var(--transition);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.pending {
            border-top: 4px solid var(--gray);
        }
        
        .stat-card.progress {
            border-top: 4px solid var(--warning);
        }
        
        .stat-card.complete {
            border-top: 4px solid var(--success);
        }
        
        .stat-card .number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .stat-card.pending .number {
            color: var(--gray);
        }
        
        .stat-card.progress .number {
            color: var(--warning);
        }
        
        .stat-card.complete .number {
            color: var(--success);
        }
        
        .stat-card .label {
            font-size: 1.1rem;
            color: var(--dark);
        }
        
        .bookings-table {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            overflow: hidden;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .table-header h2 {
            color: var(--secondary);
            font-size: 1.8rem;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: var(--secondary);
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        
        tr:hover {
            background-color: rgba(0,0,0,0.02);
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            min-width: 100px;
        }
        
        .status-pending {
            background-color: #6c757d;
            color: white;
        }
        
        .status-confirmed {
            background-color: #17a2b8;
            color: white;
        }
        
        .status-in-progress {
            background-color: #ffc107;
            color: #000;
        }
        
        .status-progress {
            background-color: #ffc107;
            color: #000;
        }
        
        .status-completed {
            background-color: #28a745;
            color: white;
        }
        
        .status-delivered {
            background-color: #28a745;
            color: white;
        }
        
        .status-payment-done {
            background-color: #20c997;
            color: white;
        }
        
        .status-pay-now {
            background-color: #007bff;
            color: white;
        }
        
        .status-picked-up {
            background-color: #6f42c1;
            color: white;
        }
        
        .status-quoted {
            background-color: #6f42c1;
            color: white;
        }
        
        .status-cancelled {
            background-color: #dc3545;
            color: white;
        }
        
        .btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            margin: 2px 0;
            text-align: center;
        }
        
        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: transparent;
            color: var(--secondary);
            border: 2px solid var(--secondary);
            text-align: center;
        }
        
        .btn-secondary:hover {
            background: var(--secondary);
            color: white;
        }
        
        .btn-view {
            background: var(--secondary);
            color: white;
            text-align: center;
        }
        
        .btn-quote {
            background: #6f42c1;
            color: white;
            text-align: center;
        }
        
        .btn-pay {
            background: #007bff;
            color: white;
            text-align: center;
        }
        
        .btn-pay:hover {
            background: #0056b3;
        }
        
        .btn-delete {
            background: var(--danger);
            color: white;
            text-align: center;
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
        
        .status-with-quote {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        /* Alert Styles */
        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Quote Modal Styles */
        .modal {
            display: <?php echo $show_quote ? 'block' : 'none'; ?>;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            background: var(--secondary);
            color: white;
            padding: 20px;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h2 {
            margin: 0;
            font-size: 1.8rem;
        }
        
        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #ccc;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .quote-header {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            background: var(--light);
            padding: 20px;
            border-radius: var(--border-radius);
        }
        
        .quote-section {
            margin-bottom: 25px;
        }
        
        .quote-section h3 {
            color: var(--secondary);
            border-bottom: 2px solid var(--secondary);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .quote-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .quote-detail {
            margin-bottom: 10px;
        }
        
        .quote-detail strong {
            color: var(--secondary);
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .items-table th {
            background: var(--secondary);
            color: white;
        }
        
        .items-table th, .items-table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        
        .items-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .total-section {
            text-align: right;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
        }
        
        .total-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
        }
        
        .total-label {
            width: 200px;
            text-align: right;
            padding-right: 20px;
        }
        
        .total-value {
            width: 150px;
            text-align: right;
            font-weight: bold;
        }
        
        .grand-total {
            font-size: 1.3rem;
            color: var(--primary);
            border-top: 2px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        /* Delete Confirmation Modal */
        .delete-modal {
            display: none;
            position: fixed;
            z-index: 2001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .delete-modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 30px;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .delete-modal-content h3 {
            color: var(--danger);
            margin-bottom: 20px;
        }
        
        .delete-modal-content p {
            margin-bottom: 25px;
            font-size: 1.1rem;
        }
        
        .delete-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        @media (max-width: 768px) {
            .status-header {
                padding: 20px;
            }
            
            .status-header h1 {
                font-size: 2rem;
            }
            
            .table-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            th, td {
                padding: 10px 8px;
                font-size: 14px;
            }
            
            .status-badge {
                font-size: 12px;
                padding: 4px 8px;
                min-width: 80px;
            }
            
            .quote-header {
                grid-template-columns: 1fr;
            }
            
            .total-row {
                flex-direction: column;
                align-items: flex-end;
            }
            
            .total-label, .total-value {
                width: auto;
                text-align: right;
            }
            
            .delete-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="customerpage/index.php" class="logo">
                <div class="logo-icon">🔧</div>
                <div class="logo-text">Auto<span>Fix</span></div>
            </a>
            
            <ul class="nav-menu">
                <li><a href="customerpage/index.php">Home</a></li>
                <li><a href="customerpage/index.php#about">About</a></li>
                <li><a href="customerpage/index.php#services">Services</a></li>
                <li><a href="customerpage/index.php#contact">Contact</a></li>
            </ul>
            
            <a href="profile.php" class="cta-button">My Profile</a>
        </div>
    </header>

    <main class="main">
        <div class="status-container">
            <div class="status-header">
                <h1>Service Status</h1>
                <p>Track your vehicle service requests and bookings</p>
            </div>
            
            <?php if($delete_message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($delete_message); ?></div>
            <?php endif; ?>
            
            <?php if($delete_error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($delete_error); ?></div>
            <?php endif; ?>
            
            <div class="stats-cards">
                <div class="stat-card pending">
                    <div class="number"><?php echo $stats['pending_count'] ?? 0; ?></div>
                    <div class="label">Pending</div>
                </div>
                
                <div class="stat-card progress">
                    <div class="number"><?php echo $stats['progress_count'] ?? 0; ?></div>
                    <div class="label">In Progress</div>
                </div>
                
                <div class="stat-card complete">
                    <div class="number"><?php echo $stats['completed_count'] ?? 0; ?></div>
                    <div class="label">Completed</div>
                </div>
            </div>
            
            <div class="bookings-table">
                <div class="table-header">
                    <h2>Recent Bookings</h2>
                </div>
                
                <div class="table-responsive">
                    <?php if (!empty($bookings)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Vehicle</th>
                                    <th>Service Type</th>
                                    <th>Booking Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td>#<?php echo $booking['booking_id']; ?></td>
                                        <td><?php echo htmlspecialchars($booking['vehicle_make'] . ' ' . $booking['vehicle_model'] . ' (' . $booking['vehicle_number'] . ')'); ?></td>
                                        <td><?php echo htmlspecialchars($booking['service_type']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                        <td>
                                            <?php if (strtolower($booking['status']) === 'quoted' && !empty($booking['quote_id'])): ?>
                                                <a href="?view_quote=<?php echo $booking['booking_id']; ?>" class="btn btn-quote">View Quote</a>
                                            <?php else: ?>
                                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $booking['status'])); ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="actions">
                                                <?php 
                                                // Determine which actions to show based on status
                                                $status = strtolower($booking['status']);
                                                
                                                // Show Pay Now button for "Pay Now" status
                                                if ($status === 'pay now'): ?>
                                                    <a href="payment.php?booking_id=<?php echo $booking['booking_id']; ?>" class="btn btn-pay">Pay Now</a>
                                                <?php endif; ?>
                                                
                                                <?php 
                                                // Show View Bill button for "Payment Done" status
                                                if ($status === 'payment done'): ?>
                                                    <a href="view_bill.php?booking_id=<?php echo $booking['booking_id']; ?>" class="btn btn-view">View Bill</a>
                                                <?php endif; ?>
                                                
                                                <?php 
                                                // Show Delete button only for allowed statuses
                                                // Customer can delete in: Pending, In Progress, Quoted
                                                if ($status === 'pending' || $status === 'in progress' || $status === 'quoted'): ?>
                                                    <button class="btn btn-delete" onclick="confirmDelete(<?php echo $booking['booking_id']; ?>)">Delete Request</button>
                                                <?php endif; ?>
                                                
                                                <?php 
                                                // Show View Details button for other statuses
                                                if ($status !== 'pending' && $status !== 'in progress' && $status !== 'quoted' && $status !== 'pay now' && $status !== 'payment done'): ?>
                                                    <a href="?view_details=<?php echo $booking['booking_id']; ?>" class="btn btn-view">View Details</a>
                                                <?php endif; ?>

                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No bookings found. <a href="bookingfor.php">Book a service now</a>.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="bookingfor.php" class="btn">Book New Service</a>
                <a href="profile.php" class="btn btn-secondary" style="margin-left: 15px;">Back to Profile</a>
            </div>
        </div>
    </main>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="delete-modal">
        <div class="delete-modal-content">
            <h3>Confirm Deletion</h3>
            <p>Are you sure you want to delete this booking request? This action cannot be undone.</p>
            <form method="POST" id="deleteForm">
                <input type="hidden" name="delete_booking" id="deleteBookingId">
                <div class="delete-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                    <button type="submit" class="btn btn-delete">Delete Request</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Quote Modal -->
    <div id="quoteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Service Quotation</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <?php if ($show_quote && $quote_data): ?>
                    <div class="quote-header">
                        <div>
                            <h3>Quote Details</h3>
                            <div class="quote-detail"><strong>Quote ID:</strong> #<?php echo $quote_data['quote_id']; ?></div>
                            <div class="quote-detail"><strong>Date:</strong> <?php echo date('M d, Y', strtotime($quote_data['quote_date'])); ?></div>
                            <div class="quote-detail"><strong>Prepared By:</strong> <?php echo htmlspecialchars($quote_data['admin_name']); ?></div>
                        </div>
                        <div>
                            <h3>Vehicle Information</h3>
                            <div class="quote-detail"><strong>Vehicle:</strong> <?php echo htmlspecialchars($quote_data['vehicle_make'] . ' ' . $quote_data['vehicle_model']); ?></div>
                            <div class="quote-detail"><strong>Number:</strong> <?php echo htmlspecialchars($quote_data['vehicle_number']); ?></div>
                            <div class="quote-detail"><strong>Service:</strong> <?php echo htmlspecialchars($quote_data['service_type']); ?></div>
                        </div>
                    </div>
                    
                    <div class="quote-section">
                        <h3>Service Items</h3>
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($quote_items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['item_description']); ?></td>
                                        <td><?php echo $item['item_quantity']; ?></td>
                                        <td>₹<?php echo number_format($item['unit_price'], 2); ?></td>
                                        <td>₹<?php echo number_format($item['item_total'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="total-section">
                        <div class="total-row">
                            <div class="total-label">Subtotal:</div>
                            <div class="total-value">₹<?php echo number_format($quote_data['subtotal'], 2); ?></div>
                        </div>
                        <?php if ($quote_data['discount'] > 0): ?>
                            <div class="total-row">
                                <div class="total-label">Discount:</div>
                                <div class="total-value">-₹<?php echo number_format($quote_data['discount'], 2); ?></div>
                            </div>
                        <?php endif; ?>
                        <div class="total-row">
                            <div class="total-label">CGST (<?php echo $quote_data['cgst_rate']; ?>%):</div>
                            <div class="total-value">₹<?php echo number_format($quote_data['total_tax'] / 2, 2); ?></div>
                        </div>
                        <div class="total-row">
                            <div class="total-label">SGST (<?php echo $quote_data['sgst_rate']; ?>%):</div>
                            <div class="total-value">₹<?php echo number_format($quote_data['total_tax'] / 2, 2); ?></div>
                        </div>
                        <div class="total-row grand-total">
                            <div class="total-label">Grand Total:</div>
                            <div class="total-value">₹<?php echo number_format($quote_data['grand_total'], 2); ?></div>
                        </div>
                    </div>
                    
                    <?php if (!empty($quote_data['other_works_notes'])): ?>
                        <div class="quote-section">
                            <h3>Additional Notes</h3>
                            <p><?php echo htmlspecialchars($quote_data['other_works_notes']); ?></p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p>Quote not found or unavailable.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Booking Details Modal -->
    <div id="detailsModal" class="modal" style="display: <?php echo $show_details ? 'block' : 'none'; ?>;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Booking Details</h2>
                <span class="close" onclick="closeDetailsModal()">&times;</span>
            </div>
            <div class="modal-body">
                <?php if ($show_details && $booking_details): ?>
                    <div class="quote-header">
                        <div>
                            <h3>Booking Information</h3>
                            <div class="quote-detail"><strong>Booking ID:</strong> #<?php echo $booking_details['booking_id']; ?></div>
                            <div class="quote-detail"><strong>Booking Date:</strong> <?php echo date('M d, Y', strtotime($booking_details['booking_date'])); ?></div>
                            <div class="quote-detail"><strong>Booking Time:</strong> <?php echo date('h:i A', strtotime($booking_details['booking_time'])); ?></div>
                            <div class="quote-detail"><strong>Status:</strong> <?php echo ucfirst($booking_details['status']); ?></div>
                        </div>
                        <div>
                            <h3>Vehicle Information</h3>
                            <div class="quote-detail"><strong>Make:</strong> <?php echo htmlspecialchars($booking_details['vehicle_make']); ?></div>
                            <div class="quote-detail"><strong>Model:</strong> <?php echo htmlspecialchars($booking_details['vehicle_model']); ?></div>
                            <div class="quote-detail"><strong>Registration:</strong> <?php echo htmlspecialchars($booking_details['vehicle_number']); ?></div>
                        </div>
                    </div>
                    
                    <div class="quote-section">
                        <h3>Service Details</h3>
                        <div class="quote-detail"><strong>Service Type:</strong> <?php echo htmlspecialchars($booking_details['service_type']); ?></div>
                        <div class="quote-detail"><strong>Tow Service:</strong> <?php echo $booking_details['tow_service'] ? 'Yes' : 'No'; ?></div>
                        <?php if (!empty($booking_details['customer_notes'])): ?>
                            <div class="quote-detail"><strong>Notes:</strong> <?php echo htmlspecialchars($booking_details['customer_notes']); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="quote-section">
                        <h3>Location</h3>
                        <div class="quote-detail">
                            <strong>Service Location:</strong> 
                            <a href="<?php echo htmlspecialchars($booking_details['location_link']); ?>" target="_blank">View on Map</a>
                        </div>
                    </div>
                <?php else: ?>
                    <p>Booking details not found or unavailable.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Close modal when clicking on X or outside the modal
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('quoteModal');
            var detailsModal = document.getElementById('detailsModal');
            var span = document.getElementsByClassName('close')[0];
            
            if (span) {
                span.onclick = function() {
                    modal.style.display = 'none';
                    // Remove query parameter from URL
                    window.location.href = window.location.pathname;
                }
            }
            
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                    // Remove query parameter from URL
                    window.location.href = window.location.pathname;
                }
                if (event.target == detailsModal) {
                    detailsModal.style.display = 'none';
                    // Remove query parameter from URL
                    window.location.href = window.location.pathname;
                }
            }
        });
        
        // Delete confirmation functions
        function confirmDelete(bookingId) {
            document.getElementById('deleteBookingId').value = bookingId;
            document.getElementById('deleteModal').style.display = 'block';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        // Close delete modal when clicking outside
        window.addEventListener('click', function(event) {
            var deleteModal = document.getElementById('deleteModal');
            if (event.target == deleteModal) {
                deleteModal.style.display = 'none';
            }
        });
        
        // Details modal functions
        function closeDetailsModal() {
            document.getElementById('detailsModal').style.display = 'none';
            // Remove query parameter from URL
            window.location.href = window.location.pathname;
        }
    </script>
</body>
</html>