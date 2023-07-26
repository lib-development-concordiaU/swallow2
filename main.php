<?php
require_once "Model/db.config.php";
require_once "Model/session.php";
require_once "Model/cataloguer.php";

isLogged($conn);

$objCataloguer = new Cataloguer($conn);
$objCataloguer->select($_SESSION['swallow_uid']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>SWALLOW | Metadata Ingestion System</title>
  <meta charset="utf-8">
  
  <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png">
  <link rel="manifest" href="images/site.webmanifest">
  <link rel="mask-icon" href="images/safari-pinned-tab.svg" color="#5bbad5">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="theme-color" content="#ffffff">

  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
  <link href="./styles.css" rel="stylesheet" type="text/css" />


  
</head>
<body>
    <div id="mm-top-bar" class="mm-top-bar">
        <div id="mm-left" class="mm-left col-sm-2">
        <img src="images/logo-menu-image.png">
        </div>
        <div id="mm-center" class="mm-center col-sm-8">
            <div id="li_mmnu_change_schema" class="main-menu">
                <label for="schema"> Current Schema </label>
                <select id='schema' style="padding: 5px; width: auto; height:30px" onChange="changeCurrentSchema()">
                  <?php
                    foreach($GLOBALS['availableSchemas']  as $schema){
                      if($schema == $_SESSION['currentSchema']){
                        $selected = 'selected';
                      }else{
                        $selected = '';
                      }
                      echo("<option value='".$schema."' ".$selected.">".$schema."</option>");
                    }
                  ?>

                </select>
              </div>
        </div>
        
        <div id="mm-right" class="mm-right col-sm-2">
                          
            <div style="position:relative; float:right; padding-top: 10px;">
                  <a class="dropdown-toggle " href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="glyphicon glyphicon-menu-hamburger mm-icon"></span>   
                  </a>

                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <?php echo($objCataloguer->name." ".$objCataloguer->lastname) ?> 
                    <hr  class="mm-hr" />
                    <a  class="mm-menu-item" id="mmnu_manage_items" href="#">
                        <img src="images/menu-icon-item.png" style="height:30px">
                        <span>Items</span>
                    </a>
                    <a  id="mmnu_manage_collections" href="#" class="mm-menu-item">
                        <img src="images/menu-icon-collection.png" style="height:30px">
                        <span>Classes</span>
                    </a>  

                    <hr class="mm-hr" />

                    <?php if(isAdmin($conn)){?>
                       
                        <a class="mm-menu-item" id='mmnu_manage_users' href='#' >
                            <img src='images/menu-icon-cataloguer.png' style='height:30px'>
                            <span>Cataloguers</span>
                        </a> 
                      
                   
                        <a class="mm-menu-item"  id="mmnu_import" href="#">
                            <img src="images/menu-icon-import.png" style="height:30px">
                            <span>Import</span>
                        </a>
                  
                        <a class="mm-menu-item"  id="mmnu_export" href="#">
                            <img src="images/menu-icon-export.png" style="height:30px">
                            <span >Export</span>
                        </a>
                  
                    <?php  }  ?>

                    <hr class="mm-hr" />
                    <a class="mm-menu-item" href="#" id="mmnu_myProfile">My Profile</a>
                    <a class="mm-menu-item" href="Controller/logout.php">Logout</a>
                </div>
            
            </div>

        </div>
    </div>


    <div id="main">
    </div>

</body>
<script src='main.js' ></script>
</html>