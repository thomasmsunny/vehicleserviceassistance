<?php 


//your project path goes here
define("BASE_URL","http://localhost/project/vehicleservice/");
define("BASE_PATH","D:/wamp64/www/project/vehicleservice/");

//set your timezone here
date_default_timezone_set('asia/kolkata');





 if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 require(BASE_PATH.'config/database.php'); 
 require( BASE_PATH .'classes/database.php'); 
 require( BASE_PATH .'classes/FormAssist.class.php'); 
 require(BASE_PATH.'classes/FormValidator.class.php'); 
 require( BASE_PATH .'classes/DataAccess.class.php');

?>