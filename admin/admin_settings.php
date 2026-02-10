<?php
require('../config/autoload.php');
$page_title = "System Settings";
include("includes/admin_header.php");

$dao = new DataAccess();
$msg = "";

// This is a template for system settings
// You can expand this based on your needs
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h1 class="h2"><i class="fas fa-cog"></i> System Settings</h1>
</div>

<?= $msg ?>

<div class="row">
  <div class="col-md-6">
    <!-- General Settings -->
    <div class="card mb-4">
      <div class="card-header">
        <i class="fas fa-sliders-h"></i> General Settings
      </div>
      <div class="card-body">
        <form method="POST">
          <div class="form-group">
            <label for="site_name">Site Name</label>
            <input type="text" class="form-control" id="site_name" name="site_name" 
                   value="Vehicle Service Management" placeholder="Enter site name">
          </div>
          
          <div class="form-group">
            <label for="contact_email">Contact Email</label>
            <input type="email" class="form-control" id="contact_email" name="contact_email" 
                   placeholder="admin@vehicleservice.com">
          </div>
          
          <div class="form-group">
            <label for="contact_phone">Contact Phone</label>
            <input type="text" class="form-control" id="contact_phone" name="contact_phone" 
                   placeholder="+91 1234567890">
          </div>
          
          <button type="submit" name="update_general" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Settings
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <!-- Email Settings -->
    <div class="card mb-4">
      <div class="card-header">
        <i class="fas fa-envelope"></i> Email Configuration
      </div>
      <div class="card-body">
        <form method="POST">
          <div class="form-group">
            <label for="smtp_host">SMTP Host</label>
            <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                   placeholder="smtp.gmail.com">
          </div>
          
          <div class="form-group">
            <label for="smtp_port">SMTP Port</label>
            <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                   placeholder="587">
          </div>
          
          <div class="form-group">
            <label for="smtp_username">SMTP Username</label>
            <input type="text" class="form-control" id="smtp_username" name="smtp_username" 
                   placeholder="your-email@gmail.com">
          </div>
          
          <div class="form-group">
            <label for="smtp_password">SMTP Password</label>
            <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                   placeholder="Enter password">
          </div>
          
          <button type="submit" name="update_email" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Email Settings
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <!-- Business Hours -->
    <div class="card mb-4">
      <div class="card-header">
        <i class="fas fa-clock"></i> Business Hours
      </div>
      <div class="card-body">
        <form method="POST">
          <div class="form-group">
            <label for="opening_time">Opening Time</label>
            <input type="time" class="form-control" id="opening_time" name="opening_time" 
                   value="09:00">
          </div>
          
          <div class="form-group">
            <label for="closing_time">Closing Time</label>
            <input type="time" class="form-control" id="closing_time" name="closing_time" 
                   value="18:00">
          </div>
          
          <div class="form-group">
            <label>Working Days</label>
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="mon" checked>
              <label class="form-check-label" for="mon">Monday</label>
            </div>
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="tue" checked>
              <label class="form-check-label" for="tue">Tuesday</label>
            </div>
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="wed" checked>
              <label class="form-check-label" for="wed">Wednesday</label>
            </div>
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="thu" checked>
              <label class="form-check-label" for="thu">Thursday</label>
            </div>
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="fri" checked>
              <label class="form-check-label" for="fri">Friday</label>
            </div>
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="sat">
              <label class="form-check-label" for="sat">Saturday</label>
            </div>
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="sun">
              <label class="form-check-label" for="sun">Sunday</label>
            </div>
          </div>
          
          <button type="submit" name="update_hours" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Hours
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <!-- System Maintenance -->
    <div class="card mb-4">
      <div class="card-header">
        <i class="fas fa-tools"></i> System Maintenance
      </div>
      <div class="card-body">
        <h6>Database Backup</h6>
        <p class="text-muted">Create a backup of your database</p>
        <button class="btn btn-info mb-3">
          <i class="fas fa-download"></i> Backup Database
        </button>
        
        <hr>
        
        <h6>Clear Cache</h6>
        <p class="text-muted">Clear system cache to improve performance</p>
        <button class="btn btn-warning mb-3">
          <i class="fas fa-trash-alt"></i> Clear Cache
        </button>
        
        <hr>
        
        <h6>System Information</h6>
        <table class="table table-sm">
          <tr>
            <td><strong>PHP Version:</strong></td>
            <td><?= phpversion() ?></td>
          </tr>
          <tr>
            <td><strong>Server Software:</strong></td>
            <td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></td>
          </tr>
          <tr>
            <td><strong>Database:</strong></td>
            <td>MySQL</td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include("includes/admin_footer.php"); ?>
