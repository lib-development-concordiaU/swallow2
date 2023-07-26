<?php 
require_once "../Model/db.config.php";
require_once "../Model/clase.php";
require_once "../Model/cataloguer.php";
require_once "../Model/session.php";
isLogged($conn);

$objCataloguer = new Cataloguer($conn);
$objCataloguer->select($_SESSION["swallow_uid"]);

$objClass = new Clase($conn);
$result = $objClass->create($_SESSION['currentSchema']."/Classification/".urldecode($_GET['definition']),$_GET['parentid']);

echo $result;

$conn->close();
?>