<?php
require_once "../Model/db.config.php";
require_once "../Model/item.php";
require_once "../Model/clase.php";
require_once "../Model/cataloguer.php";
require_once "../Model/session.php";
require_once "../Model/workflow.php";
require_once "../Model/metadata.php";
//isLogged($conn);

/*
DESCRIPTION: Traverses all metadata fields values on the item and adds the uri from the vocabulary file
*/


function prepareMetadata($in_item,$in_conn){
    //load the schema
    $objWorkflow = new Workflow($in_conn);    
    $objWorkflow->loadFromVersion($in_item->schema_definition);
    
    $result = [];
    $metadata = $in_item->metadata;
    $stepsKeys = array_keys($metadata);
    
    for($i = 0; $i < count($stepsKeys); $i++){
        $stepName = $stepsKeys[$i];
        $stepData = $metadata[ $stepName ];
        $stepSchema = $objWorkflow->getStep( $stepName );
        $result[$stepName] = [];

        if($stepSchema != false){

            if($stepSchema->type == 'single'){
                $objWorkflow->getFields($stepName);    
                $dataFields = array_keys($stepData);
                for($j = 0; $j < count($dataFields); $j++){
                    $fieldname = $dataFields[$j];
                    $fieldValue = $stepData[$fieldname];
                    if($fieldValue != '' && $fieldValue != '-1'){
                        $result[$stepName][$fieldname] = $fieldValue;
                        //check for URIs in the controlled vocabularies
                        if($objWorkflow->getVocabulary($fieldname)){
                            $uri = $objWorkflow->getURI($stepName ,$fieldname ,$fieldValue);
                            if($uri !== false){
                                $result[$stepName][$fieldname."_uri"] = $uri;
                            }
                        }
                    }
                    
                }

            }else{
                $objWorkflow->getFields($stepName); 
                
                foreach($stepData as $stepDataElement){
                    $resultElement = [];
                    $dataFields = array_keys($stepDataElement);
                    for($j = 0; $j < count($dataFields); $j++){
                        $fieldname = $dataFields[$j];
                        $fieldValue = $stepDataElement[$fieldname];
                        
                        if($fieldValue != "" && $fieldValue != '-1' ){
                            $resultElement[$fieldname] = $fieldValue;
                            //check for URIs in the controlled vocabularies
                        
                            if($objWorkflow->getVocabulary($fieldname)){
                                
                                $uri = $objWorkflow->getURI($stepName ,$fieldname ,$fieldValue);
                                if($uri !== false){
                                    $resultElement[$fieldname."_uri"] = $uri;
                                }
                            }
                        }
                        
                    }
                    
                    $result[$stepName][] = $resultElement;
                }
            
            }
        } //if($stepSchema != false){
    }
    return $result; 
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
    $record = [];
    $objItem->go($i);
    if($objItem->export == true){
        $objCataloguer = new Cataloguer($conn);
        $record["schema"] = "Swallow JSON";
        $record["schema_definition"] = $objItem->schema_definition;
        $record["swallow_id"] = $objItem->id;
        $record["create_date"] = $objItem->create_date;
        $record["last_modified"] = $objItem->last_modified;

        
        $objCataloguer->select($objItem->cataloguer_id);
        $record["cataloguer"]["name"] = $objCataloguer->name;
        $record["cataloguer"]["lastname"] = $objCataloguer->lastname;
      //  $record["cataloguer"]["email"] = $objCataloguer->email;



    // --------------------------------------------------------------------------------------
    //------------------- Get the classification information --------------------------------
    // --------------------------------------------------------------------------------------

        $objClass = new Clase($conn);
        $objClass->select($objItem->class_id);
    
        if($objClass->export){
            $record["classification"][] = array_merge($objClass->getMetadataArray(),["class_name"=>getClassNameFromSchemafile($objClass->schemaDefinition)]); 
            
            $objClass->getAncentry();
            $temp = new Clase($conn);

            $export = true;
            foreach($objClass->ancentry as $ancestor){
                if($export){
                    $temp->select($ancestor["id"]);
                    if($temp->export == 0){
                        $export = false;
                    }else{
                        $record["classification"][] = array_merge($temp->getMetadataArray(),["class_name"=>getClassNameFromSchemafile($temp->schemaDefinition)]);
                    }       
                }   
            }

            if($export){
                if($objItem->metadata != NULL){
                    $fulldataset[] = $record + prepareMetadata($objItem,$conn);
                }else{
                    $fulldataset[] = $record + [];
                } 
            }


        }
    }
    
}


function getClassNameFromSchemafile($path){
    // read the json file in  "../Definitions/".$path;
    $contents = file_get_contents("../Definitions/".$path);
    //change encoding of $contents to  utf-8
    $contents = mb_convert_encoding($contents, 'UTF-8');
    $decoded = json_decode($contents);
    return $decoded->name;

}

header('Content-Type: application/json; charset=utf-8');
echo(json_encode($fulldataset));
//var_dump($fulldataset);
$conn->close();

?>
