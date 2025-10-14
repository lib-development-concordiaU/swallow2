<?php
include "../../Model/db.config.php";
include "./phpqrcode/qrlib.php";


if (isset($_GET['url'])){
    
    $data = $_GET['url'];
    $filename  = md5($data).'.png';
    $path = 'images/'.$filename; 
    QRcode::png($data,$path,QR_ECLEVEL_L,5);
    echo($GLOBALS['baseURL'].'Widgets/qr_generator/'.$path);
}

?>
