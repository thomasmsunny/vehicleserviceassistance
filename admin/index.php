<?php
// Redirect to dashboard or login based on session
session_start();

if(isset($_SESSION['admin_id']) && $_SESSION['user_role'] === 'admin') {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit();
?>
