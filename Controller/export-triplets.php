<?php
require_once "../Model/db.config.php";
require_once "../Model/item.php";
require_once "../Model/clase.php";
require_once "../Model/cataloguer.php";
require_once "../Model/session.php";
require_once "../Model/workflow.php";
//isLogged($conn);

/*
DESCRIPTION: Traverses all metadata fields values on the item and creates a multidimensional array with uris from the workflow and the vocabulary files*/

function generateTriplets($in_item,$in_conn,$subject){
    
     //load the schema
     $objWorkflow = new Workflow($in_conn);    
     $objWorkflow->loadFromVersion($in_item->schema_definition);
     
     
     $triplets = [];
     $metadata = $in_item->metadata;
     $stepsKeys = array_keys($metadata);
          
     for($i = 0; $i < count($stepsKeys); $i++){
        $stepName = $stepsKeys[$i];
        $stepData = $metadata[ $stepName ];
        $stepSchema = $objWorkflow->getStep( $stepName );

        $dataFields= array_keys($stepData);

        if($stepSchema != false){
            if($stepSchema->type == 'single'){                        
                $triplets = array_merge($triplets, getStepTriplets($dataFields, $stepData, $subject, $objWorkflow, $stepName));
            }else{
                foreach($stepData as $stepDataElement){
                    $triplets = array_merge($triplets, getStepTriplets($dataFields, $stepDataElement, $subject, $objWorkflow, $stepName));
                }
            }

        }//if($stepSchema != false){
    }
    
    return $triplets;
}

function getStepTriplets($dataFields, $stepData, $subject, $objWorkflow, $stepName){
    $triplets = [];
    $dataFields = $objWorkflow->getFields($stepName); 
    foreach($dataFields as $dataField){
        if( isset( $dataField->uri) ){ //has a defines property
            $predicate = $dataField->uri;
            // now lets find if the object is a url or a literal
            if(isset( $stepData[ str_replace(' ', '_', $dataField->name ) ] )){
                $fieldValue = $stepData[ str_replace(' ', '_', $dataField->name ) ];    
            }else{
                $fieldValue = null;
            }

            if(is_array($fieldValue) ){
                foreach($fieldValue as $value){
                    
                    $object = getObject($objWorkflow, $stepName, $dataField, $value['value']);
                    $triplets[] = ["s"=>$subject,"p"=>$predicate,"o"=>$object];
                }
            }elseif($fieldValue != null){
                $object = getObject($objWorkflow, $stepName, $dataField, $fieldValue);
                $triplets[] = ["s"=>$subject,"p"=>$predicate,"o"=>$object];
            }                   
        }
    }
    return $triplets;
}

function getObject($objWorkflow, $stepName, $dataField, $fieldValue){

    if( $objWorkflow->getVocabulary($dataField->name) ){ //has a vocabulary file
        $object = $objWorkflow->getURI($stepName ,$dataField->name ,$fieldValue);
        if($object == false){
            $object = $fieldValue;
        }
    }else{ // obhect is a literal
        $object = $fieldValue;
    }
    
    return $object;
}


$metadataquery = base64_decode($_GET['query']);
$cataloguer = $_GET['cataloguer'];
$class = $_GET['class'];

if(isset($_GET['schema'])){
    $schema = $_GET['schema'];
}else{
    $schema = -1;
}

if(isset($_GET['format'])){
    $format = $_GET['format'];
}else{
    $format = 'json';
}

if(isset($_GET['orderby'])){
    $orderby = $_GET['orderby'];
}else{
    $orderby = '';
}

if(isset($_GET['swallowID'])){
    $swallowID = $_GET['swallowID'];
}else{
    $swallowID = -1;
}



$objItem = new Item($conn);

if($swallowID > 0){
    $objItem->select($swallowID);
}else{
    $objItem->metadataQuery($metadataquery,$cataloguer,$class,-1,$orderby,$schema);
}


$objCataloguer = new Cataloguer($conn);


$fulldataset = [];

for($i = 0; $i < $objItem->total;$i++){   
    $objItem->go($i);
    $subject = $GLOBALS['baseURL'] . "item/" . $objItem->id;
    $triplets = [];

    $objClass = new Clase($conn);
    $objClass->select($objItem->class_id);

    $record["classification"][] = $objClass->getMetadataArray();
    $objClass->getAncentry();
    $temp = new Clase($conn);

    foreach($objClass->ancentry as $ancestor){
       $temp->select($ancestor["id"]);
       //https://www.wikidata.org/wiki/Property:P361 ---- part of
        $metadataArray = $temp->getMetadataArray();
        foreach($metadataArray as $key => $value){
            if($key == "URI"){
                if($value !== ''){
                    $triplets[]=  ["s"=>$subject,"p"=>"https://www.wikidata.org/wiki/Property:P361","o"=>$value];
                }
            }
        }
    }

    if($objItem->metadata != NULL){
        $triplets  = array_merge($triplets,generateTriplets($objItem,$conn,$subject));
    }

    $fulldataset[] = $triplets;

}


header('Content-Type: application/json; charset=utf-8');
echo(json_encode($fulldataset));

$conn->close();

?>
