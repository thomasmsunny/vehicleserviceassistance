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

// Fetch customer details
$customer = getCustomerById($customer_id);

if (!$customer) {
    session_destroy();
    header("Location: customerlogin.php");
    exit();
}

$customer_name = trim($customer['firstname'] . ' ' . $customer['lastname']);
$email = $customer['email'];
$phone = $customer['phone'];
$member_since = date('M d, Y', strtotime($customer['created_at']));

// Fetch booking statistics
$stats = getBookingStatusCounts($customer_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - AutoFix</title>
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
        
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .profile-header {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), #ff7b54);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: bold;
            margin: 0 auto 20px;
            border: 5px solid rgba(255, 74, 23, 0.1);
        }
        
        .profile-header h1 {
            color: var(--secondary);
            font-size: 2.2rem;
            margin-bottom: 10px;
        }
        
        .profile-header p {
            color: var(--gray);
            font-size: 1.1rem;
        }
        
        .profile-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }
        
        @media (max-width: 992px) {
            .profile-content {
                grid-template-columns: 1fr;
            }
        }
        
        .profile-sidebar {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
        }
        
        .profile-sidebar h2 {
            color: var(--secondary);
            font-size: 1.5rem;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(0,0,0,0.05);
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 15px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            text-decoration: none;
            color: var(--dark);
            border-radius: var(--border-radius);
            transition: var(--transition);
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255, 74, 23, 0.1);
            color: var(--primary);
        }
        
        .sidebar-menu i {
            margin-right: 12px;
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }
        
        .profile-main {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
        }
        
        .profile-main h2 {
            color: var(--secondary);
            font-size: 1.8rem;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(0,0,0,0.05);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .info-card {
            background: var(--light);
            border-radius: var(--border-radius);
            padding: 25px;
            transition: var(--transition);
        }
        
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        .info-card .icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 15px;
        }
        
        .info-card h3 {
            color: var(--secondary);
            font-size: 1.3rem;
            margin-bottom: 10px;
        }
        
        .info-card p {
            color: var(--gray);
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--primary), #ff7b54);
            color: white;
            border-radius: var(--border-radius);
            padding: 25px;
            text-align: center;
        }
        
        .stat-card .number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-card .label {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            text-decoration: none;
        }
        
        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 74, 23, 0.4);
        }
        
        .btn-secondary {
            background: transparent;
            color: var(--secondary);
            border: 2px solid var(--secondary);
        }
        
        .btn-secondary:hover {
            background: var(--secondary);
            color: white;
        }
        
        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }
        
        @media (max-width: 768px) {
            .profile-header {
                padding: 20px;
            }
            
            .avatar {
                width: 100px;
                height: 100px;
                font-size: 36px;
            }
            
            .profile-header h1 {
                font-size: 1.8rem;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .actions .btn {
                width: 100%;
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
            
            <a href="logout.php" class="cta-button">Logout</a>
        </div>
    </header>

    <main class="main">
        <div class="profile-container">
            <div class="profile-header">
                <div class="avatar">
                    <?php echo strtoupper(substr($customer_name, 0, 1)); ?>
                </div>
                <h1><?php echo htmlspecialchars($customer_name); ?></h1>
                <p>Customer since: <?php echo $member_since; ?></p>
            </div>
            
            <div class="profile-content">
                <div class="profile-sidebar">
                    <h2>My Account</h2>
                    <ul class="sidebar-menu">
                        <li><a href="profile.php" class="active"><i>👤</i> My Profile</a></li>
                        <li><a href="editprofile.php"><i>✏️</i> Edit Profile</a></li>
                        <li><a href="bookingfor.php"><i>📅</i> Book Service</a></li>
                        <li><a href="service_status.php"><i>📊</i> Service Status</a></li>
                        <li><a href="transaction_history.php"><i>💳</i> Transactions</a></li>
                        <li><a href="service_history.php"><i>📋</i> Service History</a></li>
                        <li><a href="feedback.php"><i>💬</i> Feedback</a></li>
                    </ul>
                </div>
                
                <div class="profile-main">
                    <h2>Profile Overview</h2>
                    
                    <div class="info-grid">
                        <div class="info-card">
                            <div class="icon">📧</div>
                            <h3>Contact Information</h3>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($phone); ?></p>
                        </div>
                        
                        <div class="info-card">
                            <div class="icon">📍</div>
                            <h3>Address</h3>
                            <p>123 Main Street</p>
                            <p>City, State 12345</p>
                            <p>Country</p>
                        </div>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="number"><?php echo $stats['pending_count'] ?? 0; ?></div>
                            <div class="label">Pending</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="number"><?php echo $stats['progress_count'] ?? 0; ?></div>
                            <div class="label">In Progress</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="number"><?php echo $stats['completed_count'] ?? 0; ?></div>
                            <div class="label">Completed</div>
                        </div>
                    </div>
                    
                    <div class="actions">
                        <a href="editprofile.php" class="btn">Edit Profile</a>
                        <a href="bookingfor.php" class="btn">Book Service</a>
                        <a href="service_status.php" class="btn btn-outline">Check Status</a>
                        <a href="feedback.php" class="btn btn-secondary">Give Feedback</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>