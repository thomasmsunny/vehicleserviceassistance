<?php 
require('../config/autoload.php');

$page_title = "Edit Driver";
include("includes/admin_header.php");

$dao = new DataAccess();

// Check if ID is provided
if (!isset($_GET['id'])) {
    echo "<script>alert('Invalid driver ID'); window.location.href='driverviews1.php';</script>";
    exit();
}

$driver_id = $_GET['id'];
$info = $dao->getData('*', 'drivermanage', 'did=' . $driver_id);

// Check if driver exists
if (!$info || count($info) == 0) {
    echo "<script>alert('Driver not found'); window.location.href='driverviews1.php';</script>";
    exit();
}

$file = new FileUpload();
$current_status = isset($info[0]['status']) ? $info[0]['status'] : 'available';
$elements = array(
    "drivername" => $info[0]['drivername'],
    "phone" => $info[0]['phone'],
    "email" => $info[0]['email'],
    "license_no" => isset($info[0]['license_no']) ? $info[0]['license_no'] : '',
    "address" => isset($info[0]['address']) ? $info[0]['address'] : ''
);

$form = new FormAssist($elements, $_POST);

$labels = array(
    'drivername' => "Driver Name",
    'phone' => "Phone Number",
    'email' => "Email",
    'license_no' => "License Number",
    'address' => "Address"
);

$rules = array(
    "drivername" => array("required" => true, "minlength" => 3, "maxlength" => 30, "alphaspaceonly" => true),
    "phone" => array("required" => true, "minlength" => 10, "maxlength" => 10),
    "email" => array("required" => true, "minlength" => 3, "maxlength" => 50),
    "license_no" => array("required" => true, "minlength" => 3, "maxlength" => 50),
    "address" => array("required" => true, "minlength" => 5)
);

$validator = new FormValidator($rules, $labels);
$msg = "";

if (isset($_POST["btn_update"])) {
    if ($validator->validate($_POST)) {
        $data = array(
            'drivername' => $_POST['drivername'],
            'phone' => $_POST['phone'],
            'email' => $_POST['email'],
            'license_no' => $_POST['license_no'],
            'address' => $_POST['address'],
            'status' => isset($_POST['status']) ? $_POST['status'] : 'available'
        );
        
        // Only update password if provided
        if (!empty($_POST['password'])) {
            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        
        if ($dao->update($data, "drivermanage", "did=" . $driver_id)) {
            echo "<script>alert('Driver updated successfully!'); window.location.href='driverviews1.php';</script>";
            exit();
        } else {
            $error = $dao->getErrors();
            $msg = "<div class='alert alert-danger'><strong>Update failed!</strong><br>" . ($error ? $error : "Please try again.") . "</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger'>Please fix the validation errors.</div>";
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h1 class="h2"><i class="fas fa-user-edit text-success"></i> Edit Driver</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <a href="driverviews1.php" class="btn btn-sm btn-outline-secondary">
      <i class="fas fa-arrow-left"></i> Back to List
    </a>
  </div>
</div>

<?php if (!empty($msg)) echo $msg; ?>

<div class="card shadow-sm border-0">
  <div class="card-header bg-gradient-success text-white">
    <i class="fas fa-id-card"></i> Driver Details
  </div>
  <div class="card-body p-4">
    <form action="" method="POST" enctype="multipart/form-data">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label font-weight-bold">
              <i class="fas fa-user text-success"></i> Driver Name: 
              <span class="text-danger">*</span>
            </label>
            <?= $form->textBox('drivername', array('class' => 'form-control form-control-lg', 'placeholder' => 'Enter driver name')); ?>
            <span class="text-danger font-weight-bold"><?= $validator->error('drivername'); ?></span>
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label font-weight-bold">
              <i class="fas fa-phone text-success"></i> Phone Number: 
              <span class="text-danger">*</span>
            </label>
            <?= $form->textBox('phone', array('class' => 'form-control form-control-lg', 'placeholder' => '10-digit phone')); ?>
            <span class="text-danger font-weight-bold"><?= $validator->error('phone'); ?></span>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label font-weight-bold">
              <i class="fas fa-envelope text-success"></i> Email: 
              <span class="text-danger">*</span>
            </label>
            <?= $form->textBox('email', array('class' => 'form-control form-control-lg', 'placeholder' => 'driver@example.com')); ?>
            <span class="text-danger font-weight-bold"><?= $validator->error('email'); ?></span>
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label font-weight-bold">
              <i class="fas fa-lock text-success"></i> Password:
            </label>
            <input type="password" name="password" class="form-control form-control-lg" placeholder="Leave blank to keep current password">
            <small class="form-text text-muted">
              <i class="fas fa-info-circle"></i> Only fill this if you want to change the password
            </small>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label font-weight-bold">
              <i class="fas fa-id-badge text-success"></i> License Number: 
              <span class="text-danger">*</span>
            </label>
            <?= $form->textBox('license_no', array('class' => 'form-control form-control-lg', 'placeholder' => 'License number')); ?>
            <span class="text-danger font-weight-bold"><?= $validator->error('license_no'); ?></span>
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label font-weight-bold">
              <i class="fas fa-toggle-on text-success"></i> Status:
            </label>
            <select name="status" class="form-control form-control-lg">
              <option value="available" <?= $current_status == 'available' ? 'selected' : '' ?>>
                ✓ Available
              </option>
              <option value="unavailable" <?= $current_status == 'unavailable' || $current_status == 'busy' || $current_status == 'offline' ? 'selected' : '' ?>>
                ✗ Unavailable
              </option>
            </select>
            <small class="form-text text-muted">
              <i class="fas fa-info-circle"></i> Set driver availability status
            </small>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <div class="form-group">
            <label class="form-label font-weight-bold">
              <i class="fas fa-map-marker-alt text-success"></i> Address: 
              <span class="text-danger">*</span>
            </label>
            <?= $form->textArea('address', array('class' => 'form-control', 'placeholder' => 'Full address', 'rows' => 3)); ?>
            <span class="text-danger font-weight-bold"><?= $validator->error('address'); ?></span>
          </div>
        </div>
      </div>

      <hr class="my-4">

      <div class="form-group mb-0">
        <button type="submit" name="btn_update" class="btn btn-success btn-lg px-5">
          <i class="fas fa-save"></i> Update Driver
        </button>
        <a href="driverviews1.php" class="btn btn-outline-secondary btn-lg px-4 ml-2">
          <i class="fas fa-times"></i> Cancel
        </a>
      </div>
    </form>
  </div>
</div>

<style>
.bg-gradient-success {
  background: linear-gradient(135deg, #1cc88a 0%, #169b6b 100%);
}
</style>

<?php include("includes/admin_footer.php"); ?>


