<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
require_once '../customer/includes/db_config.php';

// Handle status update
$message_updated = '';
$message_error = '';

if (isset($_POST['update_status']) && is_numeric($_POST['message_id'])) {
    $message_id = $_POST['message_id'];
    $status = $_POST['status'];
    
    try {
        $pdo = getDBConnection();
        
        if ($status === 'replied') {
            // Update status and set replied_at timestamp
            $stmt = $pdo->prepare("UPDATE contact_messages SET status = ?, replied_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $message_id]);
        } else {
            // Update status only
            $stmt = $pdo->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
            $stmt->execute([$status, $message_id]);
        }
        
        if ($stmt->rowCount() > 0) {
            $message_updated = "Message status updated successfully.";
        } else {
            $message_error = "Failed to update message status.";
        }
    } catch (PDOException $e) {
        $message_error = "Database error occurred.";
    }
}

// Handle message deletion
if (isset($_POST['delete_message']) && is_numeric($_POST['message_id'])) {
    $message_id = $_POST['message_id'];
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$message_id]);
        
        if ($stmt->rowCount() > 0) {
            $message_updated = "Message deleted successfully.";
        } else {
            $message_error = "Failed to delete message.";
        }
    } catch (PDOException $e) {
        $message_error = "Database error occurred.";
    }
}

// Fetch contact messages
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC");
    $stmt->execute();
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    $messages = [];
    $message_error = "Failed to fetch messages.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <style>
        .status-new { background-color: #ffc107; color: #000; }
        .status-read { background-color: #17a2b8; color: white; }
        .status-replied { background-color: #28a745; color: white; }
        .status-archived { background-color: #6c757d; color: white; }
        .message-preview { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    </style>
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <div class="container-fluid page-body-wrapper">
        <div class="main-panel">
            <div class="content-wrapper">
                <div class="row">
                    <div class="col-lg-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Contact Messages</h4>
                                <p class="card-description">View and manage customer contact messages</p>
                                
                                <?php if (!empty($message_updated)): ?>
                                    <div class="alert alert-success"><?php echo $message_updated; ?></div>
                                <?php endif; ?>
                                
                                <?php if (!empty($message_error)): ?>
                                    <div class="alert alert-danger"><?php echo $message_error; ?></div>
                                <?php endif; ?>
                                
                                <div class="table-responsive">
                                    <table id="messagesTable" class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Subject</th>
                                                <th>Message</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                                <th>Replied</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($messages as $message): ?>
                                            <tr>
                                                <td><?php echo $message['id']; ?></td>
                                                <td><?php echo htmlspecialchars($message['name']); ?></td>
                                                <td><?php echo htmlspecialchars($message['email']); ?></td>
                                                <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                                <td class="message-preview" title="<?php echo htmlspecialchars($message['message']); ?>">
                                                    <?php echo htmlspecialchars(substr($message['message'], 0, 50)) . (strlen($message['message']) > 50 ? '...' : ''); ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php 
                                                        echo $message['status'] == 'new' ? 'warning' : 
                                                             ($message['status'] == 'read' ? 'info' : 
                                                             ($message['status'] == 'replied' ? 'success' : 'secondary')); 
                                                    ?>">
                                                        <?php echo ucfirst($message['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y H:i', strtotime($message['created_at'])); ?></td>
                                                <td>
                                                    <?php 
                                                    echo !empty($message['replied_at']) ? 
                                                        date('M j, Y H:i', strtotime($message['replied_at'])) : 
                                                        'Not replied'; 
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-primary" 
                                                                onclick="viewMessage(<?php echo $message['id']; ?>)">
                                                            View
                                                        </button>
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" 
                                                                    data-toggle="dropdown">
                                                                Status
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                <form method="post" style="display: inline;">
                                                                    <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                                    <button type="submit" name="update_status" value="new" class="dropdown-item">New</button>
                                                                    <button type="submit" name="update_status" value="read" class="dropdown-item">Read</button>
                                                                    <button type="submit" name="update_status" value="replied" class="dropdown-item">Replied</button>
                                                                    <button type="submit" name="update_status" value="archived" class="dropdown-item">Archived</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                        <form method="post" style="display: inline;" 
                                                              onsubmit="return confirm('Are you sure you want to delete this message?')">
                                                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                            <button type="submit" name="delete_message" class="btn btn-sm btn-danger">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'includes/admin_footer.php'; ?>
        </div>
    </div>

    <!-- Message Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Contact Message</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="messageContent">
                    <!-- Message content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#messagesTable').DataTable({
                "order": [[ 0, "desc" ]],
                "pageLength": 25
            });
        });
        
        function viewMessage(messageId) {
            $.ajax({
                url: 'get_contact_message.php',
                method: 'POST',
                data: { id: messageId },
                success: function(response) {
                    $('#messageContent').html(response);
                    $('#messageModal').modal('show');
                },
                error: function() {
                    alert('Failed to load message details.');
                }
            });
        }
    </script>
</body>
</html>