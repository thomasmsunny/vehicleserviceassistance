<?php
session_start();
require('../config/autoload.php');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Function to format vehicle numbers
if (!function_exists('formatVehicleNo')) {
    function formatVehicleNo($vn) {
        $vn = strtoupper(preg_replace("/[^A-Z0-9]/", "", $vn));
        if (preg_match("/^([A-Z]{2})(\d{2})([A-Z]{1,2})(\d{4})$/", $vn, $m)) {
            return $m[1] . '-' . $m[2] . '-' . $m[3] . '-' . $m[4];
        }
        return $vn;
    }
}

// Fetch all bookings and customer details
$sql = "SELECT b.*, c.firstname, c.lastname, c.email, c.phone, 
                d.drivername 
        FROM bookings b 
        JOIN customerreg c ON b.customer_id = c.customer_id
        LEFT JOIN drivermanage d ON b.driver_id = d.did
        ORDER BY b.booking_date DESC";

$result = $conn->query($sql);

// Fetch all drivers for the assign modal
$drivers = $conn->query("SELECT did, drivername FROM drivermanage ORDER BY drivername ASC");

// Fetch all bookings into an array for JSON
$bookings_data = [];
if ($result && $result->num_rows > 0) {
    $result->data_seek(0); 
    while ($row = $result->fetch_assoc()) {
        $row['vehicle_number_formatted'] = formatVehicleNo($row['vehicle_number']);
        $bookings_data[] = $row;
    }
}
if ($result) $result->data_seek(0); 

include("includes/admin_header.php");
?>

<style>
    /* Page Header with Gradient */
    .page-header {
        background: linear-gradient(135deg, #176B87 0%, #1cc88a 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 5px 15px rgba(23, 107, 135, 0.3);
        animation: slideDown 0.5s ease-out;
    }
    
    .page-header h2 {
        margin: 0;
        font-size: 2em;
        font-weight: 600;
    }
    
    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-top: 10px;
        font-size: 0.9em;
    }
    
    .breadcrumb-item a {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
    }
    
    .breadcrumb-item.active {
        color: white;
    }
    
    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideIn {
        from { transform: translateX(-20px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    /* Filter Controls */
    .filter-controls {
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        padding: 20px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        animation: slideDown 0.6s ease-out;
    }

    .search-box {
        flex-grow: 1;
        display: flex;
        align-items: center;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 0 15px;
        background: #f8f9fa;
        transition: all 0.3s;
    }
    
    .search-box:focus-within {
        border-color: #176B87;
        background: #fff;
        box-shadow: 0 0 0 0.2rem rgba(23, 107, 135, 0.15);
    }

    .search-box i {
        color: #176B87;
        margin-right: 12px;
        font-size: 1.1em;
    }

    #searchInput {
        border: none;
        padding: 12px 0;
        width: 100%;
        font-size: 16px;
        background: transparent;
    }
    
    #searchInput:focus {
        outline: none;
    }

    .filter-box {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .filter-box label {
        font-weight: 600;
        color: #555;
        white-space: nowrap;
    }

    #statusFilter {
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 16px;
        cursor: pointer;
        background-color: #fff;
        min-width: 180px;
        transition: all 0.3s;
    }
    
    #statusFilter:focus {
        border-color: #176B87;
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(23, 107, 135, 0.15);
    }
    
    /* Booking Card Grid */
    .booking-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
        animation: fadeIn 0.6s ease-out;
    }

    /* Booking Cards */
    .booking-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        padding: 25px;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        border-left: 5px solid #176B87;
        animation: slideIn 0.5s ease-out;
    }
    
    .booking-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(23, 107, 135, 0.2);
        border-left-color: #1cc88a;
    }
    
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
        padding-bottom: 12px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .card-header h4 {
        margin: 0;
        font-size: 1.2em;
        font-weight: 600;
        color: #000;
    }
    
    .card-header h4 i {
        color: #176B87;
        margin-right: 8px;
    }
    
    .card-detail {
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        font-size: 0.95em;
        padding: 5px 0;
    }
    
    .card-detail strong {
        color: #555;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .card-detail strong i {
        color: #176B87;
        width: 16px;
    }
    
    .card-detail span {
        font-weight: 500;
        color: #333;
        text-align: right;
    }

    /* Status Badges */
    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75em;
        font-weight: 700;
        text-transform: uppercase;
        white-space: nowrap;
        letter-spacing: 0.5px;
    }
    
    /* All possible status values */
    .status-Pending { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
    .status-pending { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
    
    .status-Quoted { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
    .status-quoted { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
    
    .status-PayNow { background: #f8d7da; color: #721c24; border: 1px solid #dc3545; }
    .status-paynow { background: #f8d7da; color: #721c24; border: 1px solid #dc3545; }
    .status-PaymentPending { background: #f8d7da; color: #721c24; border: 1px solid #dc3545; }
    
    .status-PayDone { background: #e2d9f3; color: #4a148c; border: 1px solid #9c27b0; }
    .status-paydone { background: #e2d9f3; color: #4a148c; border: 1px solid #9c27b0; }
    .status-Paid { background: #e2d9f3; color: #4a148c; border: 1px solid #9c27b0; }
    .status-paid { background: #e2d9f3; color: #4a148c; border: 1px solid #9c27b0; }
    
    .status-InProgress { background: #d1ecf1; color: #0c5460; border: 1px solid #17a2b8; }
    .status-inprogress { background: #d1ecf1; color: #0c5460; border: 1px solid #17a2b8; }
    .status-Processing { background: #d1ecf1; color: #0c5460; border: 1px solid #17a2b8; }
    .status-processing { background: #d1ecf1; color: #0c5460; border: 1px solid #17a2b8; }
    
    .status-Completed { background: #d4edda; color: #155724; border: 1px solid #28a745; }
    .status-completed { background: #d4edda; color: #155724; border: 1px solid #28a745; }
    .status-Complete { background: #d4edda; color: #155724; border: 1px solid #28a745; }
    .status-complete { background: #d4edda; color: #155724; border: 1px solid #28a745; }
    
    .status-Delivered { background: #d4edda; color: #155724; border: 1px solid #28a745; }
    .status-delivered { background: #d4edda; color: #155724; border: 1px solid #28a745; }
    
    .status-Cancelled { background: #f8d7da; color: #721c24; border: 1px solid #dc3545; }
    .status-cancelled { background: #f8d7da; color: #721c24; border: 1px solid #dc3545; }
    .status-Canceled { background: #f8d7da; color: #721c24; border: 1px solid #dc3545; }
    .status-canceled { background: #f8d7da; color: #721c24; border: 1px solid #dc3545; }
    
    .status-Rejected { background: #f8d7da; color: #721c24; border: 1px solid #dc3545; }
    .status-rejected { background: #f8d7da; color: #721c24; border: 1px solid #dc3545; }
    
    /* Default for unknown statuses */
    .status-badge:not([class*="status-"]) {
        background: #e9ecef;
        color: #495057;
        border: 1px solid #dee2e6;
    }
    
    /* Card Actions */
    .card-actions {
        margin-top: auto;
        padding-top: 15px;
        border-top: 2px solid #f0f0f0;
        display: flex;
        gap: 10px;
    }
    
    .action-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px;
        color: #fff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.9em;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .details-btn {
        background: linear-gradient(135deg, #176B87 0%, #14546c 100%);
    }
    
    .details-btn:hover {
        background: linear-gradient(135deg, #14546c 0%, #176B87 100%);
    }
    
    .assign-btn {
        background: linear-gradient(135deg, #176B87 0%, #1cc88a 100%);
        flex: none;
    }
    
    .assign-btn:hover {
        background: linear-gradient(135deg, #1cc88a 0%, #176B87 100%);
    }
    
    .edit-btn {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        color: #fff;
    }
    
    .edit-btn:hover {
        background: linear-gradient(135deg, #5a6268 0%, #6c757d 100%);
    }
    
    .quote-btn {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    }
    
    .quote-btn:hover {
        background: linear-gradient(135deg, #1e7e34 0%, #28a745 100%);
    }
    
    .edit-quote-btn {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #333;
    }
    
    .edit-quote-btn:hover {
        background: linear-gradient(135deg, #e0a800 0%, #ffc107 100%);
    }
    
    /* Modals */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        justify-content: center;
        align-items: center;
        z-index: 9999;
        animation: fadeIn 0.3s ease;
    }
    
    .modal-content {
        background: #fff;
        padding: 35px;
        border-radius: 15px;
        width: 90%;
        max-width: 600px;
        position: relative;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        max-height: 90vh;
        overflow-y: auto;
        animation: slideDown 0.4s ease-out;
    }
    
    .modal-content h3 {
        color: #176B87;
        margin-top: 0;
        border-bottom: 3px solid #1cc88a;
        padding-bottom: 15px;
        margin-bottom: 25px;
        font-size: 1.5em;
        font-weight: 600;
    }
    
    .close-btn {
        position: absolute;
        top: 15px;
        right: 20px;
        cursor: pointer;
        font-size: 28px;
        color: #555;
        transition: color 0.3s;
    }
    
    .close-btn:hover {
        color: #dc3545;
    }
    
    .modal-detail-section {
        margin-bottom: 20px;
        padding: 15px;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        background: #f8f9fa;
    }
    
    .modal-detail-section h4 {
        color: #176B87;
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 1.1em;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .modal-detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 0.95em;
        padding: 5px 0;
    }
    
    .modal-detail-row strong {
        font-weight: 600;
        color: #555;
    }
    
    .modal-detail-row span {
        font-weight: 500;
        color: #333;
        text-align: right;
    }
    
    .modal-buttons {
        margin-top: 25px;
        display: flex;
        gap: 12px;
    }
    
    .modal-buttons .action-btn {
        flex: 1;
    }
    
    /* Assign Modal */
    #assignModal select,
    #assignModal button[type="submit"] {
        width: 100%;
        padding: 14px;
        margin-top: 15px;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 16px;
        box-sizing: border-box;
        transition: all 0.3s;
    }
    
    #assignModal select:focus {
        border-color: #176B87;
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(23, 107, 135, 0.25);
    }
    
    #assignModal button[type="submit"] {
        background: linear-gradient(135deg, #176B87 0%, #1cc88a 100%);
        color: #fff;
        border: none;
        cursor: pointer;
        font-weight: 600;
        margin-top: 20px;
    }
    
    #assignModal button[type="submit"]:hover {
        background: linear-gradient(135deg, #1cc88a 0%, #176B87 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    /* Success Message */
    .alert-success {
        margin-bottom: 20px;
        padding: 15px 20px;
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        border-radius: 10px;
        animation: slideDown 0.5s ease-out;
    }
    
    .alert-success i {
        margin-right: 10px;
    }
    
    /* No Bookings */
    .no-bookings {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
    }
    
    .no-bookings i {
        color: #176B87;
        margin-bottom: 20px;
    }
    
    .no-bookings h3 {
        color: #333;
        margin: 15px 0 10px 0;
    }
    
    .no-bookings p {
        color: #666;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <h2><i class="fa fa-calendar-check"></i> Customer Bookings</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item active">View Bookings</li>
        </ol>
    </nav>
</div>

<?php if (isset($_GET['success_message'])): ?>
    <div class="alert-success">
        <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success_message']); ?>
    </div>
<?php endif; ?>

<div class="filter-controls">
    <div class="search-box">
        <i class="fa fa-search"></i>
        <input type="text" id="searchInput" placeholder="Search by Customer Name or Vehicle Number...">
    </div>
    
    <div class="filter-box">
        <label for="statusFilter"><i class="fa fa-filter"></i> Status:</label>
        <select id="statusFilter">
            <option value="All">All Statuses</option>
            <option value="Pending">Pending</option>
            <option value="Quoted">Quoted</option>
            <option value="Pay Now">Pay Now</option>
            <option value="Pay Done">Payment Done</option>
            <option value="Delivered">Delivered</option>
            <option value="InProgress">In Progress</option>
            <option value="Completed">Completed</option>
        </select>
    </div>
</div>

<div class="booking-grid" id="bookingGrid">
</div>

<div class="modal" id="detailsModal">
    <div class="modal-content">
        <span class="close-btn" data-modal-target="detailsModal">&times;</span>
        <h3>Booking Details <span id="modalDetailsBookingId"></span></h3>

        <div class="modal-detail-section">
            <h4><i class="fa fa-user"></i> Customer Information</h4>
            <div class="modal-detail-row"><strong>Name:</strong> <span id="detailName"></span></div>
            <div class="modal-detail-row"><strong>Email:</strong> <span id="detailEmail"></span></div>
            <div class="modal-detail-row"><strong>Phone:</strong> <span id="detailPhone"></span></div>
        </div>

        <div class="modal-detail-section">
            <h4><i class="fa fa-car"></i> Vehicle & Service</h4>
            <div class="modal-detail-row"><strong>Vehicle:</strong> <span id="detailVehicle"></span></div>
            <div class="modal-detail-row"><strong>Number:</strong> <span id="detailVehicleNo"></span></div>
            <div class="modal-detail-row"><strong>Service Type:</strong> <span id="detailServiceType"></span></div>
        </div>

        <div class="modal-detail-section">
            <h4><i class="fa fa-calendar-check"></i> Status & Assignment</h4>
            <div class="modal-detail-row"><strong>Date/Time:</strong> <span id="detailDateTime"></span></div>
            <div class="modal-detail-row"><strong>Current Status:</strong> <span id="detailStatus"></span></div>
            <div class="modal-detail-row"><strong>Assigned Driver:</strong> <span id="detailDriver"></span></div>
        </div>

        <!-- Notes Section (conditionally displayed) -->
        <div class="modal-detail-section" id="notesSection" style="display: none;">
            <h4><i class="fa fa-sticky-note"></i> Customer Notes</h4>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #176B87;">
                <p id="detailNotes" style="margin: 0; white-space: pre-wrap; color: #555; line-height: 1.6;">-</p>
            </div>
        </div>

        <div class="modal-buttons">
            <a href="#" id="quoteLink" class="action-btn quote-btn"><i class="fa fa-file-invoice-dollar"></i> Generate Quote</a>
            
            <button class="action-btn assign-btn" id="modalAssignBtn" data-modal="assign" data-booking-id="" data-driver-id="">
                <i class="fa fa-user-plus"></i> Assign Driver
            </button>
        </div>
    </div>
</div>

<div class="modal" id="assignModal">
    <div class="modal-content">
        <span class="close-btn" data-modal-target="assignModal">&times;</span>
        <h3>Assign Driver</h3>
        <form id="assignDriverForm">
            <input type="hidden" name="booking_id" id="assignModalBookingId">
            <select name="driver_id" id="driverSelect" required>
                <option value="">Select Driver</option>
                <?php 
                if ($drivers && $drivers->num_rows > 0) {
                    $drivers->data_seek(0);
                    while($d = $drivers->fetch_assoc()): ?>
                        <option value="<?= $d['did'] ?>"><?= htmlspecialchars($d['drivername']) ?></option>
                    <?php endwhile;
                }
                ?>
            </select>
            <button type="submit" id="assignSubmitBtn">Assign Driver</button>
        </form>
    </div>
</div>

<script>
const bookingsData = <?= json_encode($bookings_data); ?>;
const bookingsMap = bookingsData.reduce((map, obj) => {
    map[obj.booking_id] = obj;
    return map;
}, {});

const bookingGrid = document.getElementById('bookingGrid');
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const detailsModal = document.getElementById('detailsModal');
const assignModal = document.getElementById('assignModal');
const closeModalBtns = document.querySelectorAll('.close-btn');
const assignForm = document.getElementById('assignDriverForm');
const driverSelect = document.getElementById('driverSelect');
const assignModalBookingIdInput = document.getElementById('assignModalBookingId');
const assignSubmitBtn = document.getElementById('assignSubmitBtn');
const modalAssignBtn = document.getElementById('modalAssignBtn');

function renderBookingCards(dataToRender) {
    bookingGrid.innerHTML = '';
    
    if (dataToRender.length === 0) {
        bookingGrid.innerHTML = `
            <div class="no-bookings">
                <i class="fa fa-inbox fa-3x"></i>
                <h3>No Bookings Found</h3>
                <p>Try adjusting your search query or status filter.</p>
            </div>
        `;
        return;
    }

    dataToRender.forEach(booking => {
        const isAssigned = !!booking.drivername;
        // Normalize status for CSS class - remove spaces and special chars
        const statusClass = 'status-' + booking.status.replace(/[^a-zA-Z0-9]/g, '');
        
        let quoteButtonText = '';
        let quoteButtonClass = '';
        let quoteIcon = '';
        
        // Quote button logic: Show for In Progress, Complete, Pay Now (case-insensitive)
        const normalizedStatus = booking.status.toLowerCase();
        const quoteRelevantStatuses = ['in progress', 'complete', 'pay now'];
        const showQuoteButton = quoteRelevantStatuses.includes(normalizedStatus);
        
        // Check if quote already exists
        const hasQuote = booking.quotation && parseFloat(booking.quotation) > 0;
        
        // Special case: Show "View Quote" for Quoted status
        if (normalizedStatus === 'quoted') {
            quoteButtonText = 'View Quote';
            quoteButtonClass = 'edit-quote-btn';
            quoteIcon = 'fa-eye';
        } else if (showQuoteButton) {
            if (hasQuote) {
                quoteButtonText = 'Edit Quote';
                quoteButtonClass = 'edit-quote-btn';
                quoteIcon = 'fa-edit';
            } else {
                quoteButtonText = 'Generate Quote';
                quoteButtonClass = 'quote-btn';
                quoteIcon = 'fa-file-invoice-dollar';
            }
        }
        
        const quoteButton = (quoteButtonText) 
            ? `<a href="generate_quote.php?booking_id=${booking.booking_id}" class="action-btn ${quoteButtonClass}" style="flex:1;">
                <i class="fa ${quoteIcon}"></i> ${quoteButtonText}
            </a>`
            : '';

        const assignButton = isAssigned
            ? `<button class="action-btn assign-btn edit-btn" 
                        data-modal="assign"
                        data-booking-id="${booking.booking_id}" 
                        data-driver-id="${booking.driver_id || ''}">
                      <i class="fa fa-edit"></i> 
                   </button>`
            : `<button class="action-btn assign-btn" 
                        data-modal="assign"
                        data-booking-id="${booking.booking_id}" 
                        data-driver-id="">
                      <i class="fa fa-user-plus"></i> 
                   </button>`;

        const cardHTML = `
            <div class="booking-card">
                <div class="card-header">
                    <h4 style="color: #000;"><i class="fa fa-bookmark"></i> Booking #${booking.booking_id}</h4>
                    <span style="background: #555; color: #fff; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 0.9em;">
                        ${booking.status}
                    </span>
                </div>

                <div class="card-detail">
                    <strong><i class="fa fa-user"></i> Customer:</strong> 
                    <span>${booking.firstname} ${booking.lastname}</span>
                </div>
                
                <div class="card-detail">
                    <strong><i class="fa fa-wrench"></i> Service:</strong> 
                    <span>${booking.service_type}</span>
                </div>
                <div class="card-detail">
                    <strong><i class="fa fa-calendar"></i> Date/Time:</strong> 
                    <span>${booking.booking_date} / ${booking.booking_time}</span>
                </div>
                
                <div class="card-detail">
                    <strong><i class="fa fa-car"></i> Vehicle No.:</strong> 
                    <span>${booking.vehicle_number_formatted}</span>
                </div>
                
                <div class="card-detail">
                    <strong><i class="fa fa-user-tie"></i> Driver:</strong> 
                    <span>${booking.drivername || '<em style="color: #999;">Unassigned</em>'}</span>
                </div>

                <div class="card-actions">
                    <button class="action-btn details-btn" data-booking-id="${booking.booking_id}">
                        <i class="fa fa-info-circle"></i> View Details
                    </button>
                    ${assignButton}
                </div>
                ${quoteButton ? `<div class="card-actions">${quoteButton}</div>` : ''} 
            </div>
        `;
        bookingGrid.insertAdjacentHTML('beforeend', cardHTML);
    });

    attachCardButtonListeners();
}

function filterBookings() {
    const searchTerm = searchInput.value.toLowerCase().trim();
    const selectedStatus = statusFilter.value;

    const filteredData = bookingsData.filter(booking => {
        const statusMatch = selectedStatus === 'All' || booking.status === selectedStatus;
        const searchMatch = !searchTerm || 
            (booking.firstname + ' ' + booking.lastname).toLowerCase().includes(searchTerm) || 
            booking.vehicle_number_formatted.toLowerCase().includes(searchTerm);
            
        return statusMatch && searchMatch;
    });

    renderBookingCards(filteredData);
}

function openModal(modal) {
    document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
    modal.style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function populateDetailsModal(bookingId) {
    const booking = bookingsMap[bookingId];
    if (!booking) return;

    console.log('Booking Data:', booking); // Debug log
    console.log('Customer Notes:', booking.customer_notes); // Debug log
    console.log('Quotation:', booking.quotation); // Debug log

    document.getElementById('modalDetailsBookingId').textContent = '#' + bookingId;
    document.getElementById('detailName').textContent = booking.firstname + ' ' + booking.lastname;
    document.getElementById('detailEmail').textContent = booking.email;
    document.getElementById('detailPhone').textContent = booking.phone;
    document.getElementById('detailVehicle').textContent = booking.vehicle_make + ' ' + booking.vehicle_model;
    document.getElementById('detailVehicleNo').textContent = booking.vehicle_number_formatted;
    document.getElementById('detailServiceType').textContent = booking.service_type;
    document.getElementById('detailDateTime').textContent = booking.booking_date + ' / ' + booking.booking_time;
    
    const statusSpan = document.getElementById('detailStatus');
    statusSpan.textContent = booking.status;
    // White text on dark background for visibility
    statusSpan.className = '';
    statusSpan.style.background = '#555';
    statusSpan.style.color = '#fff';
    statusSpan.style.padding = '6px 12px';
    statusSpan.style.borderRadius = '4px';
    statusSpan.style.fontWeight = '600';
    statusSpan.style.display = 'inline-block';
    document.getElementById('detailDriver').textContent = booking.drivername || 'Unassigned';

    // Show/hide notes section based on content
    const notesSection = document.getElementById('notesSection');
    const detailNotes = document.getElementById('detailNotes');
    
    if (booking.customer_notes && booking.customer_notes.trim() !== '') {
        detailNotes.textContent = booking.customer_notes;
        notesSection.style.display = 'block';
    } else {
        notesSection.style.display = 'none';
    }

    const isAssigned = !!booking.drivername;
    modalAssignBtn.dataset.bookingId = bookingId;
    modalAssignBtn.dataset.driverId = booking.driver_id || '';
    modalAssignBtn.innerHTML = isAssigned 
        ? '<i class="fa fa-edit"></i> Edit Driver' 
        : '<i class="fa fa-user-plus"></i> Assign Driver';
    modalAssignBtn.classList.toggle('edit-btn', isAssigned);

    // Quote button logic based on current workflow (case-insensitive)
    const quoteLink = document.getElementById('quoteLink');
    quoteLink.href = `generate_quote.php?booking_id=${bookingId}`;

    // Quote button logic: Show for In Progress, Complete, Pay Now (case-insensitive)
    const normalizedStatus = booking.status.toLowerCase();
    const quoteRelevantStatuses = ['in progress', 'complete', 'pay now'];
    const showQuoteButton = quoteRelevantStatuses.includes(normalizedStatus);
    
    // Determine if quote already exists (quotation field has value)
    const hasQuote = booking.quotation && parseFloat(booking.quotation) > 0;
    
    // Special case: Show "View Quote" for Quoted status
    if (normalizedStatus === 'quoted') {
        quoteLink.innerHTML = '<i class="fa fa-eye"></i> View Quote';
        quoteLink.classList.add('edit-quote-btn');
        quoteLink.classList.remove('quote-btn');
        quoteLink.style.display = 'inline-flex';
    } else if (showQuoteButton) {
        if (hasQuote) {
            quoteLink.innerHTML = '<i class="fa fa-edit"></i> Edit Quote';
            quoteLink.classList.add('edit-quote-btn');
            quoteLink.classList.remove('quote-btn');
        } else {
            quoteLink.innerHTML = '<i class="fa fa-file-invoice-dollar"></i> Generate Quote';
            quoteLink.classList.add('quote-btn');
            quoteLink.classList.remove('edit-quote-btn');
        }
        quoteLink.style.display = 'inline-flex';
    } else {
        quoteLink.style.display = 'none';
    }

    openModal(detailsModal);
}

function populateAssignModal(bookingId, driverId) {
    const isEditing = !!driverId;

    assignModalBookingIdInput.value = bookingId;
    driverSelect.value = driverId || '';

    document.querySelector('#assignModal h3').textContent = isEditing ? 'Edit Driver' : 'Assign Driver';
    assignSubmitBtn.textContent = isEditing ? 'Update Driver' : 'Assign Driver';

    openModal(assignModal);
}

function handleCardButtonClick() {
    const btn = this;
    const bookingId = btn.dataset.bookingId;

    if (btn.classList.contains('details-btn')) {
        populateDetailsModal(bookingId);
    } else if (btn.dataset.modal === 'assign') {
        const driverId = btn.dataset.driverId;
        populateAssignModal(bookingId, driverId);
    }
}

function attachCardButtonListeners() {
    document.querySelectorAll('.booking-card button').forEach(btn => {
        btn.removeEventListener('click', handleCardButtonClick);
        btn.addEventListener('click', handleCardButtonClick);
    });
}

searchInput.addEventListener('input', filterBookings); 
statusFilter.addEventListener('change', filterBookings);

modalAssignBtn.addEventListener('click', () => {
    const bookingId = modalAssignBtn.dataset.bookingId;
    const driverId = modalAssignBtn.dataset.driverId;
    closeModal('detailsModal');
    populateAssignModal(bookingId, driverId);
});

closeModalBtns.forEach(btn => {
    btn.addEventListener('click', () => closeModal(btn.dataset.modalTarget));
});

window.addEventListener('click', e => { 
    if(e.target === detailsModal) closeModal('detailsModal'); 
    if(e.target === assignModal) closeModal('assignModal'); 
});

assignForm.addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(assignForm);
    
    if (!driverSelect.value) {
        alert("Please select a driver to assign.");
        return;
    }

    fetch('assign_driver_action.php', { 
        method:'POST',
        body: formData
    }).then(res=>res.text())
      .then(data=>{
          alert(data);
          closeModal('assignModal');
          location.reload();
      })
      .catch(error => {
        alert("An error occurred during assignment.");
        console.error('Error:', error);
      });
});

document.addEventListener('DOMContentLoaded', () => {
    renderBookingCards(bookingsData); 
});
</script>

<?php
include("includes/admin_footer.php");
$conn->close();
?>
