<?php
session_start();
require('../config/autoload.php');

// Only admin can access
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Set page title
$page_title = "Transaction Management";

// Database connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle filters
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build SQL query with filters
$sql = "SELECT 
            b.booking_id,
            b.status,
            b.quotation as quote_flag,
            b.booking_date,
            b.vehicle_make,
            b.vehicle_model,
            b.vehicle_number,
            CONCAT(c.firstname, ' ', c.lastname) as customer_name,
            c.email,
            c.phone,
            q.grand_total as quotation_amount,
            p.amount_paid,
            p.payment_method,
            p.payment_date
        FROM bookings b
        JOIN customerreg c ON b.customer_id = c.customer_id
        LEFT JOIN quotations q ON b.booking_id = q.booking_id
        LEFT JOIN payments p ON b.booking_id = p.booking_id AND p.payment_status = 'Success'
        WHERE 1=1";

$conditions = [];

// Filter by payment status
if ($filter_status) {
    $filter_status_escaped = $conn->real_escape_string($filter_status);
    $conditions[] = "b.status = '$filter_status_escaped'";
}

// Search by booking ID, customer name, or vehicle number
if ($search_query) {
    $search_escaped = $conn->real_escape_string($search_query);
    $conditions[] = "(b.booking_id LIKE '%$search_escaped%' 
                     OR CONCAT(c.firstname, ' ', c.lastname) LIKE '%$search_escaped%'
                     OR b.vehicle_number LIKE '%$search_escaped%')";
}

// Date range filter
if ($date_from) {
    $date_from_escaped = $conn->real_escape_string($date_from);
    $conditions[] = "b.booking_date >= '$date_from_escaped'";
}

if ($date_to) {
    $date_to_escaped = $conn->real_escape_string($date_to);
    $conditions[] = "b.booking_date <= '$date_to_escaped'";
}

if (count($conditions) > 0) {
    $sql .= " AND " . implode(' AND ', $conditions);
}

$sql .= " ORDER BY b.booking_date DESC, b.booking_id DESC";

$result = $conn->query($sql);

// Calculate summary statistics
$total_revenue = 0;
$pending_payments = 0;
$completed_payments = 0;
$total_transactions = $result ? $result->num_rows : 0;

if ($result && $result->num_rows > 0) {
    $result->data_seek(0);
    while ($row = $result->fetch_assoc()) {
        if ($row['quotation_amount'] && $row['quotation_amount'] > 0) {
            $total_revenue += $row['quotation_amount'];
            
            // Use actual payment amount if exists
            if ($row['amount_paid'] && $row['amount_paid'] > 0) {
                $completed_payments += $row['amount_paid'];
            } elseif ($row['status'] == 'Pay Now') {
                $pending_payments += $row['quotation_amount'];
            }
        }
    }
    $result->data_seek(0);
}

include("includes/admin_header.php");
?>

<style>
    .page-header {
        background: linear-gradient(135deg, #176B87 0%, #1cc88a 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 5px 15px rgba(23, 107, 135, 0.3);
    }
    
    .stat-box {
        background: white;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        border-left: 4px solid;
    }
    
    .stat-box.revenue { border-left-color: #1cc88a; }
    .stat-box.pending { border-left-color: #f6c23e; }
    .stat-box.completed { border-left-color: #4e73df; }
    .stat-box.total { border-left-color: #36b9cc; }
    
    .stat-value {
        font-size: 2em;
        font-weight: bold;
        color: #2c3e50;
    }
    
    .stat-label {
        color: #7f8c8d;
        font-size: 0.9em;
        text-transform: uppercase;
    }
    
    .transaction-table {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    
    .filter-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    
    .amount-paid {
        color: #1cc88a;
        font-weight: bold;
    }
    
    .amount-pending {
        color: #f6c23e;
        font-weight: bold;
    }
    
    .amount-none {
        color: #95a5a6;
    }
    
    /* Table Alignment Fixes */
    .transaction-table table {
        table-layout: fixed;
        width: 100%;
    }
    
    .transaction-table th,
    .transaction-table td {
        vertical-align: middle !important;
        padding: 12px 8px;
    }
    
    .transaction-table thead th {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #dee2e6;
    }
    
    .transaction-table tbody tr:hover {
        background-color: #f8f9fc;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <h2><i class="fas fa-money-bill-wave"></i> Transaction Management</h2>
    <p class="mb-0">Monitor and manage all payment transactions</p>
</div>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-box revenue">
            <div class="stat-value">₹<?= number_format($total_revenue, 2) ?></div>
            <div class="stat-label"><i class="fas fa-chart-line"></i> Total Revenue</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-box pending">
            <div class="stat-value">₹<?= number_format($pending_payments, 2) ?></div>
            <div class="stat-label"><i class="fas fa-clock"></i> Pending Payments</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-box completed">
            <div class="stat-value">₹<?= number_format($completed_payments, 2) ?></div>
            <div class="stat-label"><i class="fas fa-check-circle"></i> Completed Payments</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-box total">
            <div class="stat-value"><?= $total_transactions ?></div>
            <div class="stat-label"><i class="fas fa-receipt"></i> Total Transactions</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="filter-card">
    <h5 class="mb-3"><i class="fas fa-filter"></i> Filter Transactions</h5>
    <form method="get" class="form-row">
        <!-- Search -->
        <div class="col-md-3 mb-2">
            <label><i class="fas fa-search"></i> Search</label>
            <input type="text" name="search" class="form-control" placeholder="Booking ID, Customer, Vehicle..." value="<?= htmlspecialchars($search_query) ?>">
        </div>
        
        <!-- Status Filter -->
        <div class="col-md-2 mb-2">
            <label><i class="fas fa-flag"></i> Payment Status</label>
            <select name="filter_status" class="form-control">
                <option value="">All Statuses</option>
                <option value="Pay Now" <?= $filter_status == 'Pay Now' ? 'selected' : '' ?>>Pay Now</option>
                <option value="Payment Done" <?= $filter_status == 'Payment Done' ? 'selected' : '' ?>>Payment Done</option>
                <option value="Delivered" <?= $filter_status == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
            </select>
        </div>
        
        <!-- Date From -->
        <div class="col-md-2 mb-2">
            <label><i class="fas fa-calendar"></i> Date From</label>
            <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($date_from) ?>">
        </div>
        
        <!-- Date To -->
        <div class="col-md-2 mb-2">
            <label><i class="fas fa-calendar"></i> Date To</label>
            <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($date_to) ?>">
        </div>
        
        <!-- Buttons -->
        <div class="col-md-3 mb-2">
            <label>&nbsp;</label>
            <div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply</button>
                <a href="view_transactions.php" class="btn btn-secondary"><i class="fas fa-times"></i> Clear</a>
            </div>
        </div>
    </form>
</div>

<!-- Transactions Table -->
<div class="transaction-table">
    <div class="card">
        <div class="card-header" style="background: #176B87; color: white;">
            <h5 class="mb-0"><i class="fas fa-list"></i> Transaction Records</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0">
                    <thead style="background: #f8f9fc;">
                        <tr>
                            <th class="text-center" style="width: 10%;">Booking ID</th>
                            <th style="width: 12%;">Date</th>
                            <th style="width: 18%;">Customer</th>
                            <th style="width: 18%;">Vehicle</th>
                            <th class="text-center" style="width: 15%;">Service Status</th>
                            <th class="text-right" style="width: 12%;">Quoted Amount</th>
                            <th class="text-right" style="width: 12%;">Paid Amount</th>
                            <th class="text-center" style="width: 8%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center"><strong>#<?= $row['booking_id'] ?></strong></td>
                                <td><?= date('d M Y', strtotime($row['booking_date'])) ?></td>
                                <td>
                                    <div><strong><?= htmlspecialchars($row['customer_name']) ?></strong></div>
                                    <small class="text-muted"><i class="fas fa-phone"></i> <?= htmlspecialchars($row['phone']) ?></small>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($row['vehicle_make'] . ' ' . $row['vehicle_model']) ?></div>
                                    <small class="text-muted"><i class="fas fa-hashtag"></i> <?= htmlspecialchars($row['vehicle_number']) ?></small>
                                </td>
                                <!-- Service Status -->
                                <td class="text-center">
                                    <strong><?= htmlspecialchars($row['status']) ?></strong>
                                </td>
                                <!-- Quoted Amount -->
                                <td class="text-right">
                                    <?php if ($row['quotation_amount'] && $row['quotation_amount'] > 0): ?>
                                        <strong style="font-size: 1.1em;">
                                            ₹<?= number_format($row['quotation_amount'], 2) ?>
                                        </strong>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <!-- Paid Amount -->
                                <td class="text-right">
                                    <?php if ($row['amount_paid'] && $row['amount_paid'] > 0): ?>
                                        <strong style="font-size: 1.1em;">
                                            ₹<?= number_format($row['amount_paid'], 2) ?>
                                        </strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($row['payment_method'] ?? '') ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">₹0.00</span>
                                    <?php endif; ?>
                                </td>
                                <!-- Action -->
                                <td class="text-center">
                                    <a href="update_status.php?booking_id=<?= $row['booking_id'] ?>" class="btn btn-sm btn-primary" title="Update Status">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>No transactions found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
include("includes/admin_footer.php");
$conn->close();
?>
