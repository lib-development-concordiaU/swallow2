<?php




require_once "../Model/db.config.php";
require_once "../Model/session.php";
require_once "../Model/clase.php";
require_once "../Model/taxonomy.php";
require_once "../Model/item.php";
require_once "../Model/permission.php";
require_once "./Components/Form/formfield.php";

isLogged($conn);

$objItem = new Item($conn);
$objItem->select($_GET['itemid']);

$objItemClass = new Clase($conn);
$objItemClass->select($objItem->class_id);
$objItemClass->getAncentry();


?>
<form name='select-class' id ='select-class' action="#">

<div class='step-subtitle'>
    Classification
    <span class='glyphicon glyphicon-question-sign' style='cursor:pointer' data-toggle='modal' data-target='#helpModal' onclick='loadhelp("")'></span>
</div>
<form id='stepClassForm' action='Controller/item-save-step-class.php'>

<?php
$objTaxonomy = new Taxonomy();
$objTaxonomy->load("../Definitions/".$_SESSION['currentSchema']."/Classification/taxonomy.json");

$levels = [];
$levels[] = ["label"=>$objTaxonomy->current->label,"definition"=>$objTaxonomy->current->definition];
while($objTaxonomy->hasChildren){
    $objTaxonomy = $objTaxonomy->getChildren();
    $levels[] =  ["label"=>$objTaxonomy->current->label,"definition"=>$objTaxonomy->current->definition];
}
?>
    <div class='form-group'>
    <?php
        $hidden  = new Formfield('hidden','itemid','itemid',$objItem->id);
        $hidden->render();
       
        $objClass = new Clase($conn);
        $parentid = 0;

        $objPermission = new Permission($conn);

        foreach($levels as $level){
            //get the options
            echo('<div>');
            $value = 0;
            $objClass->selectByDefinition($level['definition'],$parentid);
            $options = [];
            for($i =0; $i < $objClass->total; $i++){
                $objClass->go($i);
                //check if the looged user has permissions
                if($objPermission->hasAccess($_SESSION['swallow_uid'],$objClass->id)){
                    $options[] = ["label"=>$objClass->label,"value"=>$objClass->id];
                     
                    if($objItemClass->isAncentor($objClass->id)){
                        $value = $objClass->id;
                    }elseif($objItem->class_id == $objClass->id){ 
                        $value = $objClass->id;
                    }
                }
                
            }
            $parentid = $value;
            $onchange = "saveClass('".$level['label']."')";
            $field = new Formfield('dropdown',$level['label'],$level['label'],$value,$options,$onchange);
            $field->render();
            echo('<div>');
        }
    ?>

</form>
<script src='View/step-renderer-class.js'></script>
<?php 
$conn->close();
?>
