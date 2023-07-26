<?php
include_once "../Model/db.config.php";
include_once "../Model/permission.php";

$objPermission = new Permission($conn);
$response = '';
if(isset($_GET['cataloguer_id']) and isset($_GET['class_id'])){
    $result = $objPermission->deleteCatClass($_GET['cataloguer_id'],$_GET['class_id']);
    $response = "{'result':'".$result."'}";
}else{
    $response = "{'error':'cataloguer or class id not defined'}";
}

echo($response);
?>