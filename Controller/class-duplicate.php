<?php
require_once "../Model/db.config.php";
require_once "../Model/clase.php";
require_once "../Model/cataloguer.php";
require_once "../Model/item.php";
require_once "../Model/session.php";
isLogged($conn);


function duplicateClass($class_id,$parent_id,$conn){
    $objDuplicateClass = new Clase($conn);
    $objOriginalClass = new Clase($conn);
    $objOriginalClass->select($class_id);
    if($parent_id == -1){
        $parent_id = $objOriginalClass->parentID;
    }

    $result = $objDuplicateClass->create($objOriginalClass->schemaDefinition,$parent_id);

    $objDuplicateClass->select($result);
    $objDuplicateClass->label = $objOriginalClass->label." - COPY";
    $objDuplicateClass->uri = $objOriginalClass->uri;
    $objDuplicateClass->objMetadata = $objOriginalClass->objMetadata;
    $objDuplicateClass->save();

    //duplicate all items 
    $objItems = new Item($conn);
    $objItems->selectClassID($objOriginalClass->id);
    for($i=0;$i < $objItems->total;$i++){
        $objItems->go($i);
        $objItems->duplicate($objDuplicateClass->id);
    }

    $objChildren = new Clase($conn);
    $objChildren->selectChildren($class_id);
    for($j = 0; $j < $objChildren->total; $j++){
        $objChildren->go($j);
        duplicateClass($objChildren->id,$objDuplicateClass->id,$conn);
    }

}

duplicateClass($_GET['id'],-1,$conn);
$conn->close();

?>