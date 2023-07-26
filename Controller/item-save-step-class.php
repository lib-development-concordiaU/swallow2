<?php
require_once "../Model/db.config.php";
require_once "../Model/item.php";
require_once "../util.php";
require_once "../Model/session.php";
require_once "../Model/log.php";


isLogged($conn);

$objItem = new Item($conn);
$objItem->select($_GET['itemid']);
$objItem->class_id = $_GET['classid'];
$objItem->save();

echo("{\"itemid\":\"".$_GET['itemid']."\"}");

$conn->close()
?>