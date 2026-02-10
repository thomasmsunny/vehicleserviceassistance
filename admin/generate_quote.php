<?php
session_start();
// Ensure this path points to your actual configuration file
require('../config/autoload.php'); 

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access Denied");
}

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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

// 1. Get the booking ID from the URL
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

if ($booking_id === 0) {
    die("Invalid Booking ID.");
}

// 2. Fetch booking and customer details
$sql_booking = "SELECT b.booking_id, c.firstname, c.lastname, c.email, c.phone, 
                b.service_type, b.vehicle_make, b.vehicle_model, b.vehicle_number, b.status
         FROM bookings b 
         JOIN customerreg c ON b.customer_id = c.customer_id
         WHERE b.booking_id = ?";
        
$stmt_booking = $conn->prepare($sql_booking);
$stmt_booking->bind_param("i", $booking_id);
$stmt_booking->execute();
$result_booking = $stmt_booking->get_result();

if ($result_booking->num_rows === 0) {
    die("Booking not found.");
}

$booking = $result_booking->fetch_assoc();
$stmt_booking->close();

$customer_name = htmlspecialchars($booking['firstname'] . ' ' . $booking['lastname']);
$service_type = htmlspecialchars($booking['service_type']);
$vehicle_details = htmlspecialchars($booking['vehicle_make'] . ' ' . $booking['vehicle_model']);
$vehicle_number = formatVehicleNo($booking['vehicle_number']);

// 3. Fetch Existing Quote Details (if any)
$quote = [];
$quote_items = [];
$is_editing = false;

$sql_quote = "SELECT * FROM quotations WHERE booking_id = ?";
$stmt_quote = $conn->prepare($sql_quote);
$stmt_quote->bind_param("i", $booking_id);
$stmt_quote->execute();
$result_quote = $stmt_quote->get_result();

if ($result_quote->num_rows > 0) {
    $is_editing = true;
    $quote = $result_quote->fetch_assoc();
    
    // Fetch all line items for the existing quote
    $sql_items = "SELECT item_description, item_quantity, unit_price, item_total FROM quotation_items WHERE booking_id = ?";
    $stmt_items = $conn->prepare($sql_items);
    $stmt_items->bind_param("i", $booking_id);
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();
    while ($row = $result_items->fetch_assoc()) {
        $quote_items[] = $row;
    }
    $stmt_items->close();
}
$stmt_quote->close();
$conn->close();

// Set initial form values based on fetched data
$initial_admin_name = $quote['admin_name'] ?? '';
$initial_discount = $quote['discount'] ?? '0.00';
$initial_cgst_rate = $quote['cgst_rate'] ?? '9';
$initial_sgst_rate = $quote['sgst_rate'] ?? '9';
$initial_notes = $quote['other_works_notes'] ?? '';

// If editing, use the existing line items. Otherwise, start with one empty row.
if (empty($quote_items)) {
    $quote_items[] = [
        'item_description' => '',
        'item_quantity' => 1,
        'unit_price' => 0.00,
        'item_total' => 0.00
    ];
}
// END OF PHP BLOCK
?>

<!DOCTYPE html>
<html>
<head>
    <title>Generate Quotation for Booking #<?= $booking_id ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #eef1f5; padding: 20px; color: #333; }
        .quote-container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        h2 { text-align: center; color: #176B87; margin-bottom: 25px; }
        
        .form-section { margin-bottom: 25px; padding: 15px; border: 1px solid #ddd; border-radius: 8px; }
        .form-section h3 { color: #010912ff; margin-top: 0; border-bottom: 1px dashed #ddd; padding-bottom: 5px; margin-bottom: 15px; }
        
        label { display: block; font-weight: 600; margin-bottom: 5px; color: #555; }
        input[type="text"], input[type="number"], textarea, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        textarea { resize: vertical; }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .form-grid.two-col {
            grid-template-columns: 1fr 1fr;
        }

        /* Quotation Table Styling */
        #quotation-items { width: 100%; border-collapse: collapse; margin-top: 20px; }
        #quotation-items th, #quotation-items td { padding: 10px; border: 1px solid #eee; text-align: left; }
        #quotation-items th { background: #f4f4f4; font-weight: 600; }
        
        .item-row input { margin-bottom: 0; }
        .item-row td:nth-child(2) { width: 45%; } /* Description column wider */
        .item-row td:nth-child(3) { width: 10%; } /* Qty column narrower */
        .item-row td:nth-child(4) { width: 25%; } /* Price column */
        
        .total-row strong { font-size: 1.1em; }
        .total-row td { background: #e0f7ff; font-weight: 700; }
        #quotation-totals td:last-child { text-align: right; }

        .add-item-btn {
            background-color: #28a745; color: white; padding: 8px 15px; border: none; 
            border-radius: 5px; cursor: pointer; margin-top: 10px; font-weight: 600;
        }
        .add-item-btn:hover { background-color: #1e7e34; }
        .remove-item-btn {
            background: none; border: none; color: #dc3545; cursor: pointer; font-size: 1.1em;
            padding: 0;
            line-height: 1;
        }

        .submit-btn {
            width: 100%; padding: 15px; background-color: #176B87; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1.1em; margin-top: 20px;
        }
        .submit-btn:hover { background-color: #14546c; }
    </style>
</head>
<body>

<div class="quote-container">
    <h2><i class="fa fa-file-invoice-dollar"></i> Generate Quotation</h2>
    
    <form id="quoteForm" method="POST" action="save_quote_action.php">
        <input type="hidden" name="booking_id" value="<?= $booking_id ?>">

        <div class="form-section">
            <h3>Booking Reference: #<?= $booking_id ?></h3>
            <div class="form-grid">
                <div>
                    <label>Customer Name</label>
                    <input type="text" value="<?= $customer_name ?>" readonly>
                </div>
                <div>
                    <label>Service Type</label>
                    <input type="text" value="<?= $service_type ?>" readonly>
                </div>
                <div>
                    <label>Vehicle Model</label>
                    <input type="text" value="<?= $vehicle_details ?>" readonly>
                </div>
                <div>
                    <label>Vehicle Number</label>
                    <input type="text" value="<?= $vehicle_number ?>" readonly>
                </div>
            </div>
           <div class="form-grid two-col">
    <div>
        <label>Admin/Quoter Name (Optional)</label>
        <input type="text" name="admin_name" placeholder="E.g., John Doe" value="<?= $initial_admin_name ?>" required>
    </div>
    <div>
        <label>Quotation Date</label>
        <input type="text" name="quote_date" value="<?= date('Y-m-d') ?>" readonly>
    </div>
</div>

        <div class="form-section">
            <h3>Quotation Items (Service & Parts)</h3>
            <table id="quotation-items">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th>Description</th>
                        <th style="width: 10%;">Qty</th>
                        <th style="width: 25%;">Unit Price (₹)</th>
                        <th style="width: 10%;">Total (₹)</th>
                        <th style="width: 5%;"></th>
                    </tr>
                </thead>
               <tbody>
    <?php foreach ($quote_items as $index => $item): ?>
    <tr class="item-row" data-id="<?= $index + 1 ?>">
        <td><?= $index + 1 ?></td>
        <td><input type="text" name="description[]" placeholder="e.g., Engine Oil Change, Labour" value="<?= htmlspecialchars($item['item_description']) ?>" required></td>
        <td><input type="number" name="quantity[]" value="<?= htmlspecialchars($item['item_quantity']) ?>" min="1" oninput="calculateRowTotal(this); calculateTotal();" required></td>
        <td><input type="number" name="price[]" value="<?= htmlspecialchars(number_format($item['unit_price'], 2, '.', '')) ?>" min="0" step="0.01" oninput="calculateRowTotal(this); calculateTotal();" required></td>
        <td><input type="text" class="row-total" value="<?= htmlspecialchars(number_format($item['item_quantity'] * $item['unit_price'], 2, '.', '')) ?>" readonly></td>
        <td><button type="button" class="remove-item-btn" onclick="removeItem(this)"><i class="fa fa-times-circle"></i></button></td>
    </tr>
    <?php endforeach; ?>
</tbody>
            </table>
            <button type="button" class="add-item-btn" onclick="addItem()"><i class="fa fa-plus"></i> Add Service/Part</button>
        </div>

      <div class="form-section">
    <h3>Summary, Tax & Notes</h3>
    <div class="form-grid two-col">
        <div>
            <label>Subtotal (₹)</label>
            <input type="text" id="subtotal" name="subtotal" value="<?= number_format($quote['subtotal'] ?? 0.00, 2, '.', '') ?>" readonly>
        </div>
        <div>
            <label>Discount/Other Charges (₹)</label>
            <input type="number" id="discount" name="discount" value="<?= $initial_discount ?>" min="0" step="0.01" oninput="calculateTotal()">
        </div>
        <div>
            <label>CGST Rate (%)</label>
            <input type="number" id="cgst_rate" name="cgst_rate" value="<?= $initial_cgst_rate ?>" min="0" step="0.01" oninput="calculateTotal()">
        </div>
        <div>
            <label>SGST Rate (%)</label>
            <input type="number" id="sgst_rate" name="sgst_rate" value="<?= $initial_sgst_rate ?>" min="0" step="0.01" oninput="calculateTotal()">
        </div>
    </div>

    <table id="quotation-totals" style="width: 100%; margin-top: 20px;">
        <tr class="total-row">
            <td style="width: 70%;">Total Tax (CGST + SGST)</td>
            <td>₹ <span id="total_tax_display"><?= number_format($quote['total_tax'] ?? 0.00, 2, '.', '') ?></span></td>
            <input type="hidden" name="total_tax" id="total_tax_input" value="<?= number_format($quote['total_tax'] ?? 0.00, 2, '.', '') ?>">
        </tr>
        <tr class="total-row">
            <td><strong>Grand Total</strong></td>
            <td>₹ <span id="grand_total_display"><?= number_format($quote['grand_total'] ?? 0.00, 2, '.', '') ?></span></td>
            <input type="hidden" name="grand_total" id="grand_total_input" value="<?= number_format($quote['grand_total'] ?? 0.00, 2, '.', '') ?>">
        </tr>
    </table>
    
    <label style="margin-top: 20px;">Other Works/Notes Column</label>
    <textarea name="other_works_notes" rows="3" placeholder="Add any special instructions or terms here..."><?= htmlspecialchars($initial_notes) ?></textarea>
</div>

        <button type="submit" class="submit-btn"><i class="fa fa-save"></i> Save & Generate Quotation</button>
    </form>
</div>

<script>
    let itemCounter = 1;

    // --- Row Calculation ---
    function calculateRowTotal(inputElement) {
        const row = inputElement.closest('.item-row');
        const quantity = parseFloat(row.querySelector('input[name="quantity[]"]').value) || 0;
        const price = parseFloat(row.querySelector('input[name="price[]"]').value) || 0;
        const rowTotal = quantity * price;
        
        row.querySelector('.row-total').value = rowTotal.toFixed(2);
    }

    // --- Dynamic Item Management ---

    function addItem() {
        itemCounter++;
        const tableBody = document.querySelector('#quotation-items tbody');
        const newRow = document.createElement('tr');
        newRow.className = 'item-row';
        newRow.setAttribute('data-id', itemCounter);
        
        newRow.innerHTML = `
            <td>${itemCounter}</td>
            <td><input type="text" name="description[]" placeholder="e.g., Engine Oil Change, Labour" required></td>
            <td><input type="number" name="quantity[]" value="1" min="1" oninput="calculateRowTotal(this); calculateTotal();" required></td>
            <td><input type="number" name="price[]" value="0.00" min="0" step="0.01" oninput="calculateRowTotal(this); calculateTotal();" required></td>
            <td><input type="text" class="row-total" value="0.00" readonly></td>
            <td><button type="button" class="remove-item-btn" onclick="removeItem(this)"><i class="fa fa-times-circle"></i></button></td>
        `;
        tableBody.appendChild(newRow);
        calculateTotal();
    }

    function removeItem(button) {
        const row = button.closest('.item-row');
        row.remove();
        reindexItems();
        calculateTotal();
    }
    
    function reindexItems() {
        // Renumber the rows after removal
        const rows = document.querySelectorAll('#quotation-items tbody .item-row');
        rows.forEach((row, index) => {
            row.querySelector('td:first-child').textContent = index + 1;
        });
    }

    // --- Calculation Logic (Includes CGST and SGST) ---

    function calculateTotal() {
        let subtotal = 0;
        const itemRows = document.querySelectorAll('#quotation-items tbody .item-row');

        // 1. Calculate Subtotal from Line Items
        itemRows.forEach(row => {
            const rowTotal = parseFloat(row.querySelector('.row-total').value) || 0;
            subtotal += rowTotal;
        });

        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const cgstRate = parseFloat(document.getElementById('cgst_rate').value) / 100 || 0;
        const sgstRate = parseFloat(document.getElementById('sgst_rate').value) / 100 || 0;

        // Apply Discount to Subtotal
        const netTotalAfterDiscount = subtotal - discount;

        // 2. Calculate Taxes on the Net Total
        const cgstAmount = netTotalAfterDiscount * cgstRate;
        const sgstAmount = netTotalAfterDiscount * sgstRate;
        const totalTax = cgstAmount + sgstAmount;

        // 3. Calculate Grand Total
        const grandTotal = netTotalAfterDiscount + totalTax;

        // 4. Update Display Fields
        document.getElementById('subtotal').value = subtotal.toFixed(2);
        document.getElementById('total_tax_display').textContent = totalTax.toFixed(2);
        document.getElementById('grand_total_display').textContent = grandTotal.toFixed(2);

        // 5. Update Hidden Fields for Submission
        document.getElementById('total_tax_input').value = totalTax.toFixed(2);
        document.getElementById('grand_total_input').value = grandTotal.toFixed(2);
    }

// Initialize row total and grand total on load
window.onload = function() {
    // 1. Calculate initial row total for every existing item-row.
    // This uses the input values populated by PHP.
    document.querySelectorAll('.item-row').forEach(row => {
        // You can use any input element in the row to trigger the calculation, 
        // for simplicity, we use the price input.
        calculateRowTotal(row.querySelector('input[name="price[]"]'));
    });
    
    // 2. Now that all row totals are correct, calculate the grand total.
    calculateTotal();
};
</script>

</body>
</html>