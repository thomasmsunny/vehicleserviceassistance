<?php 
require('../config/autoload.php');

$page_title = "View Services";
include("includes/admin_header.php");

$dao = new DataAccess();

// Get all services data
$services = $dao->getData('*', 'services', '1');
$services = is_array($services) ? $services : [];

// Get service count
$service_count = count($services);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom animated-header">
  <h1 class="h2">
    <i class="fas fa-list-alt text-info"></i> View Services
    <span class="badge badge-info badge-pill ml-2"><?= $service_count ?></span>
  </h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <a href="servicesoffered1.php" class="btn btn-sm btn-primary btn-pulse">
      <i class="fas fa-plus-circle"></i> Add New Service
    </a>
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-4">
    <div class="info-card card border-left-info shadow-sm">
      <div class="card-body py-3">
        <div class="row align-items-center">
          <div class="col">
            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Services</div>
            <div class="h4 mb-0 font-weight-bold text-gray-800"><?= $service_count ?></div>
          </div>
          <div class="col-auto">
            <i class="fas fa-tools fa-2x text-gray-300"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card shadow-sm border-0 table-card-hover">
  <div class="card-header bg-gradient-info text-white d-flex justify-content-between align-items-center">
    <span><i class="fas fa-table"></i> All Services</span>
    <small><i class="fas fa-info-circle"></i> Manage your service offerings</small>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-striped mb-0 modern-table">
        <thead class="thead-dark">
          <tr>
            <th class="text-center" style="width: 60px;">#</th>
            <th style="width: 100px;">Service ID</th>
            <th><i class="fas fa-cog"></i> Service Name</th>
            <th><i class="fas fa-align-left"></i> Description</th>
            <th class="text-center"><i class="fas fa-image"></i> Image</th>
            <th class="text-center" style="width: 120px;"><i class="fas fa-toggle-on"></i> Status</th>
            <th class="text-center" style="width: 180px;"><i class="fas fa-tasks"></i> Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if($services && count($services) > 0): ?>
            <?php foreach($services as $index => $service): ?>
              <tr>
                <td class="text-center"><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($service['sid']) ?></td>
                <td><?= htmlspecialchars($service['servicename']) ?></td>
                <td>
                    <?php 
                    // Truncate description if too long
                    $description = isset($service['description']) ? $service['description'] : '';
                    if (strlen($description) > 100) {
                        echo htmlspecialchars(substr($description, 0, 100)) . '...';
                    } else {
                        echo htmlspecialchars($description);
                    }
                    ?>
                </td>
                <td class="text-center">
                    <?php if (!empty($service['image'])): ?>
                        <img src="../uploads/services/<?= htmlspecialchars($service['image']) ?>" alt="Service Image" class="img-thumbnail" style="max-height: 50px; max-width: 80px;">
                    <?php else: ?>
                        <span class="text-muted">No image</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                  <?php 
                    $status = isset($service['status']) ? strtolower(trim($service['status'])) : '';
                    if($status == 'active' || $status == '1'): 
                  ?>
                    <span class="badge badge-success">
                      <i class="fas fa-check-circle"></i> Active
                    </span>
                  <?php else: ?>
                    <span class="badge badge-secondary">
                      <i class="fas fa-times-circle"></i> Inactive
                    </span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <a href="editservices1.php?id=<?= $service['sid'] ?>" 
                     class="btn btn-warning btn-sm mr-1" 
                     title="Edit Service">
                    <i class="fas fa-edit"></i> Edit
                  </a>
                  <a href="deleteservices.php?id=<?= $service['sid'] ?>" 
                     class="btn btn-danger btn-sm" 
                     onclick="return confirm('Are you sure you want to delete this service?');"
                     title="Delete Service">
                    <i class="fas fa-trash"></i> Delete
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">
                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                <h5>No Services Found</h5>
                <p>Get started by adding your first service.</p>
                <a href="servicesoffered1.php" class="btn btn-primary btn-sm">
                  <i class="fas fa-plus"></i> Add Service
                </a>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer bg-light text-muted small">
    <i class="fas fa-lightbulb"></i> <strong>Tip:</strong> Click Edit to modify service details or Delete to remove a service.
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

.border-left-info {
  border-left: 4px solid #36b9cc !important;
}

.info-card {
  transition: all 0.3s ease;
  animation: fadeInUp 0.6s ease-out;
}

.info-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.5rem 1.5rem rgba(54, 185, 204, 0.2) !important;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.table-card-hover {
  transition: all 0.3s ease;
  animation: fadeIn 0.7s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.bg-gradient-info {
  background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
}

.modern-table {
  border-collapse: separate;
  border-spacing: 0;
}

.modern-table thead th {
  border: none;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
  padding: 15px 12px;
  vertical-align: middle;
}

.modern-table tbody tr {
  transition: all 0.3s ease;
}

.modern-table tbody tr:hover {
  background-color: rgba(23, 107, 135, 0.05);
  transform: scale(1.01);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.modern-table tbody td {
  padding: 12px;
  vertical-align: middle;
  border-top: 1px solid #e3e6f0;
}

/* Status badges */
.badge {
  padding: 6px 12px;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.badge i {
  margin-right: 4px;
}

/* Action buttons */
.btn-sm {
  padding: 6px 12px;
  font-size: 0.8rem;
  transition: all 0.2s ease;
  font-weight: 500;
}

.btn-sm:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.btn-sm i {
  margin-right: 4px;
}

.btn-pulse {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(23, 107, 135, 0.7);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(23, 107, 135, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(23, 107, 135, 0);
  }
}

.badge-pill {
  animation: bounceIn 0.8s ease-out;
}

@keyframes bounceIn {
  0% {
    transform: scale(0.5);
    opacity: 0;
  }
  50% {
    transform: scale(1.1);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.text-gray-800 {
  color: #5a5c69 !important;
}

.text-gray-300 {
  color: #dddfeb !important;
}

/* Empty state styling */
.text-muted i.fa-inbox {
  color: #ccc;
}

/* Ensure text doesn't wrap in action column */
.modern-table tbody td:last-child {
  white-space: nowrap;
}

/* Image thumbnail styling */
.img-thumbnail {
  border: 1px solid #dee2e6;
  border-radius: 0.25rem;
  padding: 0.25rem;
  background-color: #fff;
}
</style>

<?php include("includes/admin_footer.php"); ?>