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
$booking_id = $_GET['booking_id'] ?? null;

// Validate booking ID
if (!$booking_id || !is_numeric($booking_id)) {
    header("Location: service_status.php");
    exit();
}

// Fetch booking and payment details
try {
    $pdo = getDBConnection();
    
    // Verify this booking belongs to the customer and has been paid
    $stmt = $pdo->prepare("SELECT b.*, p.*, q.grand_total as quote_amount, q.quote_date, q.admin_name 
                          FROM bookings b 
                          LEFT JOIN payments p ON b.booking_id = p.booking_id 
                          LEFT JOIN quotations q ON b.booking_id = q.booking_id 
                          WHERE b.booking_id = ? AND b.customer_id = ? AND b.status = 'Payment Done'");
    $stmt->execute([$booking_id, $customer_id]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        header("Location: service_status.php");
        exit();
    }
} catch (PDOException $e) {
    header("Location: service_status.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Details - AutoFix</title>
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
        
        .bill-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .bill-header {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .bill-header h1 {
            color: var(--secondary);
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            margin: 15px 0;
        }
        
        .status-payment-done {
            background-color: var(--success);
            color: white;
        }
        
        .bill-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .detail-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
        }
        
        .detail-card h2 {
            color: var(--secondary);
            font-size: 1.5rem;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light);
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--dark);
        }
        
        .detail-value {
            text-align: right;
            font-weight: 500;
        }
        
        .amount-display {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            text-align: center;
            margin: 20px 0;
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
            text-decoration: none;
            font-size: 16px;
            margin: 5px;
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
        
        .btn-print {
            background: var(--info);
            text-align: center;
        }
        
        .btn-print:hover {
            background: #138496;
        }
        
        .actions {
            text-align: center;
            margin: 30px 0;
        }
        
        @media (max-width: 768px) {
            .bill-header {
                padding: 20px;
            }
            
            .bill-header h1 {
                font-size: 2rem;
            }
            
            .bill-details {
                grid-template-columns: 1fr;
            }
            
            .detail-row {
                flex-direction: column;
                gap: 5px;
            }
            
            .detail-value {
                text-align: left;
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
        <div class="bill-container">
            <div class="bill-header">
                <h1>Bill Details</h1>
                <div class="status-badge status-payment-done">
                    Payment Done
                </div>
                <div class="amount-display">₹<?php echo number_format($booking['amount_paid'] ?? $booking['quote_amount'], 2); ?></div>
            </div>
            
            <div class="bill-details">
                <div class="detail-card">
                    <h2>Payment Information</h2>
                    <div class="detail-row">
                        <div class="detail-label">Transaction ID:</div>
                        <div class="detail-value">#TXN<?php echo $booking['payment_id'] ?? 'N/A'; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Payment Date:</div>
                        <div class="detail-value"><?php echo date('M d, Y H:i:s', strtotime($booking['payment_date'] ?? $booking['quote_date'])); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Payment Method:</div>
                        <div class="detail-value">
                            <?php 
                            $method = ucfirst($booking['payment_method'] ?? 'N/A');
                            switch($booking['payment_method'] ?? '') {
                                case 'card':
                                    echo '💳 Credit/Debit Card';
                                    break;
                                case 'upi':
                                    echo '📱 UPI';
                                    break;
                                case 'netbanking':
                                    echo '🏦 Net Banking';
                                    break;
                                default:
                                    echo $method;
                            }
                            ?>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Status:</div>
                        <div class="detail-value">Payment Done</div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h2>Booking Information</h2>
                    <div class="detail-row">
                        <div class="detail-label">Booking ID:</div>
                        <div class="detail-value">#<?php echo $booking['booking_id']; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Booking Date:</div>
                        <div class="detail-value"><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Service Type:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($booking['service_type']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Current Status:</div>
                        <div class="detail-value"><?php echo ucfirst($booking['status']); ?></div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h2>Vehicle Information</h2>
                    <div class="detail-row">
                        <div class="detail-label">Make:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($booking['vehicle_make']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Model:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($booking['vehicle_model']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Registration:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($booking['vehicle_number']); ?></div>
                    </div>
                </div>
                
                <?php if (!empty($booking['quote_amount'])): ?>
                <div class="detail-card">
                    <h2>Quotation Information</h2>
                    <div class="detail-row">
                        <div class="detail-label">Quote ID:</div>
                        <div class="detail-value">#<?php echo $booking['quote_id'] ?? 'N/A'; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Quote Date:</div>
                        <div class="detail-value"><?php echo date('M d, Y', strtotime($booking['quote_date'])); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Prepared By:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($booking['admin_name']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Quote Amount:</div>
                        <div class="detail-value">₹<?php echo number_format($booking['quote_amount'], 2); ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="actions">
                <button class="btn btn-print" onclick="window.print()">Print Bill</button>
                <a href="service_status.php" class="btn btn-secondary">Back to Service Status</a>
            </div>
        </div>
    </main>
    
    <script>
        // Add print styles
        document.addEventListener('DOMContentLoaded', function() {
            const style = document.createElement('style');
            style.innerHTML = `
                @media print {
                    .header, .btn, .actions {
                        display: none;
                    }
                    .main {
                        margin-top: 0;
                        padding: 20px;
                    }
                    .bill-container {
                        box-shadow: none;
                    }
                    .detail-card {
                        box-shadow: none;
                        border: 1px solid #ddd;
                    }
                }
            `;
            document.head.appendChild(style);
        });
    </script>
</body>
</html>