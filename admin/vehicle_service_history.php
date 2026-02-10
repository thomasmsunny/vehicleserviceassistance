<?php
require('../config/autoload.php');
$page_title = "Vehicle Service History";
include("includes/admin_header.php");

$dao = new DataAccess();

// Database connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Filter variables
$filter_customer = isset($_GET['customer']) ? $_GET['customer'] : '';
$filter_service = isset($_GET['service']) ? $_GET['service'] : '';
$filter_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filter_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query with filters
$where_conditions = [];
$params = [];
$types = '';

if (!empty($filter_customer)) {
    $where_conditions[] = "(c.firstname LIKE ? OR c.lastname LIKE ? OR c.email LIKE ?)";
    $search_term = "%$filter_customer%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'sss';
}

if (!empty($filter_service)) {
    $where_conditions[] = "s.servicename LIKE ?";
    $params[] = "%$filter_service%";
    $types .= 's';
}

if (!empty($filter_date_from)) {
    $where_conditions[] = "b.booking_date >= ?";
    $params[] = $filter_date_from;
    $types .= 's';
}

if (!empty($filter_date_to)) {
    $where_conditions[] = "b.booking_date <= ?";
    $params[] = $filter_date_to;
    $types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Query to fetch all service history
$sql = "SELECT b.booking_id, 
               CONCAT(c.firstname, ' ', c.lastname) AS customer_name,
               c.email AS customer_email,
               c.phone AS customer_phone,
               b.vehicle_make, 
               b.vehicle_model, 
               b.vehicle_number,
               s.servicename AS service_type,
               b.booking_date,
               b.status,
               CONCAT(d.drivername) AS driver_name,
               p.amount_paid
        FROM bookings b
        LEFT JOIN customerreg c ON b.customer_id = c.customer_id
        LEFT JOIN services s ON b.service_type = s.servicename
        LEFT JOIN drivermanage d ON b.driver_id = d.did
        LEFT JOIN payments p ON b.booking_id = p.booking_id
        $where_clause
        ORDER BY b.booking_date DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

$services = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}

// Get distinct services for filter dropdown
$service_list_query = "SELECT DISTINCT servicename FROM services ORDER BY servicename";
$service_list_result = $conn->query($service_list_query);
$service_options = [];
if ($service_list_result) {
    while ($row = $service_list_result->fetch_assoc()) {
        $service_options[] = $row['servicename'];
    }
}

$conn->close();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h1 class="h2"><i class="fas fa-history"></i> Vehicle Service History</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
      <i class="fas fa-print"></i> Print Report
    </button>
    <button type="button" class="btn btn-sm btn-success ml-2" onclick="exportToCSV()">
      <i class="fas fa-file-excel"></i> Export CSV
    </button>
  </div>
</div>

<!-- Filter Section -->
<div class="card mb-4">
  <div class="card-header">
    <i class="fas fa-filter"></i> Filter Service History
  </div>
  <div class="card-body">
    <form method="GET" action="" class="form-inline">
      <div class="form-group mr-3 mb-2">
        <label for="customer" class="mr-2">Customer:</label>
        <input type="text" class="form-control" id="customer" name="customer" 
               placeholder="Name or Email" value="<?= htmlspecialchars($filter_customer) ?>">
      </div>
      
      <div class="form-group mr-3 mb-2">
        <label for="service" class="mr-2">Service:</label>
        <select class="form-control" id="service" name="service">
          <option value="">All Services</option>
          <?php foreach ($service_options as $service): ?>
            <option value="<?= htmlspecialchars($service) ?>" 
                    <?= $filter_service == $service ? 'selected' : '' ?>>
              <?= htmlspecialchars($service) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div class="form-group mr-3 mb-2">
        <label for="date_from" class="mr-2">From:</label>
        <input type="date" class="form-control" id="date_from" name="date_from" 
               value="<?= htmlspecialchars($filter_date_from) ?>">
      </div>
      
      <div class="form-group mr-3 mb-2">
        <label for="date_to" class="mr-2">To:</label>
        <input type="date" class="form-control" id="date_to" name="date_to" 
               value="<?= htmlspecialchars($filter_date_to) ?>">
      </div>
      
      <button type="submit" class="btn btn-primary mb-2 mr-2">
        <i class="fas fa-search"></i> Filter
      </button>
      <a href="vehicle_service_history.php" class="btn btn-secondary mb-2">
        <i class="fas fa-redo"></i> Reset
      </a>
    </form>
  </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
  <div class="col-md-4">
    <div class="card border-left-primary">
      <div class="card-body">
        <h6 class="text-primary">Total Services</h6>
        <h3><?= count($services) ?></h3>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-left-success">
      <div class="card-body">
        <h6 class="text-success">Total Revenue</h6>
        <h3>₹<?= number_format(array_sum(array_column($services, 'amount_paid')), 2) ?></h3>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-left-info">
      <div class="card-body">
        <h6 class="text-info">Unique Customers</h6>
        <h3><?= count(array_unique(array_column($services, 'customer_email'))) ?></h3>
      </div>
    </div>
  </div>
</div>

<!-- Service History Table -->
<div class="card">
  <div class="card-header">
    <i class="fas fa-table"></i> Service Records
  </div>
  <div class="card-body">
    <?php if (count($services) > 0): ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover" id="serviceHistoryTable">
          <thead class="thead-light">
            <tr>
              <th style="width: 8%;">Booking ID</th>
              <th style="width: 12%;">Date</th>
              <th style="width: 15%;">Customer</th>
              <th style="width: 12%;">Contact</th>
              <th style="width: 15%;">Vehicle</th>
              <th style="width: 10%;">Service Type</th>
              <th style="width: 10%;">Driver</th>
              <th style="width: 10%;">Amount</th>
              <th style="width: 8%;">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($services as $row): ?>
              <tr>
                <td>#<?= htmlspecialchars($row['booking_id']) ?></td>
                <td><?= date("d-M-Y", strtotime($row['booking_date'])) ?></td>
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td><?= htmlspecialchars($row['customer_phone']) ?></td>
                <td>
                  <?= htmlspecialchars($row['vehicle_make'] . ' ' . $row['vehicle_model']) ?><br>
                  <small class="text-muted"><?= htmlspecialchars($row['vehicle_number']) ?></small>
                </td>
                <td><?= htmlspecialchars($row['service_type'] ?: 'N/A') ?></td>
                <td><?= htmlspecialchars($row['driver_name'] ?: 'Unassigned') ?></td>
                <td>₹<?= $row['amount_paid'] ? number_format($row['amount_paid'], 2) : '0.00' ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-info text-center">
        <i class="fas fa-info-circle fa-2x mb-3"></i>
        <p class="mb-0">No service history records found. Try adjusting your filters.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    $('#serviceHistoryTable').DataTable({
        "pageLength": 25,
        "order": [[1, "desc"]], // Sort by date descending
        "language": {
            "search": "Search records:",
            "lengthMenu": "Show _MENU_ records per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ records",
            "infoEmpty": "No records available",
            "infoFiltered": "(filtered from _MAX_ total records)"
        }
    });
});

function exportToCSV() {
    var csv = [];
    var rows = document.querySelectorAll("#serviceHistoryTable tr");
    
    for (var i = 0; i < rows.length; i++) {
        var row = [], cols = rows[i].querySelectorAll("td, th");
        
        for (var j = 0; j < cols.length; j++) {
            var cellText = cols[j].innerText.replace(/"/g, '""');
            row.push('"' + cellText + '"');
        }
        
        csv.push(row.join(","));
    }
    
    var csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
    var downloadLink = document.createElement("a");
    downloadLink.download = "service_history_" + new Date().toISOString().split('T')[0] + ".csv";
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>

<?php include("includes/admin_footer.php"); ?>
