<?php
require_once "../Model/db.config.php";
require_once "../Model/item.php";
require_once "../util.php";
require_once "../Model/session.php";
isLogged($conn);

if(isset($_GET['itemid'])){
    $objitem = new Item($conn);
    $objitem->select($_GET['itemid']);

    if ( $objitem->export == 1 ){
        $objitem->export = 0;
    }else{
        $objitem->export = 1;
    }

    $objitem->save();
    echo( $objitem->id );
}else{
    echo("-1");
}

$conn->close();


?>