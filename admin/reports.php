<?php
require('../config/autoload.php');
$page_title = "Reports & Analytics";
include("includes/admin_header.php");

$dao = new DataAccess();

// Database connection for complex queries
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get total revenue from payments table
$revenue_query = "SELECT SUM(amount_paid) as total FROM payments WHERE payment_status = 'Success'";
$revenue_result = $conn->query($revenue_query);
$total_revenue = $revenue_result->fetch_assoc()['total'] ?? 0;

// Get monthly bookings
$monthly_bookings_query = "SELECT COUNT(*) as count FROM bookings 
                          WHERE MONTH(booking_date) = MONTH(CURRENT_DATE()) 
                          AND YEAR(booking_date) = YEAR(CURRENT_DATE())";
$monthly_bookings_result = $conn->query($monthly_bookings_query);
$monthly_bookings = $monthly_bookings_result->fetch_assoc()['count'] ?? 0;

// Get completed services
$completed_query = "SELECT COUNT(*) as count FROM bookings WHERE status IN ('Payment Done', 'Delivered')";
$completed_result = $conn->query($completed_query);
$completed_services = $completed_result->fetch_assoc()['count'] ?? 0;

// Get service-wise statistics from bookings
$service_stats_query = "SELECT service_type, COUNT(*) as count 
                        FROM bookings 
                        WHERE service_type IS NOT NULL AND service_type != '' 
                        GROUP BY service_type 
                        ORDER BY count DESC";
$service_stats_result = $conn->query($service_stats_query);
$service_stats = [];
if ($service_stats_result && $service_stats_result->num_rows > 0) {
    while ($row = $service_stats_result->fetch_assoc()) {
        $service_stats[] = [
            'service_name' => $row['service_type'],
            'count' => $row['count']
        ];
    }
}

// Get status-wise statistics for another chart if needed
$status_stats_query = "SELECT status, COUNT(*) as count FROM bookings GROUP BY status ORDER BY count DESC";
$status_stats_result = $conn->query($status_stats_query);
$status_stats = [];
if ($status_stats_result && $status_stats_result->num_rows > 0) {
    while ($row = $status_stats_result->fetch_assoc()) {
        $status_stats[] = [
            'status_name' => $row['status'],
            'count' => $row['count']
        ];
    }
}

// Get monthly revenue (last 6 months)
$monthly_revenue = [];
for($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $revenue_month_query = "SELECT SUM(amount_paid) as total FROM payments 
                           WHERE DATE_FORMAT(payment_date, '%Y-%m') = '$month' 
                           AND payment_status = 'Success'";
    $revenue_month_result = $conn->query($revenue_month_query);
    $revenue_data = $revenue_month_result->fetch_assoc();
    $monthly_revenue[] = [
        'month' => date('M Y', strtotime("-$i months")),
        'revenue' => $revenue_data['total'] ?? 0
    ];
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h1 class="h2"><i class="fas fa-chart-bar"></i> Reports & Analytics</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
      <i class="fas fa-print"></i> Print Report
    </button>
  </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
  <div class="col-md-4">
    <div class="card border-left-success">
      <div class="card-body">
        <h6 class="text-success">Total Revenue</h6>
        <h3>₹<?= number_format($total_revenue, 2) ?></h3>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-left-info">
      <div class="card-body">
        <h6 class="text-info">Monthly Bookings</h6>
        <h3><?= $monthly_bookings ?></h3>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-left-primary">
      <div class="card-body">
        <h6 class="text-primary">Completed Services</h6>
        <h3><?= $completed_services ?></h3>
      </div>
    </div>
  </div>
</div>

<!-- Charts Section -->
<div class="row">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <i class="fas fa-chart-line"></i> Monthly Revenue Trend
      </div>
      <div class="card-body">
        <canvas id="revenueChart" style="height: 300px;"></canvas>
      </div>
    </div>
  </div>
  
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">
        <i class="fas fa-chart-pie"></i> Service Distribution
      </div>
      <div class="card-body">
        <canvas id="serviceChart" style="height: 300px;"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Service Statistics Table -->
<div class="row mt-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <i class="fas fa-table"></i> Service-wise Performance
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead class="thead-light">
              <tr>
                <th>#</th>
                <th>Service Type</th>
                <th>Total Bookings</th>
                <th>Percentage</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              // Safely handle service_stats to prevent array_column errors
              if($service_stats && is_array($service_stats) && count($service_stats) > 0):
                $total_bookings = array_sum(array_column($service_stats, 'count'));
                foreach($service_stats as $index => $stat): 
                  $percentage = $total_bookings > 0 ? ($stat['count'] / $total_bookings) * 100 : 0;
              ?>
                <tr>
                  <td><?= $index + 1 ?></td>
                  <td><?= htmlspecialchars($stat['service_name']) ?></td>
                  <td><?= $stat['count'] ?></td>
                  <td>
                    <div class="progress">
                      <div class="progress-bar bg-info" role="progressbar" 
                           style="width: <?= $percentage ?>%" 
                           aria-valuenow="<?= $percentage ?>" 
                           aria-valuemin="0" aria-valuemax="100">
                        <?= number_format($percentage, 1) ?>%
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endforeach; else: ?>
                <tr>
                  <td colspan="4" class="text-center">No data available</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Chart.js Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($monthly_revenue, 'month')) ?>,
        datasets: [{
            label: 'Revenue (₹)',
            data: <?= json_encode(array_column($monthly_revenue, 'revenue')) ?>,
            borderColor: 'rgb(23, 107, 135)',
            backgroundColor: 'rgba(23, 107, 135, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₹' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Service Distribution Chart
const serviceCtx = document.getElementById('serviceChart').getContext('2d');
const serviceChart = new Chart(serviceCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($service_stats && is_array($service_stats) ? array_column($service_stats, 'service_name') : []) ?>,
        datasets: [{
            data: <?= json_encode($service_stats && is_array($service_stats) ? array_column($service_stats, 'count') : []) ?>,
            backgroundColor: [
                'rgba(23, 107, 135, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});
</script>

<?php include("includes/admin_footer.php"); ?>
