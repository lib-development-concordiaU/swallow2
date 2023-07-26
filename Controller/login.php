<?php
require_once "../Model/db.config.php";
require_once "../Model/cataloguer.php";
require_once "ldap/ldapAuth.php";

$in_email = $_POST['login'];
$in_pwd = $_POST['pwd'];

if($ldapAunthentication){

    $email = LDAPAuth::netnameLogin($in_email,$in_pwd);
    if($email !== false){
        $objCataloguer = new Cataloguer($conn);
        $objCataloguer->selectFromEmail($email);  
        if($objCataloguer->total > 0){
            session_start();
            $_SESSION['swallow_uid'] = $objCataloguer->id;
            header("Location: ../main.php");
        }else{
            header("Location: ../index.php?err=1");  
        }

    }else{
        header("Location: ../index.php?err=1");
    }

}else{
    $objCataloguer = new Cataloguer($conn);
    $uid = $objCataloguer->authenticate($in_email,$in_pwd); 

    if ( $uid !== false ){

        session_start();
        $_SESSION['swallow_uid'] = $uid;
        header("Location: ../main.php");

    }else{
        header("Location: ../index.php?err=1");
    }
}

?>
