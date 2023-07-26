<?php

function renderClassificationFilters($conn,$selectedClass,$page){

    $hidden  = new Formfield('hidden','selectedClass','selectedClass',$selectedClass);
    $hidden->render();
  
    $objSelectedClass = new Clase($conn);
    $objSelectedClass->select($selectedClass);
    $objSelectedClass->getAncentry();
    $ancentryFull = $objSelectedClass->ancentry;
    $ancentryIDs = [];
    foreach($ancentryFull as $ancentorFull){
      $ancentryIDs[] = $ancentorFull['id'];
    }
  
    $objTaxonomy = new Taxonomy();
    $objTaxonomy->load("../Definitions/".$_SESSION['currentSchema']."/Classification/taxonomy.json");
  
    $levels = [];
    $levels[] = ["label"=>$objTaxonomy->current->label,"definition"=>$objTaxonomy->current->definition];
    while($objTaxonomy->hasChildren){
        $objTaxonomy = $objTaxonomy->getChildren();
        $levels[] =  ["label"=>$objTaxonomy->current->label,"definition"=>$objTaxonomy->current->definition];
    }
  
    $objClass = new Clase($conn);  
    $parentid = 0;
    foreach($levels as $level){
        //get the options
        echo('<div>');
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
        $onchange = "filterClass('".$level['label']."',".$page.")";
        $field = new Formfield('dropdown',$level['label'],$level['label'],$value,$options,$onchange);
        $field->addStyle('{label-styles}','width:auto');
        $field->render();
        echo('</div>');
    }
      
  }


?>
