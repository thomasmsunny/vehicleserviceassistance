<?php
session_start();
require('../config/autoload.php');

// Only admin or driver can access
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin','driver'])) {
    header("Location: login.php");
    exit();
}

// Set page title
$page_title = "Update Booking Status";

// Determine if the current user is a driver
$is_driver = ($_SESSION['user_role'] === 'driver');

// Database connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define the statuses a driver is *only* allowed to change to.
$driver_allowed_statuses = ['Picked Up', 'Delivered'];

// Define the full list of status options for the Admin (in workflow order)
$admin_status_options = [
    'Pending' => 'Pending',
    'In Progress' => 'In Progress',
    'Picked Up' => 'Picked Up',
    'Complete' => 'Complete',
    'Pay Now' => 'Pay Now',
    'Payment Done' => 'Payment Done',
    'Delivered' => 'Delivered'
];

// Set the options for the <select> element based on the user's role
if ($is_driver) {
    // Driver sees ONLY Picked Up and Delivered
    $status_options = array_combine($driver_allowed_statuses, $driver_allowed_statuses);
} else {
    // Admin sees all status options
    $status_options = $admin_status_options;
}


// Handle form submission
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $booking_id = (int)$_POST['booking_id'];
    $status = $_POST['status'];

    // =========================================================================
    // CRITICAL SECURITY VALIDATION: Enforce status restrictions based on role
    // =========================================================================
    $is_status_valid = false;
    if ($is_driver) {
        // Driver check: Must be one of the allowed driver statuses
        if (in_array($status, $driver_allowed_statuses)) {
            $is_status_valid = true;
        } else {
            $msg = "Error: Drivers are **only** allowed to set status to 'Picked Up' or 'Delivered'.";
        }
    } else {
        // Admin check: Must be one of the full status options
        if (isset($admin_status_options[$status])) {
             $is_status_valid = true;
        } else {
            $msg = "Error: Invalid status selected by Administrator.";
        }
    }
    
    if ($is_status_valid) {
        $stmt = $conn->prepare("UPDATE bookings SET status=? WHERE booking_id=?");
        $stmt->bind_param("si", $status, $booking_id);
        
        if($stmt->execute()){
            $msg = "Booking status updated successfully to **" . htmlspecialchars($status) . "**!";
        } else {
            $msg = "Error updating status: " . $stmt->error;
        }
    }
}
// =========================================================================
// End of POST handling and security checks
// =========================================================================


// Handle search and filter
$search_number = "";
$filter_status = "";
$search_sql = "SELECT booking_id, vehicle_make, vehicle_model, vehicle_number, status FROM bookings";
$conditions = [];

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_number = $conn->real_escape_string(trim($_GET['search']));
    $conditions[] = "vehicle_number LIKE '%$search_number%'";
}

if (isset($_GET['filter_status']) && !empty($_GET['filter_status'])) {
    $filter_status = $conn->real_escape_string($_GET['filter_status']);
    $conditions[] = "status='$filter_status'";
}

if (count($conditions) > 0) {
    $search_sql .= " WHERE " . implode(' AND ', $conditions);
}

$search_sql .= " ORDER BY booking_date DESC";

$bookings_result = $conn->query($search_sql);
if(!$bookings_result){
    die("Query failed: " . $conn->error);
}

// Note: $status_options is already defined and set based on $is_driver role.

// Include admin header
if (!$is_driver) {
    include("includes/admin_header.php");
    include("includes/status_styles.php");
}
?>

<?php if ($is_driver): ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Booking Status</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include("includes/status_styles.php"); ?>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #E0F2F1; margin: 0; padding: 20px; }
        .driver-container { max-width: 1000px; margin: auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .driver-header { text-align: center; margin-bottom: 30px; color: #176B87; }
    </style>
</head>
<body>
    <div class="driver-container">
        <div class="driver-header">
            <h2><i class="fa fa-edit"></i> Update Booking Status</h2>
        </div>
<?php else: ?>

<style>
    .page-header {
        background: linear-gradient(135deg, #176B87 0%, #1cc88a 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 5px 15px rgba(23, 107, 135, 0.3);
        animation: slideDown 0.5s ease-out;
    }
    
    .page-header h2 {
        margin: 0;
        font-size: 2em;
        font-weight: 600;
    }
    
    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-top: 10px;
        font-size: 0.9em;
    }
    
    .breadcrumb-item a {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
    }
    
    .breadcrumb-item.active {
        color: white;
    }
    
    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    .bg-gradient-info {
        background: linear-gradient(135deg, #36b9cc 0%, #258391 100%) !important;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #176B87 0%, #14546c 100%) !important;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(23, 107, 135, 0.05);
        cursor: pointer;
    }
    
    .table-active {
        background-color: rgba(28, 200, 138, 0.15) !important;
    }
    
    .card {
        border: none;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
        animation: fadeIn 0.5s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .card-header {
        font-weight: 600;
        border-bottom: none;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #176B87;
        box-shadow: 0 0 0 0.2rem rgba(23, 107, 135, 0.25);
    }
</style>
<?php endif; ?>

    <?php if($msg): ?>
        <div class="alert alert-<?= (strpos($msg, 'Error') !== false) ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
            <i class="fa <?= (strpos($msg, 'Error') !== false) ? 'fa-exclamation-circle' : 'fa-check-circle' ?>"></i>
            <?= str_replace('**', '<strong>', str_replace('**', '</strong>', $msg)) ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (!$is_driver): ?>
    <!-- Page Header -->
    <div class="page-header">
        <h2><i class="fa fa-sync-alt"></i> Update Booking Status</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="admin_view_requests.php">Bookings</a></li>
                <li class="breadcrumb-item active">Update Status</li>
            </ol>
        </nav>
    </div>
    <?php endif; ?>

    <!-- Search and Filter Box -->
    <div class="card mb-4">
        <div class="card-header bg-gradient-info text-white">
            <i class="fa fa-filter"></i> Search & Filter Bookings
        </div>
        <div class="card-body">
            <form method="get" class="form-row align-items-end">
                <!-- Search by Vehicle Number -->
                <div class="col-md-5 mb-2">
                    <label for="search" class="font-weight-bold"><i class="fa fa-search"></i> Vehicle Number</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-car"></i></span>
                        </div>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Search by Vehicle Number..." value="<?= htmlspecialchars($search_number) ?>">
                    </div>
                </div>
                
                <!-- Filter by Status -->
                <div class="col-md-4 mb-2">
                    <label for="filter_status" class="font-weight-bold"><i class="fa fa-flag"></i> Filter by Status</label>
                    <select name="filter_status" id="filter_status" class="form-control">
                        <option value="">All Statuses</option>
                        <?php foreach ($admin_status_options as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= $filter_status === $value ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Action Buttons -->
                <div class="col-md-3 mb-2">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-filter"></i> Apply Filter</button>
                    <?php if ($search_number || $filter_status): ?>
                        <a href="update_status.php" class="btn btn-secondary btn-block mt-2"><i class="fa fa-times"></i> Clear All</a>
                    <?php endif; ?>
                </div>
            </form>
            
            <!-- Active Filters Display -->
            <?php if ($search_number || $filter_status): ?>
            <div class="mt-3 pt-3" style="border-top: 1px solid #dee2e6;">
                <small class="text-muted"><strong>Active Filters:</strong></small>
                <?php if ($search_number): ?>
                    <span class="badge badge-info ml-2">
                        Vehicle: <?= htmlspecialchars($search_number) ?>
                        <a href="?filter_status=<?= urlencode($filter_status) ?>" class="text-white ml-1" title="Remove">&times;</a>
                    </span>
                <?php endif; ?>
                <?php if ($filter_status): ?>
                    <span class="badge badge-primary ml-2">
                        Status: <?= htmlspecialchars($filter_status) ?>
                        <a href="?search=<?= urlencode($search_number) ?>" class="text-white ml-1" title="Remove">&times;</a>
                    </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Update Status Form -->
    <div class="card">
        <div class="card-header bg-gradient-primary text-white">
            <i class="fa fa-list"></i> Select Booking to Update
            <?php if ($is_driver): ?>
                <span class="badge badge-warning float-right">Driver Mode</span>
            <?php else: ?>
                <span class="badge badge-info float-right">Admin Mode</span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="post" id="updateForm">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th width="60" class="text-center">Select</th>
                                <th width="100" class="text-center">Booking ID</th>
                                <th>Vehicle</th>
                                <th width="150">Vehicle Number</th>
                                <th width="150" class="text-center">Current Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if($bookings_result->num_rows > 0): ?>
                            <?php while($row = $bookings_result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center">
                                    <input type="radio" name="booking_id" value="<?= (int)$row['booking_id'] ?>" required style="width: 20px; height: 20px; cursor: pointer;">
                                </td>
                                <td class="text-center font-weight-bold">#<?= (int)$row['booking_id'] ?></td>
                                <td><?= htmlspecialchars($row['vehicle_make'] . ' ' . $row['vehicle_model']) ?></td>
                                <td><strong><?= htmlspecialchars($row['vehicle_number']) ?></strong></td>
                                <td class="text-center">
                                    <span style="font-weight: 600;">
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    <i class="fa fa-inbox fa-2x mb-2"></i>
                                    <p>No bookings found
                                    <?php 
                                        if ($search_number && $filter_status) {
                                            echo ' matching vehicle "' . htmlspecialchars($search_number) . '" with status "' . htmlspecialchars($filter_status) . '"';
                                        } elseif ($search_number) {
                                            echo ' matching vehicle "' . htmlspecialchars($search_number) . '"';
                                        } elseif ($filter_status) {
                                            echo ' with status "' . htmlspecialchars($filter_status) . '"';
                                        }
                                    ?>.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($bookings_result->num_rows > 0): ?>
                <hr>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="status" class="font-weight-bold">
                                <i class="fa fa-flag"></i> Select New Status
                                <?php if ($is_driver): ?>
                                    <small class="text-muted">(Driver can only set: Picked Up, Delivered)</small>
                                <?php endif; ?>
                            </label>
                            <select name="status" id="status" class="form-control form-control-lg" required>
                                <option value="">-- Choose Status --</option>
                                <?php foreach ($status_options as $value => $label): ?>
                                    <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-lg btn-primary btn-block" style="background: linear-gradient(135deg, #176B87 0%, #1cc88a 100%); border: none;">
                            <i class="fa fa-save"></i> Update Status
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

<?php if ($is_driver): ?>
    </div> <!-- Close driver-container -->
</body>
</html>
<?php else: ?>
    <script>
        // Highlight selected row
        document.querySelectorAll('input[name="booking_id"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('tbody tr').forEach(tr => tr.classList.remove('table-active'));
                this.closest('tr').classList.add('table-active');
            });
        });
    </script>
<?php 
    include("includes/admin_footer.php");
endif;

$conn->close();
?>