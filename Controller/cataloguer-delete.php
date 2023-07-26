<?php
require_once "../Model/db.config.php";
require_once "../Model/cataloguer.php";
require_once "../Model/session.php";
isLogged($conn);
isAdmin($conn);

$cataloguer_id = $_GET['id'];

$objCataloguer = new Cataloguer($conn);
$objCataloguer->delete($cataloguer_id);

//delete the items
$objItems = new Item($conn);
$objItems->deleteByCataloguerID($cataloguer_id);

//delete the permissions
$objPermission = new Permission($conn);
$objPermission->deleteByCataloguer($cataloguer_id);

$conn->close();
?>