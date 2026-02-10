<?php 
require('../config/autoload.php');

$page_title = "Add Driver";
include("includes/admin_header.php");

$file = new FileUpload();
$elements = array(
    "drivername" => "",
    "phone" => "",
    "email" => "",
    "password" => "",
    "license_no" => "",
    "address" => ""
);

$form = new FormAssist($elements, $_POST);
$dao = new DataAccess();

$labels = array(
    'drivername' => "Driver Name",
    'phone' => "Phone Number",
    'email' => "Email ID",
    'password' => "Password",
    'license_no' => "License Number",
    'address' => "Address"
);

$rules = array(
    "drivername" => array("required" => true, "minlength" => 3, "maxlength" => 30, "alphaspaceonly" => true),
    "phone" => array("required" => true, "minlength" => 10, "maxlength" => 10),
    "email" => array("required" => true, "minlength" => 3, "maxlength" => 50),
    "password" => array("required" => true, "minlength" => 4, "maxlength" => 20),
    "license_no" => array("required" => true, "minlength" => 3, "maxlength" => 50),
    "address" => array("required" => true, "minlength" => 5)
);

$validator = new FormValidator($rules, $labels);

$msg = "";

if(isset($_POST["btn_insert"])) {
    if($validator->validate($_POST)) {

        // Hash the password
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $data = array(
            'drivername' => $_POST['drivername'],
            'phone' => $_POST['phone'],
            'email' => $_POST['email'],
            'password' => $hashed_password,
            'license_no' => $_POST['license_no'],
            'address' => $_POST['address'],
            'status' => 'available'  // New drivers are available by default
        );

        if($dao->insert($data,"drivermanage")) {
            echo "<script>alert('New driver added successfully!'); window.location.href='driverviews1.php';</script>";
            exit();
        } else {
            $msg = "<div class='alert alert-danger'>Failed to add driver. Please try again.</div>";
        }
    }
}
?>

<!-- Page Content Starts Here -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom animated-header">
  <h1 class="h2"><i class="fas fa-user-plus text-success"></i> Add New Driver</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <a href="driverviews1.php" class="btn btn-sm btn-outline-secondary btn-hover-effect">
      <i class="fas fa-list"></i> View All Drivers
    </a>
  </div>
</div>

<?php if(!empty($msg)) echo $msg; ?>

<div class="card shadow-sm border-0 card-hover">
  <div class="card-header bg-gradient-success text-white">
    <i class="fas fa-id-card"></i> Driver Information
  </div>
  <div class="card-body p-4">
    <div class="row mb-3">
      <div class="col-12">
        <div class="alert alert-info alert-dismissible fade show border-0" role="alert">
          <i class="fas fa-info-circle"></i> <strong>Quick Tip:</strong> All fields are required. Password will be encrypted for security.
          <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
          </button>
        </div>
      </div>
    </div>

    <form action="" method="POST" enctype="multipart/form-data">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label font-weight-bold">
              <i class="fas fa-user text-success"></i> Driver Name: 
              <span class="text-danger">*</span>
            </label>
            <?= $form->textBox('drivername', array(
              'class' => 'form-control form-control-lg input-focus',
              'placeholder' => 'Enter driver full name'
            )); ?>
            <span class="text-danger font-weight-bold"><?= $validator->error('drivername'); ?></span>
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label font-weight-bold">
              <i class="fas fa-phone text-success"></i> Phone Number: 
              <span class="text-danger">*</span>
            </label>
            <?= $form->textBox('phone', array(
              'class' => 'form-control form-control-lg input-focus',
              'placeholder' => '10-digit phone number'
            )); ?>
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
            <?= $form->textBox('email', array(
              'class' => 'form-control form-control-lg input-focus',
              'placeholder' => 'driver@example.com'
            )); ?>
            <span class="text-danger font-weight-bold"><?= $validator->error('email'); ?></span>
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label font-weight-bold">
              <i class="fas fa-lock text-success"></i> Password: 
              <span class="text-danger">*</span>
            </label>
            <?= $form->passwordBox('password', array(
              'class' => 'form-control form-control-lg input-focus',
              'placeholder' => 'Minimum 4 characters'
            )); ?>
            <small class="form-text text-muted">
              <i class="fas fa-shield-alt"></i> Password will be encrypted
            </small>
            <span class="text-danger font-weight-bold"><?= $validator->error('password'); ?></span>
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
            <?= $form->textBox('license_no', array(
              'class' => 'form-control form-control-lg input-focus',
              'placeholder' => 'Driver license number'
            )); ?>
            <span class="text-danger font-weight-bold"><?= $validator->error('license_no'); ?></span>
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label font-weight-bold">
              <i class="fas fa-map-marker-alt text-success"></i> Address: 
              <span class="text-danger">*</span>
            </label>
            <?= $form->textArea('address', array(
              'class' => 'form-control input-focus',
              'placeholder' => 'Full address',
              'rows' => 3
            )); ?>
            <span class="text-danger font-weight-bold"><?= $validator->error('address'); ?></span>
          </div>
        </div>
      </div>

      <hr class="my-4">

      <div class="form-group mb-0">
        <button type="submit" name="btn_insert" class="btn btn-success btn-lg px-5 btn-submit-effect">
          <i class="fas fa-user-plus"></i> Add Driver
        </button>
        <a href="driverviews1.php" class="btn btn-outline-secondary btn-lg px-4 ml-2">
          <i class="fas fa-times"></i> Cancel
        </a>
      </div>
    </form>
  </div>
</div>

<!-- Custom Styles -->
<style>
.animated-header {
  animation: slideInDown 0.5s ease-out;
}

@keyframes slideInDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.card-hover {
  transition: all 0.3s ease;
}

.card-hover:hover {
  transform: translateY(-3px);
  box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
}

.bg-gradient-success {
  background: linear-gradient(135deg, #1cc88a 0%, #169b6b 100%);
}

.input-focus:focus {
  border-color: #1cc88a;
  box-shadow: 0 0 0 0.2rem rgba(28, 200, 138, 0.25);
}

.btn-submit-effect {
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.btn-submit-effect:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(28, 200, 138, 0.3);
}

.btn-hover-effect {
  transition: all 0.3s ease;
}

.btn-hover-effect:hover {
  transform: translateX(-3px);
}

.alert {
  animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>
<!-- Page Content Ends Here -->

<?php include("includes/admin_footer.php"); ?>
