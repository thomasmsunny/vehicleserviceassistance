<?php
require_once 'includes/db_config.php';

echo "<h2>Contact Form Test</h2>";

try {
    $pdo = getDBConnection();
    
    // Insert a test message
    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, status, created_at) VALUES (?, ?, ?, ?, 'new', NOW())");
    $result = $stmt->execute(['Test User', 'test@example.com', 'Test Subject', 'This is a test message.', 'new']);
    
    if ($result) {
        $lastId = $pdo->lastInsertId();
        echo "<p style='color: green;'>Successfully inserted test message with ID: " . $lastId . "</p>";
        
        // Retrieve the message
        $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
        $stmt->execute([$lastId]);
        $message = $stmt->fetch();
        
        if ($message) {
            echo "<h3>Retrieved Message:</h3>";
            echo "<ul>";
            echo "<li><strong>ID:</strong> " . $message['id'] . "</li>";
            echo "<li><strong>Name:</strong> " . $message['name'] . "</li>";
            echo "<li><strong>Email:</strong> " . $message['email'] . "</li>";
            echo "<li><strong>Subject:</strong> " . $message['subject'] . "</li>";
            echo "<li><strong>Message:</strong> " . $message['message'] . "</li>";
            echo "<li><strong>Status:</strong> " . $message['status'] . "</li>";
            echo "<li><strong>Created:</strong> " . $message['created_at'] . "</li>";
            echo "</ul>";
            
            // Clean up - delete the test message
            $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
            $stmt->execute([$lastId]);
            echo "<p style='color: blue;'>Test message cleaned up.</p>";
        }
    } else {
        echo "<p style='color: red;'>Failed to insert test message.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
}
?>