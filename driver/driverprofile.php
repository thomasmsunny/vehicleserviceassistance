<?php
require('../config/autoload.php');

// Ensure driver is logged in
if (!isset($_SESSION['driver_id'])) {
    header("Location: driverlogin.php");
    exit();
}

$page_title = "My Profile";

// Database connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the driver's details from the drivermanage table using the EXACT column names
$driver_id = $_SESSION['driver_id'];

// SQL uses the column names: did, drivername, email, phone, created_at
$sql = "SELECT did, drivername, email, phone, created_at FROM drivermanage WHERE did = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
$driver = $result->fetch_assoc();

// If driver data isn't found, redirect or show an error
if (!$driver) {
    $conn->close();
    // Redirect to logout or error page if session ID is invalid
    header("Location: driverlogout.php"); 
    exit();
}

// Data preparation
$full_name = htmlspecialchars($driver['drivername']);
$initial = strtoupper(substr($full_name, 0, 1));
$join_date = $driver['created_at'];

$conn->close();

include("includes/driver_header.php");
?>

<style>
    .profile-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        margin-bottom: 30px;
    }
    
    .profile-header-section {
        background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
        color: white;
        padding: 40px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        text-align: center;
    }
    
    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        color: white;
        margin-bottom: 20px;
        border: 4px solid rgba(255,255,255,0.3);
        font-weight: bold;
    }
    
    .profile-name {
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 10px 0;
    }
    
    .profile-meta {
        font-size: 14px;
        opacity: 0.9;
    }
    
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .detail-item {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        border-left: 4px solid #3498db;
        transition: transform 0.3s;
    }
    
    .detail-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .detail-label {
        display: block;
        font-size: 13px;
        color: #7f8c8d;
        margin-bottom: 8px;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    .detail-value {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        word-break: break-word;
    }
    
    .action-buttons {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .btn {
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: white;
        border: none;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
    }
    
    .btn-danger {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
        border: none;
    }
    
    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
    }
</style>

<!-- Profile Header -->
<div class="profile-header-section">
    <div class="profile-avatar">
        <?= $initial ?>
    </div>
    <h1 class="profile-name"><?= $full_name ?></h1>
    <p class="profile-meta">
        <i class="fas fa-calendar-alt"></i> Driver since <?= date('M d, Y', strtotime($join_date)) ?>
    </p>
</div>

<!-- Profile Details Card -->
<div class="profile-card">
    <h3 style="margin-bottom: 25px; color: #2c3e50;">
        <i class="fas fa-user-circle"></i> Personal Information
    </h3>
    
    <div class="detail-grid">
        <div class="detail-item">
            <span class="detail-label">
                <i class="fas fa-envelope"></i> Email Address
            </span>
            <span class="detail-value"><?= htmlspecialchars($driver['email']) ?></span>
        </div>
        
        <div class="detail-item">
            <span class="detail-label">
                <i class="fas fa-phone"></i> Phone Number
            </span>
            <span class="detail-value"><?= htmlspecialchars($driver['phone']) ?></span>
        </div>
        
        <div class="detail-item">
            <span class="detail-label">
                <i class="fas fa-id-badge"></i> Driver ID
            </span>
            <span class="detail-value">#<?= htmlspecialchars($driver['did']) ?></span>
        </div>
        
        <div class="detail-item">
            <span class="detail-label">
                <i class="fas fa-user"></i> Full Name
            </span>
            <span class="detail-value"><?= htmlspecialchars($driver['drivername']) ?></span>
        </div>
    </div>
    
    <div class="action-buttons">
        <a href="dashboard.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
        <a href="logout.php" class="btn btn-danger">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<?php
include("includes/driver_footer.php");