<?php
require_once "../Model/db.config.php";
require_once "../Model/Workflow.php";
require_once "../Model/session.php";
require_once "../Model/item.php";
isLogged($conn);

$objWorkflow = new Workflow();

$objItem = new Item($conn);
$objItem->select($_GET['itemid']);

$objWorkflow->load('../Definitions/'.$objItem->schema_definition.'/workflow.json');

?>

<h2>
    <a href="#" onclick="loadMainPage('View/items.php')">Items</a> > Editing Item with SWALLOW ID: <?php echo($_GET['itemid']) ?>
</h2>

<div>
    <div class="item-title">
        <?php 
            
            echo("<h1 id='item-title'> $objItem->title </h1>");
        ?>
    </div>
    <div class="col-sm-3 sidenavInner" id="sub-menu" >
        <ul class="nav nav-pills nav-stacked">
        <li id="mnu_li_classification" class='light-menu-li'>  
            <a href="#" onclick = "renderClassification(<?php echo($_GET['itemid'])?>)">Classification</a>
        </li>

        <?php foreach ($objWorkflow->steps as $step){
            $menu_item_id = "mnu_li_".str_ireplace(' ','_',$step->name);
            echo("
            <li id='".$menu_item_id."' class='light-menu-li'>
                <a id='' href='#' onclick = \"renderStep('".urlencode($step->name)."','".$step->type."',".$_GET['itemid'].",'".$menu_item_id."')\">".str_replace('_',' ',$step->name)."</a>
            </li>  
            ");
        } 
        
        ?>
        <hr />
        <?php
        if( $objItem->cataloguer_id == $_SESSION['swallow_uid'] or (isAdmin($conn)) ){
            if($objItem->locked == 1){
                $lockedTxt = "Unlock";
            }else{
                $lockedTxt = "Lock";
            }

            if($objItem->export == 1){
                $exportTxt = "Hide in export";
            }else{
                $exportTxt = "Show in export";
            }
        ?>    
        <li class='light-menu-li lock-button'>  
            <a href="#" onclick = "toggleExport('<?php echo($objItem->id)?>')"><?php echo($exportTxt)?></a>
        </li>
        <li class='light-menu-li lock-button'>  
            <a href="#" onclick = "toggleLock('<?php echo($objItem->id)?>')"><?php echo($lockedTxt)?></a>
        </li>
        <?php
        }
        ?>
        <li class='light-menu-li done-button'>  
            <a href="#" onclick = "loadMainPage('View/items.php')">Done</a>
        </li>
        </ul>
    </div>


    <div class="col-sm-7" id="stepContainer"  >

    </div>

</div>

<script src = 'View/breadcrumbs.js'></script>
<script src = 'View/deposit-item.js'></script>
<?php
$conn->close();
?>

