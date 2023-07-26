<?php
require_once "../Model/db.config.php";
require_once "../Model/clase.php";
require_once "../Model/session.php";

isLogged($conn);

$objClass = new Clase($conn);
$objClass->select($_POST['classid']);
$objClass->label= $_POST['label'];
$objClass->uri = $_POST['uri'];

$fields = $objClass->objMetadata->getFields();
foreach($fields as $field){
    
    $objClass->objMetadata->setValue($field["name"], trim($_POST[str_replace(" ","_",$field["name"])]));
}

$result = $objClass->save();

$conn->close();

echo($_POST['classid']);
?>