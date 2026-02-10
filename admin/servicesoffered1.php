<?php 
require('../config/autoload.php');

$page_title = "Add Service";  // Set page title
include("includes/admin_header.php");  // Use new header

$file = new FileUpload();
$elements = array(
    "servicename" => "",
    "description" => ""
);

$form = new FormAssist($elements, $_POST);

$dao = new DataAccess();

$labels = array(
    'servicename' => "Service Name",
    'description' => "Service Description"
);

$rules = array(
    "servicename" => array(
        "required" => true,
        "minlength" => 3,
        "maxlength" => 30,
        "alphaspaceonly" => true
    ),
    "description" => array(
        "required" => true,
        "minlength" => 10
    )
);

$validator = new FormValidator($rules, $labels);

$msg = "";

if (isset($_POST["btn_insert"])) {
    if ($validator->validate($_POST)) {
        // Handle file upload
        $image_name = "";
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_dir = "../uploads/services/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
            
            if (in_array(strtolower($file_extension), $allowed_extensions)) {
                $image_name = uniqid() . '.' . $file_extension;
                $target_file = $upload_dir . $image_name;
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $msg = "<div class='alert alert-danger'>Failed to upload image.</div>";
                }
            } else {
                $msg = "<div class='alert alert-danger'>Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.</div>";
            }
        }
        
        if (empty($msg)) {
            $data = array(
                'servicename' => $_POST['servicename'],
                'description' => $_POST['description'],
                'image' => $image_name,
                'status' => 'active'  // Set default status to active
            );
            
            if ($dao->insert($data, "services")) {
                echo "<script>alert('New service added successfully!'); window.location.href='viewservices1.php';</script>";
                exit();
            } else {
                $msg = "<div class='alert alert-danger'>Failed to add service. Please try again.</div>";
            }
        }
    }
}
?>

<!-- Page Content Starts Here -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom animated-header">
  <h1 class="h2"><i class="fas fa-plus-circle text-primary"></i> Add New Service</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <a href="viewservices1.php" class="btn btn-sm btn-outline-secondary btn-hover-effect">
      <i class="fas fa-list"></i> View All Services
    </a>
  </div>
</div>

<?php if(!empty($msg)) echo $msg; ?>

<div class="card shadow-sm border-0 card-hover">
  <div class="card-header bg-gradient-primary text-white">
    <i class="fas fa-tools"></i> Service Details
  </div>
  <div class="card-body p-4">
    <div class="row mb-3">
      <div class="col-12">
        <div class="alert alert-info alert-dismissible fade show border-0" role="alert">
          <i class="fas fa-info-circle"></i> <strong>Quick Tip:</strong> Enter a clear and descriptive name for the service.
          <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
          </button>
        </div>
      </div>
    </div>
    
    <form action="" method="POST" enctype="multipart/form-data">
      <div class="row">
        <div class="col-md-8">
          <div class="form-group">
            <label class="form-label font-weight-bold">
              <i class="fas fa-cog text-primary"></i> Service Name: 
              <span class="text-danger">*</span>
            </label>
            <?= $form->textBox('servicename', array(
              'class' => 'form-control form-control-lg input-focus', 
              'placeholder' => 'e.g., Vehicle Towing, Tire Service, Battery Jump Start',
              'autofocus' => true
            )); ?>
            <small class="form-text text-muted">
              <i class="fas fa-lightbulb"></i> Enter 3-30 characters (letters and spaces only)
            </small>
            <span class="text-danger font-weight-bold"><?= $validator->error('servicename'); ?></span>
          </div>
          
          <div class="form-group">
            <label class="form-label font-weight-bold">
              <i class="fas fa-align-left text-primary"></i> Service Description: 
              <span class="text-danger">*</span>
            </label>
            <?= $form->textArea('description', array(
              'class' => 'form-control form-control-lg input-focus', 
              'placeholder' => 'Enter a detailed description of the service...',
              'rows' => 4
            )); ?>
            <small class="form-text text-muted">
              <i class="fas fa-lightbulb"></i> Minimum 10 characters required
            </small>
            <span class="text-danger font-weight-bold"><?= $validator->error('description'); ?></span>
          </div>
          
          <div class="form-group">
            <label class="form-label font-weight-bold">
              <i class="fas fa-image text-primary"></i> Service Image:
            </label>
            <input type="file" name="image" class="form-control-file" accept=".jpg,.jpeg,.png,.gif">
            <small class="form-text text-muted">
              <i class="fas fa-info-circle"></i> Optional. JPG, JPEG, PNG, or GIF format (Max 5MB)
            </small>
          </div>
        </div>
      </div>

      <hr class="my-4">

      <div class="form-group mb-0">
        <button type="submit" name="btn_insert" class="btn btn-primary btn-lg px-5 btn-submit-effect">
          <i class="fas fa-save"></i> Add Service
        </button>
        <a href="viewservices1.php" class="btn btn-outline-secondary btn-lg px-4 ml-2">
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

.bg-gradient-primary {
  background: linear-gradient(135deg, #176B87 0%, #14546c 100%);
}

.input-focus:focus {
  border-color: #176B87;
  box-shadow: 0 0 0 0.2rem rgba(23, 107, 135, 0.25);
}

.btn-submit-effect {
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.btn-submit-effect:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(23, 107, 135, 0.3);
}

.btn-submit-effect::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.3);
  transform: translate(-50%, -50%);
  transition: width 0.6s, height 0.6s;
}

.btn-submit-effect:hover::before {
  width: 300px;
  height: 300px;
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