<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once "../Model/db.config.php";
require_once "../Model/item.php";
require_once "../Model/workflow.php";
require_once "../util.php";
require_once "../Model/session.php";
require_once "../Model/log.php";

isLogged($conn);


$stepname = $_POST['step'];
$itemid = $_POST['itemid'];

$objItem = new Item($conn);
$objItem->select($itemid);

$objWorkflow = new Workflow();
$objWorkflow->load('../Definitions/'.$objItem->schema_definition.'/workflow.json');
$fields = $objWorkflow->getFields($stepname);

$errorDetails = "";

function uploadFile($postArray,$fieldName,$itemid,$objItem,$stepname){
    //var_dump($postArray);

    $dirname = '../Uploads/'.$itemid.'/';
    if(file_exists($dirname) == false){
        mkdir($dirname);
    }
    
    if($postArray['name'] !=''){
        $filename = $postArray['name'];
        $target_file = $dirname .basename($filename);
        //should make some validation before uploading the file
        $allowed =  array('gif','png' ,'jpg','jpeg','pdf','docx','doc');
        
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(in_array($ext,$allowed) ) {

            if($postArray['size'] < 2000000) {
                move_uploaded_file($postArray["tmp_name"],$target_file);       
                $objItem->updateValue($stepname,$fieldName,substr($target_file,3));
            }else{
                $errorDetails = "File size is too big. Files must be less that 2Mb";
            }
            
        }else{
            $errorDetails = "Invalid filetype. The field was not updated";
        }
    
    }
}

$uploadFileKeys = ['image','text file','audio file','document','file'];

if($_POST['stepType'] == 'single'){

    foreach($uploadFileKeys as $key){
        if(isset($_FILES[$key])){
            uploadFile($_FILES[$key],$key,$itemid,$objItem,$stepname);
        }
    }

    foreach($_POST as $key => $value) {

        if($key != 'step' and $key != 'itemid' and $key != 'stepType' and strpos($key,'ignore') === false){
            if($key == 'collection_id'){
                $objItem->collection_id = $value;
            }else{
                if($key == 'title'){
                    $objItem->title = $value;
                }
               
                $objItem->updateValue($stepname,$key,$value);
            }
        }
    }

}else{ //Is a multiple step

    $element = [];

    foreach($uploadFileKeys as $key){
        if(isset($_FILES[$key])){
            $dirname = '../Uploads/'.$itemid.'/';
            if(file_exists($dirname) == false){
                mkdir($dirname);
            }
            
            if($_FILES[$key]['name'] !=''){
                $filename = $_FILES[$key]['name'];
                $target_file = $dirname .basename($filename);
                //should make some validation before uploading the file
                $allowed =  array('gif','png' ,'jpg','jpeg','pdf','docx','doc');
                
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if(in_array($ext,$allowed) ) {
                    if($_FILES[$key]['size'] < 2000000) {
                        move_uploaded_file($_FILES[$key]["tmp_name"],$target_file);
                        // remove the ../ from the targetfile 
                        $element[$key] = substr($target_file,3);
                    }else{
                        $errorDetails = "File size is too big. Files must be less that 2Mb";
                    }
                    
                }else{
                    $errorDetails = "Invalid filetype. The field was not updated";
                }
            }
        }
    }

    foreach($_POST as $key => $value) {
        if($key != 'step' and $key != 'itemid'  and $key != 'stepType' and strpos($key,'ignore') === false ){
            $element[$key] = $value;
        }
    }

    if(isset($element['id'])){ // is an update
        $originalElementList = $objItem->getElement($stepname);
        foreach($originalElementList as $originalElement){
            if($originalElement['id'] == $element['id']){
            //check if there's an image that's need to be preserved before deleting
                if(!isset($element['image'])){
                    if(isset($originalElement['image'])){
                        $element['image'] = $originalElement['image'];
                    }
                }

            // if there's a multiple fiels it must also be preserved
            foreach($originalElement as $key => $value){
                if( is_array($originalElement[$key]) ){
                //is a multiple field
                    $element[$key] = $value;
                }
            }
            
        }
    }
        $objItem->deleteElement($stepname,$element['id']);
    }
        $objItem->addElementMultiple($stepname,$element);
    
}


if($objItem->schema_definition == ''){
    $objItem->schema_definition = $objWorkflow->version;
}

$result = $objItem->save();

if($result == false){
    $errorMgs = "Something went wrong saving. ".$errorDetails;
}else{
    $errorMgs = "".$errorDetails;
}

echo(" {\"step\": \"".$stepname."\" , \"itemid\":\"".$itemid."\", \"stepType\":\"".$_POST['stepType']."\", \"errorMgs\": \"".$errorMgs."\" }");

$conn->close()
?>
