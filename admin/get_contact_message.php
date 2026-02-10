<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Content-Type: application/json");
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Database connection
require_once '../customer/includes/db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id']) && is_numeric($_POST['id'])) {
    $message_id = $_POST['id'];
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
        $stmt->execute([$message_id]);
        $message = $stmt->fetch();
        
        if ($message) {
            // Update status to 'read' if it's currently 'new'
            if ($message['status'] == 'new') {
                $updateStmt = $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
                $updateStmt->execute([$message_id]);
            }
            
            // Format the response
            echo '
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <h6><strong>From:</strong></h6>
                        <p>' . htmlspecialchars($message['name']) . ' &lt;' . htmlspecialchars($message['email']) . '&gt;</p>
                        
                        <h6><strong>Subject:</strong></h6>
                        <p>' . htmlspecialchars($message['subject']) . '</p>
                        
                        <h6><strong>Status:</strong></h6>
                        <p><span class="badge badge-' . 
                            ($message['status'] == 'new' ? 'warning' : 
                             ($message['status'] == 'read' ? 'info' : 
                             ($message['status'] == 'replied' ? 'success' : 'secondary'))) . '">' . 
                            ucfirst($message['status']) . '</span></p>
                    </div>
                    <div class="col-md-6">
                        <h6><strong>Received:</strong></h6>
                        <p>' . date('F j, Y \a\t g:i A', strtotime($message['created_at'])) . '</p>
                        
                        ' . (!empty($message['replied_at']) ? '
                        <h6><strong>Replied:</strong></h6>
                        <p>' . date('F j, Y \a\t g:i A', strtotime($message['replied_at'])) . '</p>
                        ' : '') . '
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <h6><strong>Message:</strong></h6>
                        <div class="bg-light p-3 rounded">
                            <p>' . nl2br(htmlspecialchars($message['message'])) . '</p>
                        </div>
                    </div>
                </div>
            </div>';
        } else {
            echo '<p>Message not found.</p>';
        }
    } catch (PDOException $e) {
        echo '<p>Error loading message.</p>';
    }
} else {
    echo '<p>Invalid request.</p>';
}
?>