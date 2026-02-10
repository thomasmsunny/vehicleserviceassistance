<?php 
require('../config/autoload.php');

$page_title = "Edit Service";
include("includes/admin_header.php");

$dao = new DataAccess();

// Check if ID is provided
if (!isset($_GET['id'])) {
    echo "<script>alert('Invalid service ID'); window.location.href='viewservices1.php';</script>";
    exit();
}

$service_id = $_GET['id'];
$info = $dao->getData('*', 'services', 'sid=' . $service_id);

// Check if service exists
if (!$info || count($info) == 0) {
    echo "<script>alert('Service not found'); window.location.href='viewservices1.php';</script>";
    exit();
}

$file = new FileUpload();
$current_status = isset($info[0]['status']) ? $info[0]['status'] : 'active';
$elements = array(
    "servicename" => $info[0]['servicename'],
    "description" => isset($info[0]['description']) ? $info[0]['description'] : ""
);

$form = new FormAssist($elements, $_POST);

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

if (isset($_POST["btn_update"])) {
    if ($validator->validate($_POST)) {
        // Handle file upload
        $image_name = $info[0]['image']; // Keep existing image by default
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_dir = "../uploads/services/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
            
            if (in_array(strtolower($file_extension), $allowed_extensions)) {
                // Delete old image if it exists
                if (!empty($image_name) && file_exists($upload_dir . $image_name)) {
                    unlink($upload_dir . $image_name);
                }
                
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
                'status' => isset($_POST['status']) ? $_POST['status'] : 'active'
            );
            
            if ($dao->update($data, "services", "sid=" . $service_id)) {
                echo "<script>alert('Service updated successfully!'); window.location.href='viewservices1.php';</script>";
                exit();
            } else {
                $error = $dao->getErrors();
                $msg = "<div class='alert alert-danger'><strong>Update failed!</strong><br>" . ($error ? $error : "Please try again.") . "</div>";
            }
        }
    } else {
        $msg = "<div class='alert alert-danger'>Please fix the validation errors.</div>";
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h1 class="h2"><i class="fas fa-edit"></i> Edit Service</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <a href="viewservices1.php" class="btn btn-sm btn-outline-secondary">
      <i class="fas fa-arrow-left"></i> Back to List
    </a>
  </div>
</div>

<?php if (!empty($msg)) echo $msg; ?>

<div class="card">
  <div class="card-header bg-gradient-primary text-white">
    <i class="fas fa-tools"></i> Service Details
  </div>
  <div class="card-body p-4">
    <form action="" method="POST" enctype="multipart/form-data">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label font-weight-bold">
              <i class="fas fa-cog text-primary"></i> Service Name: 
              <span class="text-danger">*</span>
            </label>
            <?= $form->textBox('servicename', array('class' => 'form-control form-control-lg', 'placeholder' => 'Enter service name')); ?>
            <span class="text-danger font-weight-bold"><?= $validator->error('servicename'); ?></span>
          </div>
          
          <div class="form-group">
            <label class="form-label font-weight-bold">
              <i class="fas fa-align-left text-primary"></i> Service Description: 
              <span class="text-danger">*</span>
            </label>
            <?= $form->textArea('description', array(
              'class' => 'form-control form-control-lg', 
              'placeholder' => 'Enter a detailed description of the service...',
              'rows' => 4
            )); ?>
            <span class="text-danger font-weight-bold"><?= $validator->error('description'); ?></span>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label font-weight-bold">
              <i class="fas fa-toggle-on text-primary"></i> Status:
            </label>
            <select name="status" class="form-control form-control-lg">
              <option value="active" <?= $current_status == 'active' ? 'selected' : '' ?>>
                ✓ Active
              </option>
              <option value="inactive" <?= $current_status == 'inactive' ? 'selected' : '' ?>>
                ✗ Inactive
              </option>
            </select>
            <small class="form-text text-muted">
              <i class="fas fa-info-circle"></i> Set to Active to make this service available
            </small>
          </div>
          
          <div class="form-group">
            <label class="form-label font-weight-bold">
              <i class="fas fa-image text-primary"></i> Service Image:
            </label>
            <?php if (!empty($info[0]['image'])): ?>
              <div class="mb-2">
                <img src="../uploads/services/<?= htmlspecialchars($info[0]['image']) ?>" alt="Service Image" class="img-thumbnail" style="max-height: 150px;">
              </div>
            <?php endif; ?>
            <input type="file" name="image" class="form-control-file" accept=".jpg,.jpeg,.png,.gif">
            <small class="form-text text-muted">
              <i class="fas fa-info-circle"></i> JPG, JPEG, PNG, or GIF format (Max 5MB). Leave empty to keep current image.
            </small>
          </div>
        </div>
      </div>

      <hr class="my-4">

      <div class="form-group mb-0">
        <button type="submit" name="btn_update" class="btn btn-primary btn-lg px-5">
          <i class="fas fa-save"></i> Update Service
        </button>
        <a href="viewservices1.php" class="btn btn-outline-secondary btn-lg px-4 ml-2">
          <i class="fas fa-times"></i> Cancel
        </a>
      </div>
    </form>
  </div>
</div>

<style>
.bg-gradient-primary {
  background: linear-gradient(135deg, #176B87 0%, #14546c 100%);
}
</style>

<?php include("includes/admin_footer.php"); ?>