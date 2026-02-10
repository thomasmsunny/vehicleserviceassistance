<?php
// Set page title for the header
$page_title = "Driver Dashboard";

// Check if driver is logged in
session_start();
if (!isset($_SESSION['driver_id'])) {
    header("Location: driverlogin.php");
    exit();
}

$driver_id = $_SESSION['driver_id'];
$driver_name = $_SESSION['driver_name'] ?? $_SESSION['drivername'] ?? 'Driver';

require('../config/autoload.php');

// Database connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get dashboard statistics
$assigned_pickups = 0;
$current_work = 0;
$completed_jobs = 0;
$total_jobs = 0;

try {
    // Get pending pickups count
    $assigned_result = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE driver_id = $driver_id AND status = 'Pending'");
    if ($assigned_result && $row = $assigned_result->fetch_assoc()) {
        $assigned_pickups = $row['count'];
    }

    // Get active jobs count
    $inprogress_result = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE driver_id = $driver_id AND status IN ('In Progress', 'Picked Up')");
    if ($inprogress_result && $row = $inprogress_result->fetch_assoc()) {
        $current_work = $row['count'];
    }

    // Get completed jobs count
    $completed_result = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE driver_id = $driver_id AND status IN ('Complete', 'Delivered')");
    if ($completed_result && $row = $completed_result->fetch_assoc()) {
        $completed_jobs = $row['count'];
    }

    // Get total jobs count
    $total_result = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE driver_id = $driver_id");
    if ($total_result && $row = $total_result->fetch_assoc()) {
        $total_jobs = $row['count'];
    }
} catch (Exception $e) {
    // Silently handle errors
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?= isset($page_title) ? $page_title : 'Vehicle Service Driver' ?></title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f4f6f9;
    }
    .navbar {
      background: linear-gradient(135deg, #176B87 0%, #14546c 100%);
      box-shadow: 0 2px 4px rgba(0,0,0,.08);
    }
    .navbar-brand {
      color: #fff !important;
      font-weight: bold;
      font-size: 1.5rem;
    }
    .sidebar {
      min-height: calc(100vh - 56px);
      background: #2c3e50;
      box-shadow: 2px 0 5px rgba(0,0,0,.1);
    }
    .sidebar .nav-link {
      color: #ecf0f1;
      padding: 15px 20px;
      border-left: 3px solid transparent;
      transition: all 0.3s;
    }
    .sidebar .nav-link:hover {
      background-color: #34495e;
      border-left-color: #176B87;
    }
    .sidebar .nav-link.active {
      background-color: #176B87;
      border-left-color: #fff;
    }
    .sidebar .nav-link i {
      margin-right: 10px;
      width: 20px;
    }
    .main-content {
      padding: 30px;
    }
    .card {
      border: none;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(0,0,0,.08);
      margin-bottom: 30px;
    }
    .card-header {
      background: linear-gradient(135deg, #176B87 0%, #14546c 100%);
      color: white;
      border-radius: 10px 10px 0 0 !important;
      padding: 15px 20px;
    }
    .btn-primary {
      background: #176B87;
      border-color: #176B87;
    }
    .btn-primary:hover {
      background: #14546c;
      border-color: #14546c;
    }
    .stat-card {
      border-left: 4px solid #176B87;
    }
    .dropdown-menu {
      border: none;
      box-shadow: 0 0 20px rgba(0,0,0,.1);
    }
    .navbar .dropdown-menu {
      right: 0;
      left: auto;
    }
    
    /* Dashboard specific styles */
    .page-header {
      background: linear-gradient(135deg, #176B87 0%, #14546c 100%);
      color: white;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 30px;
      box-shadow: 0 4px 12px rgba(23, 107, 135, 0.3);
    }
    
    .stat-icon {
      font-size: 2.5em;
      margin-bottom: 10px;
      color: #176B87;
    }
    
    .stat-value {
      font-size: 2em;
      font-weight: bold;
      color: #2c3e50;
      margin: 10px 0;
    }
    
    .stat-label {
      color: #7f8c8d;
      font-size: 0.9em;
      text-transform: uppercase;
      font-weight: 600;
    }
  </style>
</head>
<body>
  <!-- Top Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <a class="navbar-brand" href="dashboard.php">
      <i class="fas fa-car"></i> Vehicle Service Driver
    </a>
    
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" data-toggle="dropdown">
            <i class="fas fa-user-circle"></i> <?= htmlspecialchars($driver_name) ?>
          </a>
          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
            <a class="dropdown-item" href="driverprofile.php">
              <i class="fas fa-user"></i> Profile
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="logout.php">
              <i class="fas fa-sign-out-alt"></i> Logout
            </a>
          </div>
        </li>
      </ul>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <nav class="col-md-2 d-none d-md-block sidebar">
        <div class="sidebar-sticky">
          <ul class="nav flex-column mt-3">
            <li class="nav-item">
              <a class="nav-link active" href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
              </a>
            </li>
            
            <li class="nav-item">
              <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
                <span>BOOKINGS</span>
              </h6>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="assignedpickup.php">
                <i class="fas fa-truck-pickup"></i> Pending Pickups
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="current_working.php">
                <i class="fas fa-tools"></i> Current Work
              </a>
            </li>
            
            <li class="nav-item">
              <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
                <span>ACCOUNT</span>
              </h6>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="driverprofile.php">
                <i class="fas fa-user"></i> My Profile
              </a>
            </li>
          </ul>
        </div>
      </nav>

      <!-- Main Content -->
      <main role="main" class="col-md-10 ml-sm-auto main-content">
        <!-- Page Header -->
        <div class="page-header">
          <h2><i class="fas fa-tachometer-alt"></i> Driver Dashboard</h2>
          <p class="mb-0">Welcome back, <?= htmlspecialchars($driver_name) ?>!</p>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
          <!-- Pending Pickups -->
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card">
              <div class="card-body text-center">
                <div class="stat-icon">
                  <i class="fas fa-truck-pickup"></i>
                </div>
                <div class="stat-value"><?= $assigned_pickups ?></div>
                <div class="stat-label">Pending Pickups</div>
              </div>
            </div>
          </div>

          <!-- Active Jobs -->
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card">
              <div class="card-body text-center">
                <div class="stat-icon">
                  <i class="fas fa-tools"></i>
                </div>
                <div class="stat-value"><?= $current_work ?></div>
                <div class="stat-label">Active Jobs</div>
              </div>
            </div>
          </div>

          <!-- Completed Services -->
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card">
              <div class="card-body text-center">
                <div class="stat-icon">
                  <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value"><?= $completed_jobs ?></div>
                <div class="stat-label">Completed Services</div>
              </div>
            </div>
          </div>

          <!-- Total Jobs -->
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card">
              <div class="card-body text-center">
                <div class="stat-icon">
                  <i class="fas fa-list"></i>
                </div>
                <div class="stat-value"><?= $total_jobs ?></div>
                <div class="stat-label">Total Jobs</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <a href="assignedpickup.php" class="btn btn-primary btn-lg btn-block">
                      <i class="fas fa-truck-pickup"></i> View Pending Pickups
                    </a>
                  </div>
                  <div class="col-md-4 mb-3">
                    <a href="current_working.php" class="btn btn-primary btn-lg btn-block">
                      <i class="fas fa-tools"></i> Current Work
                    </a>
                  </div>
                  <div class="col-md-4 mb-3">
                    <a href="driverprofile.php" class="btn btn-primary btn-lg btn-block">
                      <i class="fas fa-user"></i> My Profile
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Footer -->
  <footer class="text-center py-3 mt-5" style="background-color: #2c3e50; color: white;">
    <p class="mb-0">&copy; <?= date('Y') ?> Vehicle Service Management System. All Rights Reserved.</p>
  </footer>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>