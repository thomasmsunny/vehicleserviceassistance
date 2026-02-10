<?php
require('../config/autoload.php'); // DB connection

// Check if driver is logged in
if(!isset($_SESSION['driver_id'])){
    header("Location: driverlogin.php");
    exit();
}

$page_title = "Current Work";

$driver_id = $_SESSION['driver_id'];
// IMPORTANT: Always use htmlspecialchars() when outputting user-provided data
$driver_name = htmlspecialchars($_SESSION['drivername']);

// Debug: Check what driver_id is being used
// echo "Debug: Driver ID = " . $driver_id . " | Driver Name = " . $driver_name . "<br>";
// die(); // Uncomment to stop execution and see debug info 

// Database connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if($conn->connect_error){
    die("Connection Failed: " . $conn->connect_error); 
}

// Fetch assigned bookings for this driver using PREPARED STATEMENT for security
// --- NEW: Added b.vehicle_number to the SELECT list. ---
// --- NEW: ORDER BY CASE prioritizes 'Pending' and 'Payment Done' jobs. ---
$sql = "SELECT b.*, b.tow_service, b.location_link, b.service_type, b.vehicle_number, c.firstname, c.lastname, c.phone AS customer_phone, c.email AS customer_email
        FROM bookings b
        JOIN customerreg c ON b.customer_id = c.customer_id
        WHERE b.driver_id = ?
        ORDER BY 
            CASE b.status
                WHEN 'Pending' THEN 1
                WHEN 'Picked Up' THEN 2
                WHEN 'Payment Done' THEN 3
                ELSE 99  -- Push completed/other status to the bottom
            END ASC,
            b.booking_date DESC, 
            b.booking_time ASC";

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
    
    .job-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border-left: 4px solid #3498db;
        transition: all 0.3s;
    }
    
    .job-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .job-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .job-title {
        font-size: 1.2em;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }
    
    .job-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 600;
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
    
    .no-jobs {
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
    <h2><i class="fas fa-tools"></i> Current Work</h2>
    <p class="mb-0">Jobs that require your attention</p>
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
            <?php while ($job = $result->fetch_assoc()): ?>
                <div class="job-card">
                    <div class="job-header">
                        <h3 class="job-title">
                            <i class="fas fa-car"></i> 
                            <?= htmlspecialchars($job['vehicle_make'] . ' ' . $job['vehicle_model']) ?>
                            <small class="text-muted">(<?= htmlspecialchars($job['vehicle_number']) ?>)</small>
                        </h3>
                        <span class="job-status status-badge <?= strtolower(str_replace(' ', '-', $job['status'])) ?>">
                            <?= htmlspecialchars($job['status']) ?>
                        </span>
                    </div>
                    
                    <div class="customer-info">
                        <div class="info-item">
                            <i class="fas fa-user"></i>
                            <span class="info-label">Customer:</span>
                            <span><?= htmlspecialchars($job['firstname'] . ' ' . $job['lastname']) ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-phone"></i>
                            <span class="info-label">Phone:</span>
                            <span><?= htmlspecialchars($job['customer_phone']) ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-calendar"></i>
                            <span class="info-label">Date:</span>
                            <span><?= date('M d, Y', strtotime($job['booking_date'])) ?></span>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <?php if ($job['status'] == 'Pending'): ?>
                            <a href="#" class="btn btn-primary" onclick="updateStatus(<?= $job['booking_id'] ?>, 'Picked Up'); return false;">
                                <i class="fas fa-truck-moving"></i> Mark as Picked Up
                            </a>
                        <?php elseif ($job['status'] == 'Picked Up'): ?>
                            <a href="#" class="btn btn-success" onclick="updateStatus(<?= $job['booking_id'] ?>, 'Delivered'); return false;">
                                <i class="fas fa-check"></i> Mark as Delivered
                            </a>
                        <?php elseif ($job['status'] == 'Payment Done'): ?>
                            <a href="#" class="btn btn-success" onclick="updateStatus(<?= $job['booking_id'] ?>, 'Delivered'); return false;">
                                <i class="fas fa-truck-loading"></i> Complete Delivery
                            </a>
                        <?php else: ?>
                            <span class="text-muted">No actions available</span>
                        <?php endif; ?>
                        
                        <?php if (!empty($job['location_link'])): ?>
                            <a href="<?= htmlspecialchars($job['location_link']) ?>" target="_blank" class="btn btn-primary">
                                <i class="fas fa-map-marker-alt"></i> View Location
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-jobs">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <h4>No Current Work</h4>
                <p>You don't have any jobs that require attention at the moment.</p>
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