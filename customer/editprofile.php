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

$firstname = $customer['firstname'];
$lastname = $customer['lastname'];
$email = $customer['email'];
$phone = $customer['phone'];

// Handle form submission
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_firstname = trim($_POST['firstname'] ?? '');
    $new_lastname = trim($_POST['lastname'] ?? '');
    $new_email = trim($_POST['email'] ?? '');
    $new_phone = trim($_POST['phone'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate current password
    if (empty($current_password)) {
        $error = "Please enter your current password.";
    } elseif (!password_verify($current_password, $customer['password'])) {
        $error = "Current password is incorrect.";
    } elseif (empty($new_firstname) || empty($new_lastname) || empty($new_email) || empty($new_phone)) {
        $error = "All fields are required.";
    } else {
        try {
            $pdo = getDBConnection();
            
            // Check if email is already taken by another user
            if ($new_email !== $customer['email']) {
                $stmt = $pdo->prepare("SELECT customer_id FROM customerreg WHERE email = ? AND customer_id != ?");
                $stmt->execute([$new_email, $customer_id]);
                if ($stmt->fetch()) {
                    $error = "Email is already registered with another account.";
                    goto skip_update;
                }
            }
            
            // Update password if provided
            if (!empty($new_password)) {
                if ($new_password !== $confirm_password) {
                    $error = "New passwords do not match.";
                    goto skip_update;
                }
                if (strlen($new_password) < 6) {
                    $error = "New password must be at least 6 characters.";
                    goto skip_update;
                }
                
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE customerreg SET firstname = ?, lastname = ?, email = ?, phone = ?, password = ? WHERE customer_id = ?");
                $stmt->execute([$new_firstname, $new_lastname, $new_email, $new_phone, $hashed_password, $customer_id]);
            } else {
                // Update without password change
                $stmt = $pdo->prepare("UPDATE customerreg SET firstname = ?, lastname = ?, email = ?, phone = ? WHERE customer_id = ?");
                $stmt->execute([$new_firstname, $new_lastname, $new_email, $new_phone, $customer_id]);
            }
            
            $success = "Profile updated successfully!";
            
            // Update session data
            $_SESSION['customer_name'] = $new_firstname . ' ' . $new_lastname;
            $_SESSION['customer_email'] = $new_email;
            
            // Refresh customer data
            $customer = getCustomerById($customer_id);
            $firstname = $customer['firstname'];
            $lastname = $customer['lastname'];
            $email = $customer['email'];
            $phone = $customer['phone'];
            
            skip_update:
        } catch (PDOException $e) {
            $error = "Failed to update profile. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - AutoFix</title>
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
        
        .edit-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 40px;
        }
        
        .edit-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .edit-header h1 {
            color: var(--secondary);
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .edit-header p {
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
        
        .password-requirements {
            background: var(--light);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-top: 10px;
        }
        
        .password-requirements h4 {
            color: var(--secondary);
            margin-bottom: 10px;
        }
        
        .password-requirements ul {
            padding-left: 20px;
            margin: 0;
        }
        
        .password-requirements li {
            margin-bottom: 5px;
            font-size: 14px;
            color: var(--gray);
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
            
            .edit-container {
                padding: 25px;
            }
            
            .edit-header h1 {
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
        <div class="edit-container">
            <div class="edit-header">
                <h1>Edit Profile</h1>
                <p>Update your personal information and account settings</p>
            </div>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="current_password">Current Password *</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Enter current password" required>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="firstname">First Name</label>
                            <input type="text" id="firstname" name="firstname" class="form-control" value="<?php echo htmlspecialchars($firstname); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="lastname">Last Name</label>
                            <input type="text" id="lastname" name="lastname" class="form-control" value="<?php echo htmlspecialchars($lastname); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Leave blank to keep current">
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm new password">
                        </div>
                    </div>
                </div>
                
                <div class="password-requirements">
                    <h4>Password Requirements:</h4>
                    <ul>
                        <li>At least 6 characters long</li>
                        <li>Leave blank to keep current password</li>
                    </ul>
                </div>
                
                <button type="submit" class="btn">Update Profile</button>
                
                <div class="form-footer">
                    <a href="profile.php" class="btn btn-secondary">Back to Profile</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>