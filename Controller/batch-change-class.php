<?php
require_once "../Model/db.config.php";
require_once "../Model/session.php";
require_once "../Model/log.php";
require_once "../Model/item.php";


isLogged($conn);

//get the proper dataset
$objItem = new Item($conn);

$objItem->metadataQuery(base64_decode($_GET['query']),$_GET['cataloguer'],$_GET['selectedClass'],-1,'',$_GET['schema']);

//var_dump(base64_decode($_GET['query']));

for($i = 0; $i < $objItem->total ; $i++){
    $objItem->go($i);
    $objItem->class_id = $_GET['newclass'];
    $objItem->save();
}

$conn->close();
?>
