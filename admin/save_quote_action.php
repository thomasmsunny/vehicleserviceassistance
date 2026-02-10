<?php
session_start();
// Ensure this path points to your actual configuration file
require('../config/autoload.php'); 

// 1. Security & Method Check
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Access Denied or Invalid Request Method.");
}

// 2. Database Connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}

// Start Transaction
$conn->begin_transaction();

try {
    // 3. Collect & Sanitize Main Quote Data
    $booking_id = intval($_POST['booking_id']);
    // Note: real_escape_string is redundant when using prepared statements for the final query, 
    // but is okay for data that might be used elsewhere. 
    $admin_name = $conn->real_escape_string($_POST['admin_name']);
    $quote_date = date('Y-m-d'); // Use the current date
    $subtotal = floatval($_POST['subtotal']);
    $discount = floatval($_POST['discount']);
    $cgst_rate = floatval($_POST['cgst_rate']);
    $sgst_rate = floatval($_POST['sgst_rate']);
    $total_tax = floatval($_POST['total_tax']);
    $grand_total = floatval($_POST['grand_total']);
    $other_works_notes = $conn->real_escape_string($_POST['other_works_notes']);
    
    // Check if the booking already has a quote (for editing)
    $check_sql = "SELECT booking_id FROM quotations WHERE booking_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $booking_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_stmt->close();

    if ($check_result->num_rows > 0) {
        // --- A. UPDATE Existing Quote (Header) ---
        $sql_quote = "UPDATE quotations SET
                        admin_name = ?, subtotal = ?, discount = ?, 
                        cgst_rate = ?, sgst_rate = ?, total_tax = ?, 
                        grand_total = ?, other_works_notes = ?
                      WHERE booking_id = ?";
        
        $stmt_quote = $conn->prepare($sql_quote);
        // Bind parameters: sddddddsi (string, 6 doubles, string, integer)
        $stmt_quote->bind_param("sddddddsi", 
                                 $admin_name, $subtotal, $discount, $cgst_rate, $sgst_rate, 
                                 $total_tax, $grand_total, $other_works_notes, $booking_id);
        
        if (!$stmt_quote->execute()) {
            throw new Exception("Error updating quote header: " . $stmt_quote->error);
        }
        $stmt_quote->close();

        // Delete old items before inserting new ones (for editing)
        $sql_delete_items = "DELETE FROM quotation_items WHERE booking_id = ?";
        $stmt_delete = $conn->prepare($sql_delete_items);
        $stmt_delete->bind_param("i", $booking_id);
        if (!$stmt_delete->execute()) {
            throw new Exception("Error deleting old quote items: " . $stmt_delete->error);
        }
        $stmt_delete->close();

    } else {
        // --- B. INSERT New Quote (Header) ---
        $sql_quote = "INSERT INTO quotations 
                        (booking_id, admin_name, quote_date, subtotal, discount, cgst_rate, sgst_rate, total_tax, grand_total, other_works_notes) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_quote = $conn->prepare($sql_quote);
        // Bind parameters: isddddddds (integer, string, date string, 6 doubles, string)
        $stmt_quote->bind_param("issdddddds", 
                                 $booking_id, $admin_name, $quote_date, $subtotal, $discount, 
                                 $cgst_rate, $sgst_rate, $total_tax, $grand_total, $other_works_notes);
        
        if (!$stmt_quote->execute()) {
            throw new Exception("Error inserting quote header: " . $stmt_quote->error);
        }
        $stmt_quote->close();

        // Optional: Update the booking status ONLY for a new quote
        $sql_status = "UPDATE bookings SET status = 'Quoted' WHERE booking_id = ? AND status != 'Completed' AND status != 'Cancelled'";
        $stmt_status = $conn->prepare($sql_status);
        $stmt_status->bind_param("i", $booking_id);
        if (!$stmt_status->execute()) {
            throw new Exception("Error updating booking status: " . $stmt_status->error);
        }
        $stmt_status->close();
    }


    // 4. Insert Line Items (common to both INSERT and UPDATE)
    if (isset($_POST['description']) && is_array($_POST['description'])) {
        $sql_item = "INSERT INTO quotation_items (booking_id, item_description, item_quantity, unit_price, item_total) 
                     VALUES (?, ?, ?, ?, ?)";
        $stmt_item = $conn->prepare($sql_item);

        foreach ($_POST['description'] as $index => $description) {
            // Using prepared statements (bind_param) is safer than real_escape_string here
            $desc = $_POST['description'][$index];
            $quantity = intval($_POST['quantity'][$index]);
            $price = floatval($_POST['price'][$index]);
            $item_total = $quantity * $price; 

            // Check for valid line items before inserting
            if (empty(trim($desc)) || $quantity <= 0) {
                continue; 
            }

            // Bind parameters: isidd (integer, string, integer, double, double)
            $stmt_item->bind_param("isidd", $booking_id, $desc, $quantity, $price, $item_total);
            
            if (!$stmt_item->execute()) {
                throw new Exception("Error inserting line item #$index: " . $stmt_item->error);
            }
        }
        $stmt_item->close();
    }
    
    // --- 5. CRITICAL FIX: UPDATE THE QUOTATION FLAG ON THE BOOKINGS TABLE ---
    // This is the step that makes the "View Quote" button appear on the customer page.
    $quotation_flag_value = "Added"; // Set to any non-empty string, or '1' if the column is a boolean/tinyint
    $sql_update_flag = "UPDATE bookings SET quotation = ? WHERE booking_id = ?";
    $stmt_update_flag = $conn->prepare($sql_update_flag);
    $stmt_update_flag->bind_param("si", $quotation_flag_value, $booking_id);
    
    if (!$stmt_update_flag->execute()) {
        throw new Exception("Error updating quotation flag in bookings table: " . $stmt_update_flag->error);
    }
    $stmt_update_flag->close();


    // Commit Transaction if everything succeeded
    $conn->commit();
    $conn->close();

    // Success and Redirect
    $_SESSION['success_message'] = "Quotation for Booking #$booking_id saved successfully!";
    header("Location: viewquote.php?booking_id=" . $booking_id);
    exit(); 

} catch (Exception $e) {
    // Rollback Transaction on error
    $conn->rollback();
    $conn->close();

    // Store error message and redirect
    $_SESSION['error_message'] = "The quotation could not be saved due to an error: " . $e->getMessage();
    header("Location: generate_quote_page.php?booking_id=" . $booking_id); // Redirect back to edit page
    exit();
}
