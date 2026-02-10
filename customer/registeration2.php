<?php
// Simplified registration page without external dependencies that cause loading issues
$error = '';
$success = '';
$fullname = $email = $phone = '';

if (isset($_POST['register'])) {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Simple validation
    if (empty($fullname) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Try database connection
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=vehicleservice", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM customerreg WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Email already registered. Please use a different email.';
            } else {
                // Split name
                $names = explode(' ', $fullname, 2);
                $firstname = $names[0];
                $lastname = isset($names[1]) ? $names[1] : '';
                
                // Insert user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO customerreg (firstname, lastname, email, phone, password, status) VALUES (?, ?, ?, ?, ?, 1)");
                $stmt->execute([$firstname, $lastname, $email, $phone, $hashedPassword]);
                
                $success = 'Registration successful! You can now login.';
                // Clear form fields
                $fullname = $email = $phone = '';
            }
        } catch(Exception $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>AutoFix - Customer Registration</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  body {
      background: linear-gradient(135deg, rgba(104,141,196,0.9), rgba(15,42,48,0.9)), 
                  url('images/logo.jpg') no-repeat center center/cover;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
  }
  .register-box {
      background: white;
      border-radius: 15px;
      width: 100%;
      max-width: 400px; /* Reduced from 450px */
      padding: 30px 25px; /* Reduced padding */
      box-shadow: 0 10px 25px rgba(0,0,0,0.3);
      text-align: center;
      animation: fadeIn 0.8s ease;
  }
  @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-15px); }
      to { opacity: 1; transform: translateY(0); }
  }
  .register-box h2 {
      margin-bottom: 20px; /* Reduced from 25px */
      color: #ff4a17;
      font-weight: 600;
      font-size: 1.8rem; /* Reduced from 2rem */
  }
  .input-group { 
      margin-bottom: 15px; /* Reduced from 20px */
      text-align: left; 
  }
  .input-group label {
      display: block;
      margin-bottom: 6px; /* Reduced from 8px */
      font-weight: 600;
      color: #2c3e50;
      font-size: 15px; /* Slightly smaller */
  }
  .input-group input {
      width: 100%;
      padding: 12px 13px; /* Reduced padding */
      border-radius: 6px; /* Reduced from 8px */
      border: 1px solid #ccc;
      outline: none;
      transition: all 0.3s;
      font-size: 15px; /* Slightly smaller */
  }
  .input-group input:focus {
      border-color: #ff4a17;
      box-shadow: 0 0 5px rgba(255, 74, 23, 0.4);
  }
  .btn-register {
      width: 100%;
      background: #ff4a17;
      color: white;
      border: none;
      padding: 12px; /* Reduced from 15px */
      border-radius: 6px; /* Reduced from 8px */
      font-weight: 600;
      font-size: 15px; /* Reduced from 16px */
      cursor: pointer;
      transition: all 0.3s;
      margin-top: 8px; /* Reduced from 10px */
      text-align: center; /* Center the text */
  }
  .btn-register:hover { 
      background: #e04010;
      transform: translateY(-2px);
      box-shadow: 0 5px 12px rgba(255, 74, 23, 0.4);
  }
  .links { 
      margin-top: 15px; /* Reduced from 20px */
      font-size: 13px; /* Smaller font */
  }
  .links a { 
      color: #ff4a17; 
      text-decoration: none; 
      font-weight: 600;
  }
  .links a:hover { 
      text-decoration: underline; 
  }
  .alert {
      padding: 12px; /* Reduced from 15px */
      border-radius: 6px; /* Reduced from 8px */
      margin-bottom: 15px; /* Reduced from 20px */
      text-align: center;
      font-size: 14px; /* Smaller font */
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
  .header {
      background: #2c3e50;
      color: white;
      padding: 12px; /* Reduced from 15px */
      border-radius: 8px 8px 0 0; /* Reduced from 10px */
      margin: -30px -25px 20px -25px; /* Adjusted for smaller padding */
  }
  .header h1 {
      font-size: 1.6rem; /* Reduced from 1.8rem */
      margin-bottom: 3px; /* Reduced from 5px */
  }
  .header p {
      opacity: 0.8;
      font-size: 14px; /* Smaller font */
  }
  .back-link {
      display: inline-block;
      margin-top: 15px; /* Reduced from 20px */
      color: #2c3e50;
      text-decoration: none;
      font-weight: 600;
      font-size: 13px; /* Smaller font */
  }
  .back-link:hover {
      text-decoration: underline;
  }
</style>
</head>
<body>
  <div class="register-box">
    <div class="header">
      <h1>AutoFix</h1>
      <p>Customer Registration</p>
    </div>
    
    <?php if($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <script>
            setTimeout(function() {
                window.location.href = 'customerlogin.php';
            }, 2000);
        </script>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group">
            <label for="fullname">Full Name</label>
            <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" placeholder="Enter your full name" required>
        </div>

        <div class="input-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email" required>
        </div>

        <div class="input-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="Enter your phone number" required>
        </div>

        <div class="input-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <div class="input-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
        </div>

        <button type="submit" name="register" class="btn-register">Register</button>
        <div class="links">
            Already have an account? <a href="customerlogin.php">Login</a>
        </div>
        <a href="customerpage/index.php" class="back-link">← Back to Home</a>
    </form>
  </div>

  <script>
  // Simple password visibility toggle (without jQuery dependency)
  document.addEventListener('DOMContentLoaded', function() {
      // Create toggle functionality for password fields
      const passwordFields = ['password', 'confirm_password'];
      passwordFields.forEach(function(fieldId) {
          const field = document.getElementById(fieldId);
          if (field) {
              // Create toggle button
              const toggle = document.createElement('span');
              toggle.innerHTML = '👁️';
              toggle.style.position = 'absolute';
              toggle.style.right = '13px'; /* Adjusted for smaller padding */
              toggle.style.top = '35px'; /* Adjusted for smaller padding */
              toggle.style.cursor = 'pointer';
              toggle.style.userSelect = 'none';
              toggle.style.fontSize = '14px'; /* Smaller icon */
              
              // Add click event
              toggle.addEventListener('click', function() {
                  if (field.type === 'password') {
                      field.type = 'text';
                      toggle.innerHTML = '🔒';
                  } else {
                      field.type = 'password';
                      toggle.innerHTML = '👁️';
                  }
              });
              
              // Position relative for parent
              field.parentNode.style.position = 'relative';
              field.parentNode.appendChild(toggle);
          }
      });
  });
  </script>
</body>
</html>