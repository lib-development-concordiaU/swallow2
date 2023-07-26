<?php
require_once "../Model/db.config.php";
require_once "../Model/clase.php";
require_once "../Model/cataloguer.php";
require_once "../Model/item.php";
require_once "../Model/permission.php";
require_once "../Model/session.php";
isLogged($conn);

function deleteClass($class_id,$conn){
    $objClass = new Clase($conn);
    $objClass->select($class_id);

    //delete the items
    $objItems = new Item($conn);
    $objItems->deleteByClassID($class_id);

    //delete the permissions
    $objPermission = new Permission($conn);
    $objPermission->deleteByClassID($class_id);

    //delete teh children
    $objChildren = new Clase($conn);
    $objChildren->selectChildren($class_id);
    for($j = 0; $j < $objChildren->total;$j++){
        deleteClass($objChildren->id,$conn);
    }

    $objClass->delete();
}

deleteClass($_GET['id'],$conn);
$conn->close();
?>