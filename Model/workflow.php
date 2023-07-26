<?php

class Workflow {

    public $version = '';
    public $steps = [];
    public $step = [];
    public $fields = [];
    public $vocabulary = [];
    

    function load($url){
        $contents = file_get_contents($url); 
        //change encoding of $contents to  utf-8
        $contents = mb_convert_encoding($contents, 'UTF-8', 'UTF-8');
        $decoded = json_decode($contents);
        
        if (json_last_error() === JSON_ERROR_NONE) { 
            $this->version = $decoded->schema_version;
            $this->steps = $decoded->steps;
            return true;

        } else { 
            return false;
        } 
    }

    function loadFromVersion($in_version){
        $url ='../Definitions/'.$in_version.'/workflow.json';
        return $this->load($url);
        
    }

    function getStep($key){
        $result = false;

        foreach($this->steps as $step){
            if($step->name == $key){
                $result = $step;
            }
        }
        $this->step = $result;
        return $result;
    }

    function getFields($in_step){
        $objstep = $this->getStep($in_step);
        $this->fields = $objstep->fields;
        return $objstep->fields;
    }

    /*
    DESCRIPTION: Retrieves the label value for the fields where the URI is stored 
    RETURN VALUES: Label String if succesful. False Otherwise.  
    */
    function getLabel($in_step_name,$in_field_name,$in_field_value){
        $label = false;

        if(strpos($in_field_value,'http') !== false){
            $this->getStep($in_step_name);
            $this->getVocabulary($in_field_name);
            
            foreach($this->vocabulary as $item){
                if(isset($item['uri']) and $item['uri']==$in_field_value){
                    $label = $item['label'];
                }    
            }

            return $label;

        }else{ // the value is already a label
            return false;
        }
    }

    /*
    DESCRIPTION: Retrieves the uri value for the given field and label
    RETURN VALUES: URI if succesful. False Otherwise.  
    */
    function getURI($in_step_name,$in_field_name,$in_field_value){
        $uri = false;

        $this->getStep($in_step_name);
        $this->getVocabulary($in_field_name);
        
        foreach($this->vocabulary as $item){
            //echo($item['label']."==".$in_field_value);
            if(isset($item['uri']) and ($item['label']==$in_field_value)){
                $uri = $item['uri'];
            }    
        }

        return $uri;        
    }


    /*
    DESCRIPTION: Given a controlled vocabulary field name populates the vocabulary property.
    RETURN VALUES: True if succesful. False Otherwise.  
    */
    function getVocabulary($in_field_name){
        foreach($this->fields as $field){
            if($field->name == $in_field_name){
                
                if(strpos($field->type,'vocabulary') > 0){
                    
                    $contents = file_get_contents( '../Definitions/'.$this->version.'/'.$field->source ); 
                    //change encoding of $contents to  utf-8
                    $contents = mb_convert_encoding($contents, 'UTF-8', 'UTF-8');

                    
                    $this->vocabulary = json_decode($contents,true)['values'];
                    return true;
                }else{
                    $this->vocabulary = [];
                    return false;
                }
            }
        }

    }

}


?>
