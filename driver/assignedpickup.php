<?php
require('../config/autoload.php'); // DB connection

// Check if driver is logged in
if(!isset($_SESSION['driver_id'])){
    header("Location: driverlogin.php");
    exit();
}

$driver_id = $_SESSION['driver_id'];
// IMPORTANT: Always use htmlspecialchars() when outputting user-provided data
$driver_name = htmlspecialchars($_SESSION['drivername']); 
$page_title = "Pending Pickups";

// Database connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if($conn->connect_error){
    die("Connection Failed: " . $conn->connect_error); 
}

// Fetch assigned bookings for this driver using PREPARED STATEMENT for security
$sql = "SELECT b.*, b.tow_service, b.location_link, b.service_type, c.firstname, c.lastname, c.phone AS customer_phone, c.email AS customer_email
        FROM bookings b
        JOIN customerreg c ON b.customer_id = c.customer_id
        WHERE b.driver_id = ? AND b.status = 'Pending'
        ORDER BY b.booking_date DESC, b.booking_time ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();

include("includes/driver_header.php");
?>

<style>
    .page-header {
        background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
    }
    
    .booking-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border-left: 4px solid #3498db;
        transition: all 0.3s;
    }
    
    .booking-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .booking-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .booking-title {
        font-size: 1.2em;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }
    
    .customer-info {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 15px;
    }
    
    .info-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .info-label {
        font-weight: 600;
        color: #7f8c8d;
        font-size: 0.9em;
    }
    
    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #eee;
    }
    
    .btn {
        padding: 8px 15px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9em;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.3s;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: white;
        border: none;
    }
    
    .btn-success {
        background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
        color: white;
        border: none;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .no-bookings {
        text-align: center;
        padding: 40px;
        color: #7f8c8d;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .status-badge {
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.8em;
        font-weight: 600;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <h2><i class="fas fa-truck-pickup"></i> Pending Pickups</h2>
    <p class="mb-0">Bookings waiting for vehicle pickup</p>
</div>

<?php 
// --- MESSAGE DISPLAY LOGIC ---
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';

if ($message):
?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo urldecode($message); ?>
    </div>
<?php 
endif;

if ($error):
?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> Error: <?php echo urldecode($error); ?>
    </div>
<?php 
endif;
// --- END MESSAGE DISPLAY LOGIC ---
?>

<div class="row">
    <div class="col-12">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($booking = $result->fetch_assoc()): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <h3 class="booking-title">
                            <i class="fas fa-car"></i> 
                            <?= htmlspecialchars($booking['service_type']) ?>
                        </h3>
                        <span class="status-badge pending">
                            <?= htmlspecialchars($booking['status']) ?>
                        </span>
                    </div>
                    
                    <div class="customer-info">
                        <div class="info-item">
                            <i class="fas fa-user"></i>
                            <span class="info-label">Customer:</span>
                            <span><?= htmlspecialchars($booking['firstname'] . ' ' . $booking['lastname']) ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-phone"></i>
                            <span class="info-label">Phone:</span>
                            <span><?= htmlspecialchars($booking['customer_phone']) ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-calendar"></i>
                            <span class="info-label">Date:</span>
                            <span><?= date('M d, Y', strtotime($booking['booking_date'])) ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <span class="info-label">Time:</span>
                            <span><?= htmlspecialchars($booking['booking_time']) ?></span>
                        </div>
                    </div>
                    
                    <div class="customer-info">
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span class="info-label">Location:</span>
                            <?php if (!empty($booking['location_link'])): ?>
                                <a href="<?= htmlspecialchars($booking['location_link']) ?>" target="_blank">
                                    View on Map
                                </a>
                            <?php else: ?>
                                <span>N/A</span>
                            <?php endif; ?>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-truck"></i>
                            <span class="info-label">Tow Service:</span>
                            <span><?= ($booking['tow_service'] == 1 || strtolower($booking['tow_service']) == 'yes') ? 'Yes' : 'No' ?></span>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="#" class="btn btn-primary" onclick="updateStatus(<?= $booking['booking_id'] ?>, 'Picked Up'); return false;">
                            <i class="fas fa-truck-moving"></i> Mark as Picked Up
                        </a>
                        
                        <?php if (!empty($booking['location_link'])): ?>
                            <a href="<?= htmlspecialchars($booking['location_link']) ?>" target="_blank" class="btn btn-success">
                                <i class="fas fa-map-marker-alt"></i> View Location
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-bookings">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <h4>No Pending Pickups</h4>
                <p>You don't have any pending pickups at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Status Update Form -->
<form id="statusForm" method="POST" action="update_booking_status.php" style="display: none;">
    <input type="hidden" name="booking_id" id="bookingId">
    <input type="hidden" name="new_status" id="newStatus">
</form>

<script>
function updateStatus(bookingId, newStatus) {
    if (confirm('Are you sure you want to update the status to "' + newStatus + '"?')) {
        document.getElementById('bookingId').value = bookingId;
        document.getElementById('newStatus').value = newStatus;
        document.getElementById('statusForm').submit();
    }
}
</script>

<?php
$conn->close();
include("includes/driver_footer.php");
?>