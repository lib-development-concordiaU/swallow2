<script>


    function setHeight(jq_in){
        jq_in.each(function(index, elem){
        // This line will work with pure Javascript (taken from NicB's answer):
        elem.style.height = elem.scrollHeight+'px'; 
     });
    }
    


</script>

<?php 
require_once "../Model/db.config.php";
require_once "../Model/Workflow.php";
require_once "../Model/session.php";
require_once "../Model/Cataloguer.php";
require_once "../Model/Item.php";
isLogged($conn);

if(isset($_GET['id'])){

    $objItem = new Item($conn);
    $objItem->select($_GET['id']);

    $objWorkflow = new Workflow();
    $objWorkflow->load('../Definitions/'.$objItem->schema_definition.'/workflow.json');

    $objCataloguer = new Cataloguer($conn);
    $objCataloguer->select($objItem->cataloguer_id);


// -------------------------------------------------------------------

    $objClass = new Clase($conn);
    $objClass->select($objItem->class_id);

    $objClass->getAncentry();
    $temp = new Clase($conn);

    $classes = [];
    $classes[] = $objClass->getMetadataArray();

    foreach($objClass->ancentry as $ancestor){
       $temp->select($ancestor["id"]);
       $classes[] = $temp->getMetadataArray();
    }
 

    
    #navigate the workflow and show the values when present in the record
    echo("
        <h1> $objItem->title </h1>
    ");

    echo("
                <h3> Cataloguer </h3>
                <p> <b>Name</b>: $objCataloguer->name $objCataloguer->lastname </p>
                <hr />
            ");
    
    echo("<h3> Class </h3>");
    foreach(array_reverse($classes) as $class){
        foreach(array_keys($class) as $key){
            echo("<p> <b>".$key."</b>: ".$class[$key]." </p>");
        }
        echo("<br />"); 
    }
    
    echo("<hr />");
            
    
            
    foreach ($objWorkflow->steps as $step){
       
        echo("<h3> $step->name </h3>");
        
        if($step->type == 'single'){
            $fields = $objWorkflow->getFields($step->name);
            foreach($fields as $field){
            
                $value = $objItem->getValue($step->name,$field->name);
                if($value != NULL){
                    //check if is multiple
                    if( isset($field->multiple) ){
                        // Deal with multiple fields here
                        echo("<p> <b>$field->name:</b>");
                        if(is_array($value)){
                            foreach($value as $elem){
                                echo($elem['value'].", ");
                            }
                        }
                        echo("</p>");
                    }else{
                        echo("<p> <b>$field->name:</b>");
                        //check if is an image
                        if(strpos($value,".jpg") != false or strpos($value,".png") != false){
                            echo("<img src='".$value."' width = 200px>");
                        }elseif(strlen($value) > 64){
                            echo("<p'>".$value."</p>");
                        }else{
                            echo($value);
                        }
                    }   
                }
            }
        }else{ // Multiple step
                $elements = $objItem->getElement($step->name);  
                
                foreach($elements as $element){
                    
                    $keys = array_keys($element);
                    $id = '';

                    foreach($keys as $key ){
                        if($key == 'id'){
                            $id = $element[$key];
                        }else{

                            echo("<p><b>".$key.":</b> ");

                            if(is_array($element[$key])){

                                foreach($element[$key] as $multielement){
                                    if(strpos($multielement["value"],".jpg") != false or strpos($multielement["value"],".png") != false){
                                        echo("<img src='".$multielement["value"]."' width = 200px>");
                                    }elseif(strlen($multielement["value"]) > 64){
                                        echo("<textarea class='longtext' >".$multielement["value"]."</textarea>");
                                    }else{
                                        echo("".$multielement["value"].". ");
                                    }
                                }
                            }else{

                                if(strpos($element[$key],".jpg") != false or strpos($element[$key],".png") != false){
                                    echo("<img src='".$element[$key]."' width = 200px>");
                                }elseif(strlen($element[$key]) > 64){
                                    echo("<textarea class='longtext' >".$element[$key]."</textarea>");
                                }else{
                                    echo("".$element[$key]."");
                                }
                            }
                            echo("</p>");
                        }
                    }
                    echo("<p> ------------------------  </p>");                  
                }
            // }
        }
    }
    echo("<hr />");
    
}

?>
