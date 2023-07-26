<?php
require_once "../Model/db.config.php";
require_once "../Model/session.php";
require_once "../Model/log.php";
require_once "../Model/item.php";


isLogged($conn);

//get the proper dataset
$objitem = new Item($conn);

$objitem->metadataQuery($_GET['query'],$_GET['cataloguer'],$_GET['selectedClass'],-1,'',$_GET['schema']);
$objitem->deletelist();

//kill them all 

$conn->close();
?>
