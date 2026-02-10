<?php   
include("dbconnect.php");
$id = $_GET['id'];
$sql = "delete from drivermanage where did=".$id;

$conn->query(query: $sql);

 header('location:driverviews1.php');



?>
