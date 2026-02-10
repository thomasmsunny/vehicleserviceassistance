<?php
session_start();

// Database connection - using direct PDO connection to avoid dependencies
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        // Direct database connection
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=vehicleservice", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Get customer by email
            $stmt = $pdo->prepare("SELECT * FROM customerreg WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['customer_id'] = $user['customer_id'];
                $_SESSION['customer_name'] = trim($user['firstname'] . ' ' . $user['lastname']);
                $_SESSION['customer_email'] = $user['email'];
                header("Location: customerpage/index.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } catch(Exception $e) {
            $error = "Login failed. Please try again.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login - AutoFix</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #688dc4, #0f2a30);
            color: #212529;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            overflow: hidden;
        }
        
        .login-header {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 25px 20px;
        }
        
        .logo {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .login-header h1 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        
        .login-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .login-form {
            padding: 25px;
        }
        
        .form-group {
            margin-bottom: 18px;
        }
        
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 15px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #ff4a17;
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 74, 23, 0.1);
        }
        
        .btn {
            display: inline-block;
            background: #ff4a17;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 8px;
            text-align: center;
        }
        
        .btn:hover {
            background: #e04010;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 74, 23, 0.4);
        }
        
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 18px;
            text-align: center;
            font-size: 14px;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .form-footer p {
            margin-bottom: 12px;
            color: #6c757d;
            font-size: 13px;
        }
        
        .form-footer a {
            color: #ff4a17;
            text-decoration: none;
            font-weight: 600;
        }
        
        .form-footer a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 576px) {
            .login-container {
                margin: 10px;
            }
            
            .login-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">🔧</div>
            <h1>AutoFix</h1>
            <p>Customer Login Portal</p>
        </div>
        
        <div class="login-form">
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn">Login</button>
            </form>
            
            <div class="form-footer">
                <p>Don't have an account? <a href="registeration2.php">Register Now</a></p>
                <p><a href="customerpage/index.php">← Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>