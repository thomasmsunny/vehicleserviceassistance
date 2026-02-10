<?php
// Session check - ensure admin is logged in
if(!isset($_SESSION['admin_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get current page name for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?= isset($page_title) ? $page_title : 'Vehicle Service Admin' ?></title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" rel="stylesheet">
  
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
  </style>
</head>
<body>
  <!-- Top Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <a class="navbar-brand" href="dashboard.php">
      <i class="fas fa-car"></i> Vehicle Service Admin
    </a>
    
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle text-white" href="#" id="notificationDropdown" data-toggle="dropdown">
            <i class="fas fa-bell"></i>
            <span class="badge badge-danger badge-pill">3</span>
          </a>
          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationDropdown">
            <h6 class="dropdown-header">Notifications</h6>
            <a class="dropdown-item" href="admin_view_requests.php">
              <i class="fas fa-exclamation-circle text-warning"></i> New booking requests
            </a>
            <a class="dropdown-item" href="admin_feedback.php">
              <i class="fas fa-comment text-info"></i> New feedback
            </a>
          </div>
        </li>
        
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" data-toggle="dropdown">
            <i class="fas fa-user-circle"></i> <?= $_SESSION['admin_name'] ?>
          </a>
          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
            <a class="dropdown-item" href="admin_profile.php">
              <i class="fas fa-user"></i> Profile
            </a>
            <a class="dropdown-item" href="admin_settings.php">
              <i class="fas fa-cog"></i> Settings
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
              <a class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
              </a>
            </li>
            
            <li class="nav-item">
              <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
                <span>SERVICES</span>
              </h6>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $current_page == 'servicesoffered1.php' ? 'active' : '' ?>" href="servicesoffered1.php">
                <i class="fas fa-plus-circle"></i> Add Service
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $current_page == 'viewservices1.php' ? 'active' : '' ?>" href="viewservices1.php">
                <i class="fas fa-list"></i> View Services
              </a>
            </li>
            
            <li class="nav-item">
              <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
                <span>DRIVERS</span>
              </h6>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $current_page == 'drivermanage1.php' ? 'active' : '' ?>" href="drivermanage1.php">
                <i class="fas fa-user-plus"></i> Add Driver
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $current_page == 'driverviews1.php' ? 'active' : '' ?>" href="driverviews1.php">
                <i class="fas fa-users"></i> View Drivers
              </a>
            </li>
            
            <li class="nav-item">
              <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
                <span>BOOKINGS</span>
              </h6>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $current_page == 'admin_view_requests.php' ? 'active' : '' ?>" href="admin_view_requests.php">
                <i class="fas fa-calendar-check"></i> View Requests
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $current_page == 'update_status.php' ? 'active' : '' ?>" href="update_status.php">
                <i class="fas fa-sync-alt"></i> Update Status
              </a>
            </li>
            
            <li class="nav-item">
              <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
                <span>SERVICE HISTORY</span>
              </h6>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $current_page == 'vehicle_service_history.php' ? 'active' : '' ?>" href="vehicle_service_history.php">
                <i class="fas fa-history"></i> View History
              </a>
            </li>
            
            <li class="nav-item">
              <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
                <span>TRANSACTIONS</span>
              </h6>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $current_page == 'view_transactions.php' ? 'active' : '' ?>" href="view_transactions.php">
                <i class="fas fa-receipt"></i> View Transactions
              </a>
            </li>
            
            <li class="nav-item">
              <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
                <span>FEEDBACK</span>
              </h6>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $current_page == 'admin_feedback.php' ? 'active' : '' ?>" href="admin_feedback.php">
                <i class="fas fa-comments"></i> Customer Feedback
              </a>
            </li>
            
            <li class="nav-item">
              <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
                <span>REPORTS</span>
              </h6>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $current_page == 'reports.php' ? 'active' : '' ?>" href="reports.php">
                <i class="fas fa-chart-bar"></i> Analytics
              </a>
            </li>
            
            <li class="nav-item">
              <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
                <span>MESSAGES</span>
              </h6>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $current_page == 'view_contact_messages.php' ? 'active' : '' ?>" href="view_contact_messages.php">
                <i class="fas fa-envelope"></i> Contact Messages
              </a>
            </li>
          </ul>
        </div>
      </nav>

      <!-- Main Content -->
      <main role="main" class="col-md-10 ml-sm-auto main-content">
