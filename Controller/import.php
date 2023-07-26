<?php
require_once "../Model/db.config.php";
require_once "../Model/session.php";
require_once "../Model/log.php";
require_once "../Model/map.php";
require_once "../Model/cataloguer.php";
require_once "../Model/clase.php";
require_once "../Model/item.php";


isLogged($conn);
set_time_limit(0);
/*
Parameters: 
$cataloguer_info,$collection_info,$metadata_info are Assoc Arrays
$schema_definition id a string

This function store the record on the database
*/
function createRecord($cataloguer_info,$class_id,$title,$schema_definition,$metadata_info,$conn,$is_preview){
    //get the cataloguer
    $error = "";
    $objCataloguer = new Cataloguer($conn);
    //if(array_key_exists('email',$cataloguer_info)){
    if(isset($cataloguer_info['email'])){
        $objCataloguer->selectFromEmail($cataloguer_info['email']);
   //}elseif(array_key_exists('name',$cataloguer_info) and array_key_exists('lastname',$cataloguer_info)){
    }elseif(isset($cataloguer_info['name']) and isset($cataloguer_info['lastname'])){
        $objCataloguer->selectFromName($cataloguer_info['name'],$cataloguer_info['lastame']);
    }else{ 
        $error.=" ERROR: Can't find the cataloguer. Create the cataloguer before importing the records.";
    }

    //check if the class exists

    if($objCataloguer->total > 0 and $class_id > 0){
        $objItem = new Item($conn);
        //find if there's another item with the same title
        $itemExists = $objItem->exists($title,$class_id);

        if(!$is_preview){

                $id = $objItem->create($objCataloguer->id,$schema_definition);
                $objItem->select($id);
                $objItem->class_id = $class_id;
                $objItem->title = $title;
                $objItem->metadata = $metadata_info;
                $objItem->save();       
                $error .= " Item with title \"$title\" was imported with id= $id.";

        }else{
            $error .= " Item with title \"$title\" OK.";
           
        }

        if($itemExists){
            $error .= " WARNING: Item with title \"$title\" already exists.";
        }
        
    }else{
        $error.=" ERROR: Won't import record: No cataloguer or class information.";
    }

    return $error;

}


function createAssoc($header,$row){

    $result = array();
    $index = 0;
    foreach($header as $field){
        $result[$field] = $row[$index];
        $index++;
    }
    return $result;
}


function loadCSV($csvPath,$objMap,$conn,$is_preview){
    $fp = fopen($csvPath,'r');
    $header = fgetcsv($fp);

    $report = array();
    $lineNumber = 1;
    while(! feof($fp)){
        $row = fgetcsv($fp);

        if($row !== false){
            $record = createAssoc($header,$row);
        
            $mappedRecord = $objMap->maprecord($record,'CSV');

            
            if($mappedRecord[4] != ""){
                $error = "ERROR: Can't import item in line $lineNumber. Cause: ".$mappedRecord[4];
            }else{
                $error = createRecord($mappedRecord[0],$mappedRecord[1],$mappedRecord[2],$objMap->target_schema,$mappedRecord[3],$conn,$is_preview);
            }
            
            $report[] = array("message"=>$error);
            $lineNumber++;
        }   
        
    }

    return $report;
    
}

function file_get_contents_utf8($fn) {
    $content = file_get_contents($fn);
     return mb_convert_encoding($content, 'UTF-8',
         mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
}

function loadJSON($jsonPath,$objMap,$conn,$is_preview){
    $contents = file_get_contents_utf8($jsonPath);   
    $source = json_decode($contents,TRUE);
    $lineNumber = 1;
    $report = [];

    if(!is_null($source)){
        foreach($source as $record){
        
            $mappedRecord = $objMap->maprecordJSON($record,'JSON'); 
         
              if($mappedRecord[4] != ""){
                  $error = "ERROR: Can't import item number $lineNumber. Cause: ".$mappedRecord[4];
              }else{
                  $error = createRecord($mappedRecord[0],$mappedRecord[1],$mappedRecord[2],$objMap->target_schema,$mappedRecord[3],$conn,$is_preview);
              }
              
              $report[] = array("message"=>$error);
              $lineNumber++;
          }
    }else{
        $report[] = array("message"=>"ERROR parsing JSON file: ".json_last_error_msg()); 
    }

    return $report;

}

$report = array();


if(isset($_FILES['fname'])){
    $filename = $_FILES['fname']['name'];
    
    //echo($_FILES['fname']['error']);

    $dataFile = "../Uploads/Imports/".basename($filename );       
    //should make some validation before uploading the file
    $allowed =  array('csv','json');
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if(in_array($ext,$allowed) ) {
    
        move_uploaded_file($_FILES["fname"]["tmp_name"],$dataFile);
        
        if($_POST['map'] != ''){

            $objMap = new Map();
            $objMap->load($_POST['map']);
            
            if(isset($_POST['is_preview'])){
                $preview = true;
            }else{
                $preview = false;
            }

            if($objMap->source_file_type == "CSV"){    
                $report = loadCSV($dataFile,$objMap,$conn,$preview);    
            }elseif($objMap->source_file_type == "JSON"){
                $report = loadJSON($dataFile,$objMap,$conn,$preview);  
            }

        }else{
            $report[] = array("message"=>'ERROR: No mapping function was selected'); ;
        }
    }else{
        $report[] = array("message"=>'ERROR: Unrecognized file format. Only CSV and JSON file types are accepted') ;
    }

}else{

    $report[] = array("message"=>'ERROR: No source file was selected') ;
}

echo(json_encode($report));

$conn->close();

?>
