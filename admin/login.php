<?php
// Start session and autoload
require('../config/autoload.php');

$dao = new DataAccess(); // Your DataAccess class
$msg = "";

// Handle admin login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['uname']);
    $password = trim($_POST['password']);

    // Fetch admin user from DB
    $condition = "username='$username' AND password='$password'";
    $info = $dao->getData('*', 'adminlogin', $condition);
    
    // Check if user found (getData returns empty array if no results)
    if ($info && count($info) > 0) {
        // Get the first result
        $admin = $info[0];
        
        // Determine which ID field exists
        $id_field = (isset($admin['admin_id']) ? 'admin_id' : null);
        
        if ($id_field) {
            // ---------- ADMIN SESSION ----------
            $_SESSION['admin_id'] = $admin[$id_field];      // admin primary key
            $_SESSION['admin_name'] = $admin['username'];    // admin name
            $_SESSION['user_role'] = 'admin';               // separate role for access control

            // Redirect to admin dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $msg = "Database configuration error: ID field not found";
        }
    } else {
        $msg = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login</title>
<style>
* { box-sizing: border-box; }
body { font-family: 'Poppins', sans-serif; display:flex; justify-content:center; align-items:center; height:100vh; background:#f4f4f4; margin:0; }
.login-container { background:#fff; padding:40px; border-radius:10px; width:100%; max-width:400px; box-shadow:0 8px 16px rgba(0,0,0,0.2); }
h2 { text-align:center; margin-bottom:30px; color:#176B87; }
.form-group { margin-bottom:20px; }
label { display:block; margin-bottom:8px; color:#333; }
input { width:100%; padding:10px; border-radius:5px; border:1px solid #ccc; font-size:1em; }
.btn { width:100%; padding:12px; background-color:#176B87; color:#fff; border:none; border-radius:5px; font-size:1em; cursor:pointer; transition:0.3s; }
.btn:hover { background-color:#14546c; }
.error { color:red; text-align:center; margin-bottom:15px; }
</style>
</head>
<body>
<div class="login-container">
    <h2>Admin Login</h2>
    <?php if(!empty($msg)) echo "<div class='error'>$msg</div>"; ?>
    <form method="POST">
        <div class="form-group">
            <label for="uname">Username</label>
            <input type="text" name="uname" id="uname" required />
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required />
        </div>
        <button type="submit" class="btn">Login</button>
    </form>
</div>
</body>
</html>
