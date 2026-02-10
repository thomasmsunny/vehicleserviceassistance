<?php
require('../config/autoload.php');

// Destroy all session data
session_unset();
session_destroy();

// Redirect to driver login page
header("Location: driverlogin.php");
exit();
?>
