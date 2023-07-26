<?php
require_once "../Model/db.config.php";
require_once "../Model/clase.php";
require_once "../Model/taxonomy.php";
require_once "./Components/Form/formfield.php";
require_once "./Components/Form/button/button.php";

function renderPermissionsSelector($conn,$selectedSchema,$selectedClass,$cataloguer_id){

    $objSelectedClass = new Clase($conn);
    $objSelectedClass->select($selectedClass);
    $ancentryFull = $objSelectedClass->getAncentry();
    $ancentryIDs = [];
    if(is_array($ancentryFull)){
        foreach($ancentryFull as $ancentorFull){
            $ancentryIDs[] = $ancentorFull['id'];
          }
    }
    

    $objTaxonomy = new Taxonomy();
    $objTaxonomy->load("../Definitions/".$selectedSchema."/Classification/taxonomy.json");
  
    $levels = [];
    $levels[] = ["label"=>$objTaxonomy->current->label,"definition"=>$objTaxonomy->current->definition];
    while($objTaxonomy->hasChildren){
        $objTaxonomy = $objTaxonomy->getChildren();
        $levels[] =  ["label"=>$objTaxonomy->current->label,"definition"=>$objTaxonomy->current->definition];
    }

    $objClass = new Clase($conn);  
    $parentid = 0;
    foreach($levels as $level){
        echo("<div class='form-group'>");
       //get the options
        $value = 0;
        $objClass->selectByDefinition($level['definition'],$parentid);
        $options = [];
        for($i =0; $i < $objClass->total; $i++){
            $objClass->go($i);
            $options[] = ["label"=>$objClass->label,"value"=>$objClass->id];
            if(in_array($objClass->id,$ancentryIDs)){
                $value = $objClass->id;
            }elseif($selectedClass == $objClass->id){
                $value = $objClass->id;
            }
        }
        $parentid = $value;

        $onchange = "changeClass('".$selectedSchema."','".$level['label']."',".$cataloguer_id.")";
        $field = new Formfield('dropdown',$level['label'],$level['label'],$value,$options,$onchange);
        $field->styles[] = ["id"=>"{label-styles}","css"=>"width:16.66%"];
        $field->render();

        $onclick = "addClassPermission('".$level['label']."',".$cataloguer_id.")";
        $objBtn = new Button($level['label']."-btn",true, "Add", 'button',$onclick);
        
        $objBtn->render();
        echo("</div>");
    }
}

if(isset($_GET['selectedClass'])){
    $selectedClass = $_GET['selectedClass'];
}else{
    $selectedClass = 0;
}

if(isset($_GET['selectedSchema'])){
    $selectedSchema= $_GET['selectedSchema'];
}else{
    $selectedSchema = $_SESSION['currentSchema'];
}

if($selectedSchema > -1){
    renderPermissionsSelector($conn,$selectedSchema,$selectedClass,$_GET['cataloguerid']);
}

?>