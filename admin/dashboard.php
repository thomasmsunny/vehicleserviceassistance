<?php
require('../config/autoload.php');

// Set page title
$page_title = "Admin Dashboard";

// Include header and status styles
include("includes/admin_header.php");

// Initialize database access
$dao = new DataAccess();

// Get all data first using the working approach
$all_services = $dao->getData('*', 'services', '1');
$all_services = is_array($all_services) ? $all_services : [];
$total_services_count = count($all_services);

$all_drivers = $dao->getData('*', 'drivermanage', '1');
$all_drivers = is_array($all_drivers) ? $all_drivers : [];
$total_drivers_count = count($all_drivers);

$all_bookings = $dao->getData('*', 'bookings', '1');
$all_bookings = is_array($all_bookings) ? $all_bookings : [];
$total_bookings_count = count($all_bookings);

$pending_bookings_data = $dao->getData('*', 'bookings', "status='pending' OR status='Pending'");
$pending_bookings_data = is_array($pending_bookings_data) ? $pending_bookings_data : [];
$pending_bookings_count = count($pending_bookings_data);

// Get system status information
$system_status = 'Operational';
$system_status_class = 'success';
$database_status = 'Connected';
$database_status_class = 'success';

// Check database connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if ($conn->connect_error) {
    $database_status = 'Connection Failed';
    $database_status_class = 'danger';
    $system_status = 'Degraded';
    $system_status_class = 'warning';
}
$conn->close();

// Get additional system info
$total_customers_result = $dao->getData('*', 'customerreg', '1');
$total_customers_result = is_array($total_customers_result) ? $total_customers_result : [];
$total_customers = count($total_customers_result);

$active_drivers_result = $dao->getData('*', 'drivermanage', "status='available'");
$active_drivers_result = is_array($active_drivers_result) ? $active_drivers_result : [];
$active_drivers = count($active_drivers_result);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h1 class="h2"><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <div class="btn-group mr-2">
      <button type="button" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-calendar"></i> <?= date('F d, Y') ?>
      </button>
    </div>
  </div>
</div>

<!-- Enhanced Statistics Cards with Icons and Animations -->
<div class="row">
    <!-- Total Services -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2 card-hover">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Services</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_services_count ?? 0 ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-tools fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Drivers -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2 card-hover">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Drivers</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_drivers_count ?? 0 ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Bookings -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2 card-hover">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Bookings</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_bookings_count ?? 0 ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Bookings -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2 card-hover">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Bookings</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pending_bookings_count ?? 0 ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Quick Actions Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3" style="background: linear-gradient(135deg, #176B87 0%, #14546c 100%); color: white;">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-bolt"></i> Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="viewservices1.php" class="btn btn-primary btn-block btn-lg rounded-pill shadow-sm">
                            <i class="fas fa-tools mr-2"></i>View Services
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="driverviews1.php" class="btn btn-success btn-block btn-lg rounded-pill shadow-sm">
                            <i class="fas fa-users mr-2"></i>View Drivers
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="admin_view_requests.php" class="btn btn-info btn-block btn-lg rounded-pill shadow-sm">
                            <i class="fas fa-clipboard-list mr-2"></i>View Bookings
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="reports.php" class="btn btn-warning btn-block btn-lg rounded-pill shadow-sm">
                            <i class="fas fa-chart-bar mr-2"></i>Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Recent Activity Section -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3" style="background: linear-gradient(135deg, #176B87 0%, #14546c 100%); color: white;">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-history"></i> Recent Activity</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <p>Welcome to the Vehicle Service Management System. Here you can manage services, drivers, and bookings.</p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><i class="fas fa-check-circle text-success mr-2"></i> Add or manage services offered</li>
                            <li class="list-group-item"><i class="fas fa-check-circle text-success mr-2"></i> Assign and manage drivers</li>
                            <li class="list-group-item"><i class="fas fa-check-circle text-success mr-2"></i> View and update booking requests</li>
                            <li class="list-group-item"><i class="fas fa-check-circle text-success mr-2"></i> Generate reports and analytics</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3" style="background: linear-gradient(135deg, #176B87 0%, #14546c 100%); color: white;">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-server"></i> System Status</h6>
            </div>
            <div class="card-body">
                <!-- Clean System Status Display -->
                <div class="text-center mb-4">
                    <i class="fas fa-heartbeat fa-3x text-<?= $system_status_class ?> mb-3"></i>
                    <h5>System is <span class="text-<?= $system_status_class ?>"><?= $system_status ?></span></h5>
                </div>
                
                <!-- Status Details -->
                <div class="status-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-database text-muted mr-2"></i> Database</span>
                        <span class="badge badge-<?= $database_status_class ?>"><?= $database_status ?></span>
                    </div>
                </div>
                
                <div class="status-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-users text-muted mr-2"></i> Active Drivers</span>
                        <span class="badge badge-info"><?= $active_drivers ?? 0 ?></span>
                    </div>
                </div>
                
                <div class="status-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-user-friends text-muted mr-2"></i> Customers</span>
                        <span class="badge badge-primary"><?= $total_customers ?? 0 ?></span>
                    </div>
                </div>
                
                <!-- Additional Info -->
                <hr>
                <div class="small text-muted">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Admin:</span>
                        <span><?= $_SESSION['admin_name'] ?? 'Unknown' ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Last Login:</span>
                        <span><?= date('M d, Y H:i') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .border-left-primary {
        border-left: 4px solid #176B87 !important;
    }
    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }
    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }
    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }
    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,.08);
        margin-bottom: 20px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,.15) !important;
    }
    .card-header {
        border-radius: 10px 10px 0 0 !important;
        padding: 15px 20px;
    }
    .rounded-pill {
        border-radius: 50rem !important;
    }
    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1.1rem;
    }
    .text-gray-800 {
        color: #5a5c69 !important;
    }
    .text-gray-300 {
        color: #dddfeb !important;
    }
    .font-weight-bold {
        font-weight: 700 !important;
    }
    .text-xs {
        font-size: .75rem;
    }
    .status-item {
        padding: 10px;
        border-radius: 5px;
        background-color: #f8f9fc;
        transition: background-color 0.2s;
    }
    .status-item:hover {
        background-color: #eef2f7;
    }
</style>

<?php include("includes/admin_footer.php"); ?>