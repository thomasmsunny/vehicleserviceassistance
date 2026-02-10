<?php
require('../config/autoload.php');
$page_title = "Admin Profile";
include("includes/admin_header.php");

$dao = new DataAccess();
$msg = "";

// Get current admin data
$admin_id = $_SESSION['admin_id'];
$admin_data = $dao->getData('*', 'adminlogin', "admin_id='$admin_id'");
$admin = $admin_data[0];

// Handle profile update
if(isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    
    $data = array('username' => $username);
    
    if($dao->update($data, 'adminlogin', "admin_id='$admin_id'")) {
        $_SESSION['admin_name'] = $username;
        $msg = "<div class='alert alert-success'>Profile updated successfully!</div>";
        // Refresh admin data
        $admin_data = $dao->getData('*', 'adminlogin', "admin_id='$admin_id'");
        $admin = $admin_data[0];
    } else {
        $msg = "<div class='alert alert-danger'>Failed to update profile!</div>";
    }
}

// Handle password change
if(isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    if($admin['password'] == $current_password || password_verify($current_password, $admin['password'])) {
        if($new_password == $confirm_password) {
            if(strlen($new_password) >= 6) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $data = array('password' => $hashed_password);
                
                if($dao->update($data, 'adminlogin', "admin_id='$admin_id'")) {
                    $msg = "<div class='alert alert-success'>Password changed successfully!</div>";
                } else {
                    $msg = "<div class='alert alert-danger'>Failed to change password!</div>";
                }
            } else {
                $msg = "<div class='alert alert-warning'>Password must be at least 6 characters long!</div>";
            }
        } else {
            $msg = "<div class='alert alert-warning'>New passwords do not match!</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger'>Current password is incorrect!</div>";
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h1 class="h2"><i class="fas fa-user"></i> Admin Profile</h1>
</div>

<?= $msg ?>

<div class="row">
  <div class="col-md-4">
    <!-- Profile Card -->
    <div class="card">
      <div class="card-body text-center">
        <div class="mb-3">
          <i class="fas fa-user-circle fa-5x text-primary"></i>
        </div>
        <h4><?= htmlspecialchars($admin['username']) ?></h4>
        <p class="text-muted">Administrator</p>
        <hr>
        <p><small class="text-muted">Member since: <?= date('M d, Y', strtotime($admin['created_at'] ?? 'now')) ?></small></p>
      </div>
    </div>
  </div>

  <div class="col-md-8">
    <!-- Update Profile Form -->
    <div class="card mb-4">
      <div class="card-header">
        <i class="fas fa-edit"></i> Update Profile
      </div>
      <div class="card-body">
        <form method="POST">
          <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" 
                   value="<?= htmlspecialchars($admin['username']) ?>" required>
          </div>
          <button type="submit" name="update_profile" class="btn btn-primary">
            <i class="fas fa-save"></i> Update Profile
          </button>
        </form>
      </div>
    </div>

    <!-- Change Password Form -->
    <div class="card">
      <div class="card-header">
        <i class="fas fa-key"></i> Change Password
      </div>
      <div class="card-body">
        <form method="POST">
          <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" class="form-control" id="current_password" 
                   name="current_password" required>
          </div>
          <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" class="form-control" id="new_password" 
                   name="new_password" required minlength="6">
            <small class="form-text text-muted">Minimum 6 characters</small>
          </div>
          <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" class="form-control" id="confirm_password" 
                   name="confirm_password" required minlength="6">
          </div>
          <button type="submit" name="change_password" class="btn btn-warning">
            <i class="fas fa-lock"></i> Change Password
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include("includes/admin_footer.php"); ?>
