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
$search_term = $_GET['search'] ?? '';

// Fetch payments with booking details
try {
    $pdo = getDBConnection();
    
    if (!empty($search_term)) {
        // Search query
        $stmt = $pdo->prepare("SELECT p.*, b.vehicle_make, b.vehicle_model, b.vehicle_number, b.service_type 
                              FROM payments p 
                              JOIN bookings b ON p.booking_id = b.booking_id 
                              WHERE p.customer_id = ? 
                              AND (p.payment_id LIKE ? 
                                   OR b.booking_id LIKE ? 
                                   OR b.vehicle_make LIKE ? 
                                   OR b.vehicle_model LIKE ? 
                                   OR b.vehicle_number LIKE ? 
                                   OR b.service_type LIKE ? 
                                   OR p.payment_method LIKE ? 
                                   OR p.payment_status LIKE ?)
                              ORDER BY p.payment_date DESC");
        $search_param = '%' . $search_term . '%';
        $stmt->execute([$customer_id, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param]);
    } else {
        // Regular query without search
        $stmt = $pdo->prepare("SELECT p.*, b.vehicle_make, b.vehicle_model, b.vehicle_number, b.service_type 
                              FROM payments p 
                              JOIN bookings b ON p.booking_id = b.booking_id 
                              WHERE p.customer_id = ? 
                              ORDER BY p.payment_date DESC");
        $stmt->execute([$customer_id]);
    }
    
    $payments = $stmt->fetchAll();
} catch (PDOException $e) {
    $payments = [];
}

// Calculate summary
$total_transactions = count($payments);
$total_spent = 0;
$successful_payments = 0;
$failed_payments = 0;

foreach ($payments as $payment) {
    if ($payment['payment_status'] == 'Success') {
        $total_spent += $payment['amount_paid'];
        $successful_payments++;
    } else {
        $failed_payments++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - AutoFix</title>
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
            --danger: #dc3545;
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
        
        .transaction-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .transaction-header {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .transaction-header h1 {
            color: var(--secondary);
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .transaction-header p {
            color: var(--gray);
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .summary-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            text-align: center;
            transition: var(--transition);
        }
        
        .summary-card:hover {
            transform: translateY(-5px);
        }
        
        .summary-card.total {
            border-top: 4px solid var(--primary);
        }
        
        .summary-card.spent {
            border-top: 4px solid var(--success);
        }
        
        .summary-card.success {
            border-top: 4px solid var(--info);
        }
        
        .summary-card.failed {
            border-top: 4px solid var(--danger);
        }
        
        .summary-card .label {
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: var(--dark);
        }
        
        .summary-card .amount {
            font-size: 2rem;
            font-weight: 700;
        }
        
        .summary-card.total .amount {
            color: var(--primary);
        }
        
        .summary-card.spent .amount {
            color: var(--success);
        }
        
        .summary-card.success .amount {
            color: var(--info);
        }
        
        .summary-card.failed .amount {
            color: var(--danger);
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
        
        .search-box {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .search-input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 16px;
            min-width: 250px;
        }
        
        .search-input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 74, 23, 0.1);
        }
        
        .search-btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
        }
        
        .search-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
        }
        
        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: transparent;
            color: var(--secondary);
            border: 2px solid var(--secondary);
            text-align: center;
        }
        
        .btn-secondary:hover {
            background: var(--secondary);
            color: white;
        }
        
        .btn-reset {
            background: var(--gray);
            color: white;
            text-align: center;
        }
        
        .btn-reset:hover {
            background: #5a6268;
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
            text-align: center;
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
        
        .amount-positive {
            color: var(--success);
            font-weight: 600;
        }
        
        .amount-negative {
            color: var(--primary);
            font-weight: 600;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            min-width: 100px;
        }
        
        .status-success {
            background-color: var(--success);
            color: white;
        }
        
        .status-pending {
            background-color: var(--warning);
            color: #000;
        }
        
        .status-failed {
            background-color: var(--danger);
            color: white;
        }
        
        .status-refunded {
            background-color: var(--info);
            color: white;
        }
        
        .payment-method {
            display: inline-block;
            padding: 5px 10px;
            background: var(--light);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .table-header h2 {
            color: var(--secondary);
            font-size: 1.5rem;
            margin: 0;
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
        
        .no-transactions {
            text-align: center;
            padding: 40px;
            color: var(--gray);
        }
        
        .no-transactions i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        .no-transactions p {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .transaction-header {
                padding: 20px;
            }
            
            .transaction-header h1 {
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
            
            .status-badge {
                font-size: 12px;
                padding: 4px 8px;
                min-width: 80px;
            }
            
            .summary-section {
                grid-template-columns: 1fr;
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
        <div class="transaction-container">
            <div class="transaction-header">
                <h1>Transaction History</h1>
                <p>View your complete payment records and financial history</p>
            </div>
            
            <div class="summary-section">
                <div class="summary-card total">
                    <div class="label">Total Transactions</div>
                    <div class="amount"><?php echo $total_transactions; ?></div>
                </div>
                
                <div class="summary-card spent">
                    <div class="label">Total Spent</div>
                    <div class="amount">₹<?php echo number_format($total_spent, 2); ?></div>
                </div>
                
                <div class="summary-card success">
                    <div class="label">Successful Payments</div>
                    <div class="amount"><?php echo $successful_payments; ?></div>
                </div>
                
                <div class="summary-card failed">
                    <div class="label">Failed Payments</div>
                    <div class="amount"><?php echo $failed_payments; ?></div>
                </div>
            </div>
            
            <div class="filter-section">
                <div class="filter-header">
                    <h2>Filter Transactions</h2>
                    <div class="filter-controls">
                        <div class="filter-group">
                            <span class="filter-label">From:</span>
                            <input type="date" id="from-date">
                        </div>
                        <div class="filter-group">
                            <span class="filter-label">To:</span>
                            <input type="date" id="to-date">
                        </div>
                        <button class="btn" onclick="applyFilters()">Apply Filters</button>
                        <button class="btn btn-reset" onclick="resetFilters()">Reset</button>
                    </div>
                </div>
            </div>
            
            <div class="transaction-table">
                <div class="table-header">
                    <h2>Payment Records</h2>
                    <div class="search-box">
                        <input type="text" class="search-input" placeholder="Search transactions..." id="search-input" value="<?php echo htmlspecialchars($search_term); ?>">
                        <button class="search-btn" onclick="searchTransactions()">Search</button>
                        <?php if (!empty($search_term)): ?>
                            <a href="?" class="btn btn-reset" style="margin-left: 10px;">Clear</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <?php if (!empty($payments)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>Date & Time</th>
                                    <th>Booking ID</th>
                                    <th>Vehicle</th>
                                    <th>Service</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td>#TXN<?php echo $payment['payment_id']; ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($payment['payment_date'])); ?></td>
                                        <td>#<?php echo $payment['booking_id']; ?></td>
                                        <td><?php echo htmlspecialchars($payment['vehicle_make'] . ' ' . $payment['vehicle_model'] . ' (' . $payment['vehicle_number'] . ')'); ?></td>
                                        <td><?php echo htmlspecialchars($payment['service_type']); ?></td>
                                        <td>
                                            <span class="payment-method">
                                                <?php 
                                                $method = ucfirst($payment['payment_method']);
                                                switch($payment['payment_method']) {
                                                    case 'card':
                                                        echo '💳 ' . $method;
                                                        break;
                                                    case 'upi':
                                                        echo '📱 ' . $method;
                                                        break;
                                                    case 'netbanking':
                                                        echo '🏦 ' . $method;
                                                        break;
                                                    default:
                                                        echo $method;
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($payment['payment_status']); ?>">
                                                <?php echo ucfirst($payment['payment_status']); ?>
                                            </span>
                                        </td>
                                        <td class="amount-negative">₹<?php echo number_format($payment['amount_paid'], 2); ?></td>
                                        <td>
                                            <div class="actions">
                                                <a href="transaction_details.php?id=<?php echo $payment['payment_id']; ?>" class="btn-view">View Details</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-transactions">
                            <div>💳</div>
                            <h3>No Transaction History</h3>
                            <?php if (!empty($search_term)): ?>
                                <p>No transactions found matching "<?php echo htmlspecialchars($search_term); ?>"</p>
                                <p>Try a different search term.</p>
                            <?php else: ?>
                                <p>You haven't made any payments yet.</p>
                            <?php endif; ?>
                            <a href="bookingfor.php" class="btn">Book a Service</a>
                            <?php if (!empty($search_term)): ?>
                                <a href="?" class="btn btn-secondary" style="margin-top: 10px;">View All Transactions</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($payments)): ?>
                    <div class="table-footer">
                        <div>
                            <?php if (!empty($search_term)): ?>
                                Showing <?php echo count($payments); ?> results for "<?php echo htmlspecialchars($search_term); ?>"
                            <?php else: ?>
                                Showing 1 to <?php echo count($payments); ?> of <?php echo count($payments); ?> entries
                            <?php endif; ?>
                        </div>
                        <div class="pagination">
                            <a href="#" class="active">1</a>
                        </div>
                    </div>
                <?php endif; ?>
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
        
        function applyFilters() {
            // In a real implementation, this would filter the transactions
            alert('Filters would be applied in a full implementation');
        }
        
        function resetFilters() {
            document.getElementById('from-date').value = '';
            document.getElementById('to-date').value = '';
            document.getElementById('search-input').value = '';
        }
        
        function searchTransactions() {
            const searchTerm = document.getElementById('search-input').value.trim();
            if (searchTerm !== '') {
                // Redirect to the same page with search parameter
                window.location.href = '?search=' + encodeURIComponent(searchTerm);
            } else {
                // If search term is empty, reload without search parameter
                window.location.href = window.location.pathname;
            }
        }
        
        // Allow search when pressing Enter in the search box
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        searchTransactions();
                    }
                });
            }
        });
    </script>
</body>
</html>