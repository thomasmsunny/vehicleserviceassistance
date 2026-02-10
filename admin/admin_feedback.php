<?php
session_start();
require('../config/autoload.php');

// Check if admin is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Set page title
$page_title = "Customer Feedback";

// Database connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle filters
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$rating_filter = isset($_GET['rating']) ? $_GET['rating'] : '';

// Fetch feedback with customer info
$sql = "SELECT f.feedback_id, 
               CONCAT(c.firstname, ' ', c.lastname) AS customer_name,
               c.phone,
               f.rating, 
               f.feedback_text, 
               f.created_at
        FROM feedback f
        JOIN customerreg c ON f.customer_id = c.customer_id
        WHERE 1=1";

$conditions = [];

// Search filter
if ($search_query) {
    $search_escaped = $conn->real_escape_string($search_query);
    $conditions[] = "(CONCAT(c.firstname, ' ', c.lastname) LIKE '%$search_escaped%' 
                     OR f.feedback_text LIKE '%$search_escaped%')";
}

// Rating filter
if ($rating_filter) {
    $rating_escaped = $conn->real_escape_string($rating_filter);
    $conditions[] = "f.rating = $rating_escaped";
}

if (count($conditions) > 0) {
    $sql .= " AND " . implode(' AND ', $conditions);
}

$sql .= " ORDER BY f.created_at DESC";

$result = $conn->query($sql);

// Calculate statistics
$total_feedback = $result->num_rows;
$avg_rating = 0;
$rating_counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

if ($result->num_rows > 0) {
    $result->data_seek(0);
    $total_ratings = 0;
    while ($row = $result->fetch_assoc()) {
        $total_ratings += $row['rating'];
        $rating_counts[$row['rating']]++;
    }
    $avg_rating = $total_feedback > 0 ? round($total_ratings / $total_feedback, 1) : 0;
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
    
    .stat-box.total { border-left-color: #36b9cc; }
    .stat-box.average { border-left-color: #f6c23e; }
    .stat-box.excellent { border-left-color: #1cc88a; }
    .stat-box.poor { border-left-color: #e74a3b; }
    
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
    
    .filter-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    
    .feedback-table {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    
    .feedback-table table {
        table-layout: auto;
        width: 100%;
    }
    
    .feedback-table th,
    .feedback-table td {
        vertical-align: middle !important;
        padding: 12px 8px;
    }
    
    .feedback-table thead th {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #dee2e6;
    }
    
    .feedback-table tbody tr:hover {
        background-color: #f8f9fc;
    }
    
    .rating-stars {
        color: #FFC000;
        font-size: 1.1em;
    }
    
    .feedback-text {
        max-width: 400px;
        text-align: left;
        line-height: 1.5;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <h2><i class="fas fa-comments"></i> Customer Feedback</h2>
    <p class="mb-0">View and manage customer reviews and ratings</p>
</div>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-box total">
            <div class="stat-value"><?= $total_feedback ?></div>
            <div class="stat-label"><i class="fas fa-comment-dots"></i> Total Feedback</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-box average">
            <div class="stat-value"><?= $avg_rating ?> <i class="fas fa-star" style="font-size: 0.6em; color: #FFC000;"></i></div>
            <div class="stat-label"><i class="fas fa-chart-line"></i> Average Rating</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-box excellent">
            <div class="stat-value"><?= $rating_counts[5] + $rating_counts[4] ?></div>
            <div class="stat-label"><i class="fas fa-thumbs-up"></i> 4-5 Star Reviews</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-box poor">
            <div class="stat-value"><?= $rating_counts[1] + $rating_counts[2] ?></div>
            <div class="stat-label"><i class="fas fa-thumbs-down"></i> 1-2 Star Reviews</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="filter-card">
    <h5 class="mb-3"><i class="fas fa-filter"></i> Filter Feedback</h5>
    <form method="get" class="form-row">
        <!-- Search -->
        <div class="col-md-6 mb-2">
            <label><i class="fas fa-search"></i> Search</label>
            <input type="text" name="search" class="form-control" placeholder="Customer name or feedback text..." value="<?= htmlspecialchars($search_query) ?>">
        </div>
        
        <!-- Rating Filter -->
        <div class="col-md-3 mb-2">
            <label><i class="fas fa-star"></i> Filter by Rating</label>
            <select name="rating" class="form-control">
                <option value="">All Ratings</option>
                <option value="5" <?= $rating_filter == '5' ? 'selected' : '' ?>>5 Stars</option>
                <option value="4" <?= $rating_filter == '4' ? 'selected' : '' ?>>4 Stars</option>
                <option value="3" <?= $rating_filter == '3' ? 'selected' : '' ?>>3 Stars</option>
                <option value="2" <?= $rating_filter == '2' ? 'selected' : '' ?>>2 Stars</option>
                <option value="1" <?= $rating_filter == '1' ? 'selected' : '' ?>>1 Star</option>
            </select>
        </div>
        
        <!-- Buttons -->
        <div class="col-md-3 mb-2">
            <label>&nbsp;</label>
            <div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply</button>
                <a href="admin_feedback.php" class="btn btn-secondary"><i class="fas fa-times"></i> Clear</a>
            </div>
        </div>
    </form>
</div>


<!-- Feedback Table -->
<div class="feedback-table">
    <div class="card">
        <div class="card-header" style="background: #176B87; color: white;">
            <h5 class="mb-0"><i class="fas fa-list"></i> Feedback Records</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0">
                    <thead style="background: #f8f9fc;">
                        <tr>
                            <th class="text-center" style="width: 8%;">ID</th>
                            <th style="width: 18%;">Customer</th>
                            <th class="text-center" style="width: 12%;">Rating</th>
                            <th style="width: 45%;">Feedback</th>
                            <th class="text-center" style="width: 17%;">Submitted At</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center"><strong>#<?= $row['feedback_id'] ?></strong></td>
                            <td>
                                <div><strong><?= htmlspecialchars($row['customer_name']) ?></strong></div>
                                <small class="text-muted"><i class="fas fa-phone"></i> <?= htmlspecialchars($row['phone']) ?></small>
                            </td>
                            <td class="text-center">
                                <div class="rating-stars">
                                    <?php for($i=0; $i<$row['rating']; $i++): ?>
                                        <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                    <?php for($i=$row['rating']; $i<5; $i++): ?>
                                        <i class="far fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                                <small class="text-muted"><?= $row['rating'] ?>/5</small>
                            </td>
                            <td>
                                <div class="feedback-text">
                                    <?= htmlspecialchars($row['feedback_text']) ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <div><strong><?= date('d M Y', strtotime($row['created_at'])) ?></strong></div>
                                <small class="text-muted"><?= date('h:i A', strtotime($row['created_at'])) ?></small>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>No feedback found</p>
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
