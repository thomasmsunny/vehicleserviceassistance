<?php
require('../config/autoload.php');

$error = ""; // Initialize error variable

// If driver is already logged in
if (isset($_SESSION['driver_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Database connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME); 
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// When login form is submitted
if (isset($_POST['login'])) {
    $username_or_email = trim($_POST['username']); 
    $password = $_POST['password'];
    
    // Validate input
    if (empty($username_or_email)) {
        $error = "Email is required!";
    } elseif (empty($password)) {
        $error = "Password is required!";
    } else {
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT did, drivername, password FROM drivermanage WHERE email = ?");
        $stmt->bind_param("s", $username_or_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();

            // Check password using password_verify only (more secure)
            if (password_verify($password, $row['password'])) {
                // Set session variables
                $_SESSION['driver_id'] = $row['did'];
                $_SESSION['driver_name'] = $row['drivername'];
                $_SESSION['drivername'] = $row['drivername'];
                $_SESSION['user_role'] = 'driver';

                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid Email or Password!";
            }
        } else {
            $error = "Invalid Email or Password!";
        }
        
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Driver Login - Vehicle Service</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #176B87 0%, #64CCC5 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 420px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        h2 {
            text-align: center;
            color: #176B87;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        input[type="text"], 
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            box-sizing: border-box;
            transition: all 0.3s;
        }

        input:focus {
            border-color: #176B87;
            outline: none;
            box-shadow: 0 0 0 3px rgba(23, 107, 135, 0.1);
        }

        button {
            width: 100%;
            padding: 15px;
            background: #176B87;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            margin-top: 10px;
        }

        button:hover {
            background: #64CCC5;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(23, 107, 135, 0.3);
        }

        .error {
            background: #ffe6e6;
            color: #d32f2f;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #d32f2f;
            font-size: 14px;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h2><i class="fa-solid fa-truck-fast"></i> Driver Portal</h2>
            <p class="subtitle">Vehicle Service Management System</p>
        </div>
        
        <?php if ($error): ?>
            <div class='error'><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Email Address</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       required 
                       placeholder="Enter your email"
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       placeholder="Enter your password">
            </div>

            <button type="submit" name="login"><i class="fa-solid fa-right-to-bracket"></i> Login to Dashboard</button>
        </form>
        
        <div class="footer">
            <p>&copy; 2024 Vehicle Service. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
