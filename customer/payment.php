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
    $error_message = "Invalid booking ID provided.";
} else {
    // Fetch booking details
    try {
        $pdo = getDBConnection();
        
        // Verify this booking belongs to the customer and has a quote
        $stmt = $pdo->prepare("SELECT b.*, q.grand_total as amount FROM bookings b JOIN quotations q ON b.booking_id = q.booking_id WHERE b.booking_id = ? AND b.customer_id = ? AND b.status = 'Pay Now'");
        $stmt->execute([$booking_id, $customer_id]);
        $booking = $stmt->fetch();
        
        if (!$booking) {
            // Let's check what the actual status is to provide a better error message
            $stmt = $pdo->prepare("SELECT b.*, q.grand_total as amount, b.status as actual_status FROM bookings b JOIN quotations q ON b.booking_id = q.booking_id WHERE b.booking_id = ? AND b.customer_id = ?");
            $stmt->execute([$booking_id, $customer_id]);
            $booking_info = $stmt->fetch();
            
            if (!$booking_info) {
                $error_message = "Booking not found or you don't have permission to access it.";
            } else if ($booking_info['actual_status'] !== 'Pay Now') {
                $error_message = "This booking is not ready for payment. Current status: " . $booking_info['actual_status'];
            } else {
                $error_message = "This booking does not have a quotation. Please contact support.";
            }
        } else {
            $amount = $booking['amount'];
            $service_type = $booking['service_type'];
            $vehicle_details = $booking['vehicle_make'] . ' ' . $booking['vehicle_model'] . ' (' . $booking['vehicle_number'] . ')';
        }
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}

// Handle payment processing
$payment_message = '';
$payment_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($error_message)) {
    // In a real implementation, you would process the actual payment here
    // For this demo, we'll just update the status to "Payment Done"
    
    $payment_method = $_POST['payment_method'] ?? 'card';
    
    try {
        $pdo = getDBConnection();
        
        // Update booking status to "Payment Done"
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'Payment Done' WHERE booking_id = ? AND customer_id = ?");
        $stmt->execute([$booking_id, $customer_id]);
        
        if ($stmt->rowCount() > 0) {
            // Insert payment record
            $stmt = $pdo->prepare("INSERT INTO payments (booking_id, customer_id, amount_paid, payment_method) VALUES (?, ?, ?, ?)");
            $stmt->execute([$booking_id, $customer_id, $amount, $payment_method]);
            
            $payment_message = "Payment processed successfully! Your booking status has been updated.";
        } else {
            $payment_error = "Failed to update booking status. Please try again.";
        }
    } catch (PDOException $e) {
        $payment_error = "An error occurred while processing your payment: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - AutoFix</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .header {
            background: #2c3e50;
            color: white;
            padding: 15px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #ff4a17;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
        }
        
        .nav-menu li {
            margin-left: 20px;
        }
        
        .nav-menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }
        
        .nav-menu a:hover {
            color: #ff4a17;
        }
        
        .main {
            margin-top: 80px;
            padding: 30px 0;
        }
        
        .payment-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .payment-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .payment-header h1 {
            color: #2c3e50;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .detail-label {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .detail-value {
            text-align: right;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            font-size: 24px;
            font-weight: bold;
            color: #ff4a17;
            border-bottom: 2px solid #eee;
        }
        
        .payment-methods {
            margin: 30px 0;
        }
        
        .payment-methods h3 {
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .method-options {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .method-option {
            flex: 1;
            min-width: 150px;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .method-option:hover {
            border-color: #ff4a17;
        }
        
        .method-option.selected {
            border-color: #ff4a17;
            background-color: #fff5f5;
        }
        
        .method-option i {
            font-size: 24px;
            margin-bottom: 10px;
            color: #ff4a17;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .form-control:focus {
            border-color: #ff4a17;
            outline: none;
        }
        
        small {
            display: block;
            color: #6c757d;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .btn {
            display: inline-block;
            background: #ff4a17;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
            width: 100%;
            text-align: center;
        }
        
        .btn:hover {
            background: #e04010;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #7f8c8d;
        }
        
        .btn-secondary:hover {
            background: #6c7a7b;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        @media (max-width: 768px) {
            .method-options {
                flex-direction: column;
            }
            
            .method-option {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">AutoFix</div>
                <ul class="nav-menu">
                    <li><a href="customerpage/index.php">Home</a></li>
                    <li><a href="bookingfor.php">Book Service</a></li>
                    <li><a href="service_status.php">Service Status</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="payment-container">
            <div class="payment-header">
                <h1>Secure Payment</h1>
                <p>Complete your payment for service booking #<?php echo htmlspecialchars($booking_id ?? 'N/A'); ?></p>
            </div>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
                <div style="text-align: center; margin-top: 30px;">
                    <a href="service_status.php" class="btn">View Service Status</a>
                    <a href="setup_pay_now_test.php" class="btn btn-secondary" style="margin-top: 10px;">Setup Test Booking</a>
                </div>
            <?php elseif($payment_message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($payment_message); ?></div>
                <div style="text-align: center; margin-top: 30px;">
                    <a href="service_status.php" class="btn">View Service Status</a>
                </div>
            <?php else: ?>
                <?php if($payment_error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($payment_error); ?></div>
                <?php endif; ?>
                
                <div class="alert alert-info">
                    <strong>Secure Payment:</strong> Your payment information is encrypted and securely processed.
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Booking ID:</div>
                    <div class="detail-value">#<?php echo htmlspecialchars($booking_id); ?></div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Service:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($service_type ?? 'N/A'); ?></div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Vehicle:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($vehicle_details ?? 'N/A'); ?></div>
                </div>
                
                <div class="total-row">
                    <div>Total Amount:</div>
                    <div>₹<?php echo number_format($amount ?? 0, 2); ?></div>
                </div>
                
                <form method="POST" id="payment-form">
                    <input type="hidden" name="payment_method" id="payment_method" value="card">
                    
                    <div class="payment-methods">
                        <h3>Select Payment Method</h3>
                        <div class="method-options">
                            <div class="method-option selected" onclick="selectPaymentMethod(this, 'card')">
                                <div>💳</div>
                                <div>Credit/Debit Card</div>
                            </div>
                            <div class="method-option" onclick="selectPaymentMethod(this, 'upi')">
                                <div>📱</div>
                                <div>UPI</div>
                            </div>
                            <div class="method-option" onclick="selectPaymentMethod(this, 'netbanking')">
                                <div>🏦</div>
                                <div>Net Banking</div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="card-details">
                        <div class="form-group">
                            <label for="card_number">Card Number</label>
                            <input type="text" id="card_number" name="card_number" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19" required>
                            <small>Enter 16-digit card number</small>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="expiry">Expiry Date</label>
                                    <input type="text" id="expiry" name="expiry" class="form-control" placeholder="MM/YY" maxlength="5" required>
                                    <small>Format: MM/YY</small>
                                </div>
                            </div>
                            
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="cvv">CVV</label>
                                    <input type="text" id="cvv" name="cvv" class="form-control" placeholder="123" maxlength="3" required>
                                    <small>3-digit security code</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="upi-details" style="display: none;">
                        <div class="form-group">
                            <label for="upi_id">UPI ID</label>
                            <input type="text" id="upi_id" name="upi_id" class="form-control" placeholder="yourname@upi">
                        </div>
                    </div>
                    
                    <div id="netbanking-details" style="display: none;">
                        <div class="form-group">
                            <label for="bank">Select Bank</label>
                            <select id="bank" name="bank" class="form-control">
                                <option value="">-- Select Bank --</option>
                                <option value="sbi">State Bank of India</option>
                                <option value="hdfc">HDFC Bank</option>
                                <option value="icici">ICICI Bank</option>
                                <option value="axis">Axis Bank</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn">Pay Now ₹<?php echo number_format($amount ?? 0, 2); ?></button>
                        <a href="service_status.php" class="btn btn-secondary" style="margin-top: 10px;">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>
    
    <script>
        function selectPaymentMethod(element, method) {
            // Remove selected class from all options
            document.querySelectorAll('.method-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            element.classList.add('selected');
            
            // Update hidden input field
            document.getElementById('payment_method').value = method;
            
            // Show/hide payment details based on selection
            document.getElementById('card-details').style.display = method === 'card' ? 'block' : 'none';
            document.getElementById('upi-details').style.display = method === 'upi' ? 'block' : 'none';
            document.getElementById('netbanking-details').style.display = method === 'netbanking' ? 'block' : 'none';
        }
        
        // Card number formatting
        function formatCardNumber(input) {
            // Remove all non-digit characters
            let value = input.value.replace(/\D/g, '');
            
            // Limit to 16 digits
            if (value.length > 16) {
                value = value.substring(0, 16);
            }
            
            // Add space after every 4 digits
            let formattedValue = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }
            
            input.value = formattedValue;
        }
        
        // Expiry date formatting
        function formatExpiryDate(input) {
            // Remove all non-digit characters
            let value = input.value.replace(/\D/g, '');
            
            // Limit to 4 digits (MMYY)
            if (value.length > 4) {
                value = value.substring(0, 4);
            }
            
            // Add slash after 2 digits (MM/YY)
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2);
            }
            
            input.value = value;
        }
        
        // CVV validation
        function validateCVV(input) {
            // Remove all non-digit characters
            let value = input.value.replace(/\D/g, '');
            
            // Limit to 3 digits
            if (value.length > 3) {
                value = value.substring(0, 3);
            }
            
            input.value = value;
        }
        
        // Form validation before submission
        function validateCardForm() {
            const cardNumber = document.getElementById('card_number');
            const expiry = document.getElementById('expiry');
            const cvv = document.getElementById('cvv');
            
            // Remove spaces and validate card number
            const cardDigits = cardNumber.value.replace(/\s/g, '');
            if (cardDigits.length !== 16) {
                alert('Card number must be 16 digits');
                cardNumber.focus();
                return false;
            }
            
            // Validate expiry date format
            const expiryRegex = /^(0[1-9]|1[0-2])\/([0-9]{2})$/;
            if (!expiryRegex.test(expiry.value)) {
                alert('Expiry date must be in MM/YY format');
                expiry.focus();
                return false;
            }
            
            // Validate CVV
            if (cvv.value.length !== 3) {
                alert('CVV must be 3 digits');
                cvv.focus();
                return false;
            }
            
            return true;
        }
        
        // Set initial payment method
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('payment_method').value = 'card';
            
            // Add event listeners for card validation
            const cardNumber = document.getElementById('card_number');
            const expiry = document.getElementById('expiry');
            const cvv = document.getElementById('cvv');
            
            if (cardNumber) {
                cardNumber.addEventListener('input', function() {
                    formatCardNumber(this);
                });
            }
            
            if (expiry) {
                expiry.addEventListener('input', function() {
                    formatExpiryDate(this);
                });
            }
            
            if (cvv) {
                cvv.addEventListener('input', function() {
                    validateCVV(this);
                });
            }
            
            // Add form validation
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (document.getElementById('payment_method').value === 'card' && !validateCardForm()) {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
</body>
</html>