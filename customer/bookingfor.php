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
$success = $error = '';

// Fetch services
$services = getAllServices();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicle_make = $_POST['vehicle_make'] ?? '';
    $vehicle_model = $_POST['vehicle_model'] ?? '';
    $vehicle_number = $_POST['vehicle_number'] ?? '';
    $location_link = $_POST['location_link'] ?? '';
    $service_type = $_POST['service_type'] ?? '';
    $tow_service = isset($_POST['tow_service']) ? 1 : 0;
    $customer_notes = $_POST['customer_notes'] ?? '';
    $booking_date = $_POST['booking_date'] ?? '';
    $booking_time = $_POST['booking_time'] ?? '';
    
    if (!empty($vehicle_make) && !empty($vehicle_model) && !empty($vehicle_number) && 
        !empty($location_link) && !empty($service_type) && !empty($booking_date) && !empty($booking_time)) {
        
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("INSERT INTO bookings (customer_id, vehicle_make, vehicle_model, vehicle_number, location_link, service_type, tow_service, customer_notes, booking_date, booking_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$customer_id, $vehicle_make, $vehicle_model, $vehicle_number, $location_link, $service_type, $tow_service, $customer_notes, $booking_date, $booking_time]);
            
            $success = "Booking submitted successfully! We'll contact you soon to confirm.";
        } catch (PDOException $e) {
            $error = "Failed to submit booking. Please try again.";
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Service - AutoFix</title>
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
        
        .booking-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 40px;
        }
        
        .booking-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .booking-header h1 {
            color: var(--secondary);
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .booking-header p {
            color: var(--gray);
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        
        .form-col {
            flex: 1;
            padding: 0 10px;
            min-width: 200px;
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary);
            font-size: 16px;
        }
        
        .form-control {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: var(--transition);
        }
        
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 74, 23, 0.1);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }
        
        .checkbox-group input {
            margin-right: 10px;
            width: 18px;
            height: 18px;
        }
        
        .btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 15px 35px;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            font-size: 18px;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            width: 100%;
            margin-top: 10px;
        }
        
        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 74, 23, 0.4);
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
        
        .alert {
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            text-align: center;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-footer {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .form-footer a {
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .form-col {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 20px;
            }
            
            .booking-container {
                padding: 25px;
            }
            
            .booking-header h1 {
                font-size: 2rem;
            }
            
            .form-footer {
                flex-direction: column;
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
        <div class="booking-container">
            <div class="booking-header">
                <h1>Book Vehicle Service</h1>
                <p>Fill in the details below to book your service appointment</p>
            </div>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="vehicle_make">Vehicle Make *</label>
                            <input type="text" id="vehicle_make" name="vehicle_make" class="form-control" placeholder="e.g., Toyota" required>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="vehicle_model">Vehicle Model *</label>
                            <input type="text" id="vehicle_model" name="vehicle_model" class="form-control" placeholder="e.g., Camry" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="vehicle_number">Vehicle Number *</label>
                    <input type="text" id="vehicle_number" name="vehicle_number" class="form-control" placeholder="e.g., KL 35 C 1234" required>
                </div>
                
                <div class="form-group">
                    <label for="service_type">Service Type *</label>
                    <select id="service_type" name="service_type" class="form-control" required>
                        <option value="">Select Service</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?php echo htmlspecialchars($service['servicename']); ?>">
                                <?php echo htmlspecialchars($service['servicename']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="location_link">Pickup Address / Map Link *</label>
                    <input type="url" id="location_link" name="location_link" class="form-control" placeholder="Enter address or map link" required>
                </div>
                
                <div class="form-group">
                    <label for="customer_notes">Additional Notes</label>
                    <textarea id="customer_notes" name="customer_notes" class="form-control" placeholder="Any special requests or notes"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="booking_date">Booking Date *</label>
                            <input type="date" id="booking_date" name="booking_date" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="booking_time">Booking Time *</label>
                            <input type="time" id="booking_time" name="booking_time" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="tow_service" name="tow_service">
                    <label for="tow_service">Need Tow Service?</label>
                </div>
                
                <button type="submit" class="btn">Book Now</button>
                
                <div class="form-footer">
                    <a href="profile.php" class="btn btn-secondary">Back to Profile</a>
                </div>
            </form>
        </div>
    </main>
    
    <script>
        // Set min date to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('booking_date').setAttribute('min', today);
            
            // Set default date to tomorrow
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('booking_date').value = tomorrow.toISOString().split('T')[0];
        });
    </script>
</body>
</html>