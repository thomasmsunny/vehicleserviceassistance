<?php
// Don't check session here - let the main file handle it
$driver_name = $_SESSION['driver_name'] ?? 'Driver';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Driver Dashboard' ?> - Vehicle Service</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
        }
        
        /* Sidebar Styles */
        #sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-heading {
            padding: 1rem;
            background: rgba(0,0,0,0.1);
        }
        
        .sidebar-heading h4 {
            color: white;
            margin: 0;
            font-weight: 600;
        }
        
        .nav-item {
            margin: 0.2rem 0;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: #3498db;
        }
        
        .nav-link.active {
            background: rgba(52,152,219,0.2);
            color: white;
            border-left-color: #3498db;
            font-weight: 600;
        }
        
        .nav-link i {
            width: 25px;
            margin-right: 10px;
        }
        
        .sidebar-heading.px-3 {
            color: rgba(255,255,255,0.9);
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.1);
            border-left: 3px solid #3498db;
        }
        
        /* Top Navbar */
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            background: white !important;
        }
        
        .navbar-brand {
            font-weight: 600;
            color: #2c3e50 !important;
        }
        
        /* Main Content */
        #content {
            min-height: 100vh;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .border-left-primary { border-left: 4px solid #4e73df !important; }
        .border-left-success { border-left: 4px solid #1cc88a !important; }
        .border-left-info { border-left: 4px solid #36b9cc !important; }
        .border-left-warning { border-left: 4px solid #f6c23e !important; }
        
        /* User Dropdown */
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            #sidebar {
                margin-left: -250px;
            }
            
            #sidebar.active {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
            <div class="sidebar-heading">
                <h4><i class="fas fa-car"></i> Driver Portal</h4>
            </div>
            
            <div class="sidebar-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
                            <span>PICKUPS</span>
                        </h6>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'assignedpickup.php' ? 'active' : '' ?>" href="assignedpickup.php">
                            <i class="fas fa-truck-pickup"></i> Assigned Pickups
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'current_working.php' ? 'active' : '' ?>" href="current_working.php">
                            <i class="fas fa-tools"></i> Current Work
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
                            <span>ACCOUNT</span>
                        </h6>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'driverprofile.php' ? 'active' : '' ?>" href="driverprofile.php">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4" id="content">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#sidebar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <a class="navbar-brand" href="dashboard.php">
                    <i class="fas fa-car-side"></i> Vehicle Service - Driver
                </a>
                
                <div class="ml-auto">
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-user-circle fa-lg"></i> <?= htmlspecialchars($driver_name) ?>
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

            <!-- Page Content -->
            <div class="container-fluid">
