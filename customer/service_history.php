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

// Fetch bookings with service and vehicle details
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE customer_id = ? ORDER BY booking_date DESC, booking_time DESC");
    $stmt->execute([$customer_id]);
    $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    $bookings = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service History - AutoFix</title>
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
        
        .history-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .history-header {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .history-header h1 {
            color: var(--secondary);
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .history-header p {
            color: var(--gray);
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .filter-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .filter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .filter-header h2 {
            color: var(--secondary);
            font-size: 1.5rem;
        }
        
        .filter-controls {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .filter-label {
            font-weight: 600;
            color: var(--dark);
        }
        
        select, input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 16px;
        }
        
        select:focus, input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 74, 23, 0.1);
        }
        
        .btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-align: center; /* Center the text */
        }
        
        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: transparent;
            color: var(--secondary);
            border: 2px solid var(--secondary);
            text-align: center; /* Center the text */
        }
        
        .btn-secondary:hover {
            background: var(--secondary);
            color: white;
        }
        
        .history-table {
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
        
        .search-box {
            display: flex;
            gap: 10px;
        }
        
        .search-input {
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 16px;
            width: 250px;
        }
        
        .search-input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 74, 23, 0.1);
        }
        
        .search-btn {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            padding: 12px 20px;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .search-btn:hover {
            background: var(--primary-dark);
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
        
        .service-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
        }
        
        .service-periodic {
            background-color: #17a2b8;
            color: white;
        }
        
        .service-denting {
            background-color: #ffc107;
            color: #000;
        }
        
        .service-major {
            background-color: var(--primary);
            color: white;
        }
        
        .service-detailing {
            background-color: #28a745;
            color: white;
        }
        
        .service-ceramic {
            background-color: #6f42c1;
            color: white;
        }
        
        .service-graphine {
            background-color: #fd7e14;
            color: white;
        }
        
        .service-waxing {
            background-color: #20c997;
            color: white;
        }
        
        .service-full {
            background-color: #e83e8c;
            color: white;
        }
        
        .service-washing {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-view {
            background: var(--secondary);
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            text-align: center; /* Center the text */
        }
        
        .btn-view:hover {
            background: #1a2530;
            transform: translateY(-2px);
        }
        
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .pagination {
            display: flex;
            gap: 5px;
        }
        
        .pagination a {
            display: inline-block;
            padding: 8px 15px;
            background: white;
            border: 1px solid #ddd;
            text-decoration: none;
            color: var(--dark);
            border-radius: var(--border-radius);
        }
        
        .pagination a:hover, .pagination a.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        @media (max-width: 768px) {
            .history-header {
                padding: 20px;
            }
            
            .history-header h1 {
                font-size: 2rem;
            }
            
            .filter-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-controls {
                width: 100%;
            }
            
            .table-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-box {
                width: 100%;
            }
            
            .search-input {
                width: 100%;
            }
            
            th, td {
                padding: 10px 8px;
                font-size: 14px;
            }
            
            .service-badge {
                font-size: 12px;
                padding: 4px 8px;
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
        <div class="history-container">
            <div class="history-header">
                <h1>Service History</h1>
                <p>View your complete vehicle service records and maintenance history</p>
            </div>
            
            <div class="filter-section">
                <div class="filter-header">
                    <h2>Filter Services</h2>
                    <div class="filter-controls">
                        <div class="filter-group">
                            <span class="filter-label">From:</span>
                            <input type="date" id="from-date">
                        </div>
                        <div class="filter-group">
                            <span class="filter-label">To:</span>
                            <input type="date" id="to-date">
                        </div>
                        <div class="filter-group">
                            <span class="filter-label">Service:</span>
                            <select id="service-type">
                                <option value="">All Services</option>
                                <option value="Periodic Service">Periodic Services</option>
                                <option value="Full Service">Full Service</option>
                                <option value="Waxing">Waxing</option>
                                <option value="Ceramic Coating">Ceramic Coating</option>
                                <option value="Graphine Coating">Graphine Coating</option>
                            </select>
                        </div>
                        <button class="btn">Apply Filters</button>
                    </div>
                </div>
            </div>
            
            <div class="history-table">
                <div class="table-header">
                    <h2>Service Records</h2>
                    <div class="search-box">
                        <input type="text" class="search-input" placeholder="Search by vehicle or service...">
                        <button class="search-btn">Search</button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <?php if (!empty($bookings)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Service Date</th>
                                    <th>Service Type</th>
                                    <th>Vehicle</th>
                                    <th>Vehicle Number</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td>#<?php echo $booking['booking_id']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                        <td>
                                            <span class="service-badge service-<?php echo strtolower(str_replace(' ', '-', $booking['service_type'])); ?>">
                                                <?php echo htmlspecialchars($booking['service_type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($booking['vehicle_make'] . ' ' . $booking['vehicle_model']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['vehicle_number']); ?></td>
                                        <td>
                                            <div class="actions">
                                                <button class="btn-view">View Details</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No service history found.</p>
                    <?php endif; ?>
                </div>
                
                <div class="table-footer">
                    <div>
                        Showing 1 to <?php echo count($bookings); ?> of <?php echo count($bookings); ?> entries
                    </div>
                    <div class="pagination">
                        <a href="#" class="active">1</a>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="bookingfor.php" class="btn">Book New Service</a>
                <a href="profile.php" class="btn btn-secondary" style="margin-left: 15px;">Back to Profile</a>
            </div>
        </div>
    </main>
    
    <script>
        // Set default dates for filter
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const thirtyDaysAgo = new Date();
            thirtyDaysAgo.setDate(today.getDate() - 30);
            
            document.getElementById('to-date').valueAsDate = today;
            document.getElementById('from-date').valueAsDate = thirtyDaysAgo;
        });
    </script>
</body>
</html>