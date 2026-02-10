<?php
// PHP Script Start
session_start();
// Ensure this path points to your actual configuration file
require('../config/autoload.php'); 

// 1. Get the booking ID from the URL
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

if ($booking_id === 0) {
    die("Invalid Booking ID.");
}

// 2. Database connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}

// Check who is logged in
$is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
$is_customer = isset($_SESSION['customer_id']);
$logged_in_customer_id = $_SESSION['customer_id'] ?? null;

// CRITICAL SECURITY: Check if the logged-in user has permission to see THIS quote.
// Fetch the customer_id associated with this booking
$sql_owner_check = "SELECT customer_id FROM bookings WHERE booking_id = ?";
$stmt_owner_check = $conn->prepare($sql_owner_check);
$stmt_owner_check->bind_param("i", $booking_id);
$stmt_owner_check->execute();
$result_owner_check = $stmt_owner_check->get_result();
$booking_owner = $result_owner_check->fetch_assoc();
$stmt_owner_check->close(); // Close this statement before running others

$has_permission = false;

if ($is_admin) {
    // Admin always has permission
    $has_permission = true;
} elseif ($is_customer && $booking_owner && $booking_owner['customer_id'] === $logged_in_customer_id) {
    // Customer has permission only if the booking belongs to them
    $has_permission = true;
}

if (!$has_permission) {
    $conn->close(); // Close connection before dying
    die("Access Denied: You do not have permission to view this quotation.");
}
// Access is granted, proceed to data fetching.

// Function to format vehicle numbers (copied from your main script)
if (!function_exists('formatVehicleNo')) {
    function formatVehicleNo($vn) {
        $vn = strtoupper(preg_replace("/[^A-Z0-9]/", "", $vn));
        if (preg_match("/^([A-Z]{2})(\d{2})([A-Z]{1,2})(\d{4})$/", $vn, $m)) {
            return $m[1] . '-' . $m[2] . '-' . $m[3] . '-' . $m[4];
        }
        return $vn;
    }
}

// 3. Fetch all necessary data
// Fetch Booking and Customer Details
$sql_customer = "SELECT c.firstname, c.lastname, c.email, c.phone, 
                 b.service_type, b.vehicle_make, b.vehicle_model, b.vehicle_number
                 FROM bookings b 
                 JOIN customerreg c ON b.customer_id = c.customer_id
                 WHERE b.booking_id = ?";
$stmt_customer = $conn->prepare($sql_customer);
$stmt_customer->bind_param("i", $booking_id);
$stmt_customer->execute();
$result_customer = $stmt_customer->get_result();

if ($result_customer->num_rows === 0) {
    $conn->close();
    die("Booking details not found.");
}
$booking = $result_customer->fetch_assoc();
$stmt_customer->close();

// Fetch Quote Details
$sql_quote = "SELECT * FROM quotations WHERE booking_id = ?";
$stmt_quote = $conn->prepare($sql_quote);
$stmt_quote->bind_param("i", $booking_id);
$stmt_quote->execute();
$result_quote = $stmt_quote->get_result();

if ($result_quote->num_rows === 0) {
    $conn->close();
    die("Quotation not found for this booking.");
}
$quote = $result_quote->fetch_assoc();
$stmt_quote->close();

// Fetch Quote Items
$sql_items = "SELECT item_description, item_quantity, unit_price, item_total 
              FROM quotation_items WHERE booking_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $booking_id);
$stmt_items->execute();
$quote_items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();

$conn->close();

// Prepare variables for display
$customer_name = htmlspecialchars($booking['firstname'] . ' ' . $booking['lastname']);
$customer_email = htmlspecialchars($booking['email']);
$customer_phone = htmlspecialchars($booking['phone']);
$vehicle_details = htmlspecialchars($booking['vehicle_make'] . ' ' . $booking['vehicle_model']);
$vehicle_number = formatVehicleNo($booking['vehicle_number']);

$quote_date = date('d-M-Y', strtotime($quote['quote_date']));
$admin_name = htmlspecialchars($quote['admin_name']);
$notes = nl2br(htmlspecialchars($quote['other_works_notes'])); // Format notes with line breaks

// Format financial variables
$subtotal = number_format($quote['subtotal'], 2);
$discount = number_format($quote['discount'], 2);
$cgst_rate = number_format($quote['cgst_rate'], 2);
$sgst_rate = number_format($quote['sgst_rate'], 2);
$total_tax = number_format($quote['total_tax'], 2);
$grand_total = number_format($quote['grand_total'], 2);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Quotation #<?= $booking_id ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: sans-serif; background: #f4f4f9; padding: 20px; color: #333; }
        .quote-document {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #ddd;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #176B87; padding-bottom: 15px; }
        .header h1 { color: #176B87; margin: 0; font-size: 2.5em; }
        .header p { margin: 5px 0; font-size: 1.1em; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .info-box { padding: 15px; border: 1px solid #eee; border-radius: 5px; }
        .info-box h3 { margin-top: 0; color: #010912ff; font-size: 1.2em; border-bottom: 1px dashed #eee; padding-bottom: 5px; margin-bottom: 10px; }
        .info-box p { margin: 5px 0; font-size: 0.95em; }
        .info-box p strong { display: inline-block; width: 100px; }

        /* Quote Items Table */
        .items-table { width: 100%; border-collapse: collapse; margin: 30px 0; }
        .items-table th, .items-table td { padding: 12px; border: 1px solid #e0e0e0; text-align: left; }
        .items-table th { background: #176B87; color: white; font-weight: 600; }
        .items-table td:last-child, .items-table th:last-child { text-align: right; }
        .items-table td:nth-child(3), .items-table td:nth-child(4) { text-align: center; }

        /* Totals Area */
        .totals-notes { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 30px; }
        .notes-section { padding: 15px; border: 1px solid #ccc; border-radius: 5px; background: #f9f9f9; }
        .notes-section h3 { margin-top: 0; color: #010912ff; font-size: 1.2em; }
        
        .totals-table { width: 100%; }
        .totals-table td { padding: 8px 0; border-bottom: 1px dotted #ccc; }
        .totals-table td:last-child { text-align: right; font-weight: bold; }
        .totals-table tr.grand-total td { 
            font-size: 1.3em; 
            color: #176B87; 
            font-weight: bold;
            border-top: 2px solid #176B87;
            border-bottom: none;
        }

        /* Action Buttons */
        .actions { text-align: center; margin-top: 30px; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; margin: 0 10px; }
        .btn-print { background-color: #28a745; color: white; }
        .btn-print:hover { background-color: #1e7e34; }
        
        @media print {
            body { background: none; }
            .quote-document { border: none; box-shadow: none; padding: 0; }
            .actions { display: none; }
        }
    </style>
</head>
<body>

<div class="quote-document">
    
    <div class="header">
        <h1>AutoFix</h1>
        <h3>Aluva To Nedumbassery Airport Road Opposite Federal Bank PIN:535022</h3>
        <h4>Ph: +91 9961248888</h4>
        <p>Reference #<?= $booking_id ?> | Date: <?= $quote_date ?></p>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <h3>Customer Details</h3>
            <p><strong>Name:</strong> <?= $customer_name ?></p>
            <p><strong>Email:</strong> <?= $customer_email ?></p>
            <p><strong>Phone:</strong> <?= $customer_phone ?></p>
        </div>
        <div class="info-box">
            <h3>Vehicle Details</h3>
            <p><strong>Service:</strong> <?= htmlspecialchars($booking['service_type']) ?></p>
            <p><strong>Model:</strong> <?= $vehicle_details ?></p>
            <p><strong>Reg. No:</strong> <?= $vehicle_number ?></p>
        </div>
    </div>

    <h2>Quoted Services & Parts</h2>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th>Description</th>
                <th style="width: 10%;">Qty</th>
                <th style="width: 15%;">Unit Price (₹)</th>
                <th style="width: 15%;">Total (₹)</th>
            </tr>
        </thead>
        <tbody>
            <?php $item_count = 1; ?>
            <?php foreach ($quote_items as $item): ?>
            <tr>
                <td><?= $item_count++ ?></td>
                <td><?= htmlspecialchars($item['item_description']) ?></td>
                <td><?= htmlspecialchars($item['item_quantity']) ?></td>
                <td><?= number_format($item['unit_price'], 2) ?></td>
                <td><?= number_format($item['item_total'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals-notes">
        <div class="notes-section">
            <h3>Quoted By</h3>
            <p>This quotation was prepared by: <strong><?= $admin_name ?></strong></p>
            <hr>
            <h3>Notes & Terms</h3>
            <p><?= $notes ?></p>
        </div>

        <div>
            <table class="totals-table">
                <tr>
                    <td>Subtotal</td>
                    <td>₹ <?= $subtotal ?></td>
                </tr>
                <tr>
                    <td>Discount/Other Charges</td>
                    <td>- ₹ <?= $discount ?></td>
                </tr>
                <tr>
                    <td>Taxable Amount</td>
                    <td>₹ <?= number_format($quote['subtotal'] - $quote['discount'], 2) ?></td>
                </tr>
                <tr>
                    <td>CGST (<?= $cgst_rate ?>%)</td>
                    <td>₹ <?= number_format($quote['total_tax'] / 2, 2) ?></td>
                </tr>
                <tr>
                    <td>SGST (<?= $sgst_rate ?>%)</td>
                    <td>₹ <?= number_format($quote['total_tax'] / 2, 2) ?></td>
                </tr>
                <tr class="grand-total">
                    <td>GRAND TOTAL</td>
                    <td>₹ <?= $grand_total ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="actions">
        <button class="btn btn-print" onclick="window.print()"><i class="fa fa-print"></i> Print / Save as PDF</button>
        </div>

</div>

</body>
</html>