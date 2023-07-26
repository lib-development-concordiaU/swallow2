<?php 
include_once "workflow.php";

class Map{

    public $fieldmap = array();
    public $source_schema = "";
    public $target_schema = "";
    public $source_file_type = "";


    function load($path){
        $contents = file_get_contents($path); 
       //change encoding of $contents to  utf-8
        $contents = mb_convert_encoding($contents, 'UTF-8', 'UTF-8');
        $decoded = json_decode($contents,TRUE);

        $this->source_file_type = $decoded['source file type'];
        $this->source_schema = $decoded['source schema'];
        $this->target_schema = $decoded['target schema'];
        $this->fieldmap = $decoded['fieldmap'];
        
    }
      

    function validateControlledVocabulary($path,$value){
        $contents = file_get_contents($path); 
        //change encoding of $contents to  utf-8
        $contents = mb_convert_encoding($contents, 'UTF-8', 'UTF-8');
        $vocabulary = json_decode($contents,TRUE);

        $found = false;
        foreach($vocabulary["values"] as $term){
            if($term['label'] == $value){
                $found = true;
            }
        }
        return $found;
    }


    /* ------------------------------------------------------------------------------------------------------------------- 
    -------------------------------------------------   CSV IMPORT   -----------------------------------------------------
    The general idea is to create a full record based on the target schema and include values 
    when they exist. To do this the function will parse the target schema workflow to create each section and field values
    -------------------------------------------------------------------------------------------------------------------- */
    
    function getSourceValueCSV($target_schema_key,$source_record,$id = 0){
        $result = [];
        foreach($this->fieldmap as $pair ){       
            if(str_ireplace(' ','_',$pair['target_field']) == str_ireplace(' ','_',$target_schema_key)){
                $key= $pair['source_field'];
                if(isset($source_record[$key])){
                    if($id == 0){
                        $result[] = $source_record[$key];
                    }else{
                        if(isset($pair['id']) and $pair['id'] == $id){
                            $result[] = $source_record[$key];
                        }
                    }
                }
            }
        }
        return $result;
    }

    function getSourceValueJSON($target_schema_key,$source_record,$id = 0){
        $result = [];
        
        foreach($this->fieldmap as $pair ){ 
        
            if(str_ireplace(' ','_',$pair['target_field']) == str_ireplace(' ','_',$target_schema_key)){       
              
                $path= $pair['source_field'];
                $parts = explode('/',$path);
                $total = count($parts);

                if($total == 2){
            
                    if(isset($source_record[$parts[0]])){

                        $step = $source_record[$parts[0]];
                        if( isset($step[0]) ){ // multiple steps have a nested structure 
                            $step = $step[0];
                        }
                        
                        
                        if(is_array($step) and isset($step[$parts[1]])){
                            if(is_array($step[$parts[1]])){ // is a multiple field
                                $result=$step[$parts[1]];
                        
                            }else{
                                $result[0] = $step[$parts[1]];
                            }     
                        }else{
                          //  echo('undefined key: '.$parts.'\n');
                        }
                    } else{
                       // echo('undefined key: '.$parts.'\n');
                    }
                }else{
                    //source schema doesn't have steps
                    if(isset($source_record[$parts[0]])){
                        if(is_array($source_record[$parts[0]])){//has multiple values
                            //create the proper element
                            foreach($source_record[$parts[0]] as $elem ){
                                $value =  $elem;
                                $id = uniqid('',TRUE);
                                $result[] = ["id"=>$id,"value"=>$value] ;
                            }
                            
                        }else{
                            $result[0] = $source_record[$parts[0]];
                        }
                    }
                }
            }// if(str_ireplace(' ','_',$pair['target_field']) == str_ireplace(' ','_',$target_schema_key)){  
        }
        return $result;
    }

    function getSourceStepJSON($target_schema_stepname,$source_record){
        $result = [];
        foreach($this->fieldmap as $pair ){ 
            $map_target_stepname = explode('/',str_ireplace(' ','_',$pair['target_field']))[0];
            if($target_schema_stepname == str_ireplace(' ','/',$map_target_stepname) ){
                $source_stepname = explode('/', $pair['source_field']);
                if(isset($source_record[$source_stepname[0]] )){
                    $result[$source_stepname[0]] = $source_record[$source_stepname[0]];
                }
            }
        }
        return $result;
    }

    function getSourceStepCSV($target_schema_stepname,$source_record){
        // should return an array of all stepname columns if is a multiple step on the source file 
        $result = [];
        foreach($this->fieldmap as $pair ){ 
            $map_target_stepname = explode('/',str_ireplace(' ','_',$pair['target_field']))[0];
            if($target_schema_stepname == str_ireplace(' ','/',$map_target_stepname) ){ // found the correct target step
                $source_step_name_parts = explode('#',$pair['source_field']);
                //the multiple source fields follow the rule stepnane#id_fieldName. Normal field names wont have the #id 
                
                if(count($source_step_name_parts) > 1 ){ //is a multiple step with multiple instances
                    $id = explode('_',$source_step_name_parts[1])[0];
                    if(!in_array($source_step_name_parts[0].'#'.$id,$result)){
                        $result[] = $source_step_name_parts[0].'#'.$id;
                    }
                }else{ // is single step
                   // $result[] = $source_step_name_parts[0];
                }

            }
            
        }
        return $result;

    }


    function getSourceValue($target_schema_key,$source_record,$format,$id = 0){
        switch ($format) {
            case 'CSV':
                $result = $this->getSourceValueCSV($target_schema_key,$source_record,$id);
                
                if( count($result) == 0 ){
                    return '';
                }elseif(count($result) == 1 and (!is_array($result[0]))){
                    return ($result[0]);
                }else{
                    return ($result);
                }
                
                break;
            
            case 'JSON':
                $result = $this->getSourceValueJSON($target_schema_key,$source_record,$id);
                // -----------------------------------------------------------------------------------------------------------------------------------------
                // -----------------------------------------------------------------------------------------------------------------------------------------
                // -----------------------------------------------------------------------------------------------------------------------------------------
               // echo($target_schema_key."\n");
               // var_dump($source_record).
               // var_dump($result);

                // -----------------------------------------------------------------------------------------------------------------------------------------
                // -----------------------------------------------------------------------------------------------------------------------------------------
                // -----------------------------------------------------------------------------------------------------------------------------------------
                
                if( count($result) == 0 ){
                    return '';
                }elseif(count($result) == 1 and (!is_array($result[0]))){
                    return ($result[0]);
                }else{
                    return ($result);
                }
                
                //return ($result);
                break;
            
            default:
                return false;
                break;
        }
    }

    function maprecord($source_record,$format){
        $metadata = [];
        $cataloguer = [];
        $classID = 0;
        $objTargetSchema = new Workflow();
        $objTargetSchema->loadFromVersion($this->target_schema);

        $error = '';

        // GET the cataloger information
        $val = $this->getSourceValue('Cataloguer.email',$source_record,$format ); 
      
        if($val== ''){
            $error .= 'Cataloguer not defined. ';
        }else{
            $cataloguer = ["email"=>$val];
        }

        // get the class information
        $val = $this->getSourceValue('Class.id',$source_record,$format ); 
        if($val == ''){
            $error .= 'class not defined. ';
        }else{
            $classID = $val;
        }  
        
        // get the title (this is because the title is stored twice: on the tabular part of te record for fast access and on the metadata-json part al well)
        $title = $this->getSourceValue('Item_Description/title',$source_record,$format ); 
        
        if($title == ''){
            $error .= 'Item Contains no title. '; 
        }

        foreach($objTargetSchema->steps as $step){ //traverse the target schema and try to fill the schema with the available information
            
            $name = $step->name;
            $type = $step->type;
            $fields = $objTargetSchema->getFields($name);
            $metadata[$name] = [];
            if($type == 'single'){
                foreach($fields as $field){
                    //look if there's a value for the field on the source record
                    $value = $this->getSourceValue($name.'/'.$field->name,$source_record,$format );
                   // $value = $values[0];
                    
                   
                    if(!is_array($value)){
                         // check is the value is valid
                        $valid = true;
                        if(isset($field->source) and $value != "" and $field->type == "dropdown|vocabulary" and !$this->validateControlledVocabulary("../Definitions/".$this->target_schema."/".$field->source,$value)){
                            $valid = false;
                            $error .= " Field ".$field->name." has an invalid value ".$value;
                        }
                        if($value != '' and $valid){
                            if(isset($field->multiple) and $field->multiple == "yes"){
                                $id = uniqid('',TRUE);
                                $element = ["id"=>$id,"value"=>$value];
                                $metadata[$name][str_ireplace(" ","_",$field->name)][] = $element;
                            }else{
                                $metadata[$name][str_ireplace(" ","_",$field->name)] =  $value;
                            
                            }
                        }
                    }
                    else{
                        foreach($value as $val){
                            $valid = true;
                            if(isset($field->source) and $val != "" and $field->type == "dropdown|vocabulary" and !$this->validateControlledVocabulary("../Definitions/".$this->target_schema."/".$field->source,$val)){
                                $valid = false;
                                $error .= " Field ".$field->name." has an invalid value ".$val;
                            }

                            if($val != ''  and $valid){
                                if(isset($field->multiple) and $field->multiple == "yes"){
                                    $id = uniqid('',TRUE);
                                    $element = ["id"=>$id,"value"=>$val];
                                    $metadata[$name][str_ireplace(" ","_",$field->name)][] = $element;
                                }else{
                                    $metadata[$name][str_ireplace(" ","_",$field->name)] =  $val;
                                
                                }
                            }
                        
                        }

                    }
                }
            }else{ //is a multiple step
                
                $source_steps = $this->getSourceStepCSV($name,$source_record);
                $id = 1;
                $finished = false;
              //  if(count($source_steps) > 0){ // the source file has multiple values 
                for($i = 0; $i <= count($source_steps); $i++){   

                    while(!$finished){
                        $finished = true;
                        $element = [];
                        
                        foreach($fields as $field){
                            $element['id'] = $id;
                            $value = $this->getSourceValue($name.'/'.$field->name,$source_record,$format,$id);
                            //what happens if it has multiple values
                            
                            if(!is_array($value)){

                                $valid = true;
                                if(isset($field->source) and $value != "" and $field->type == "dropdown|vocabulary" and !$this->validateControlledVocabulary("../Definitions/".$this->target_schema."/".$field->source,$value)){
                                    $valid = false;
                                    $error .= " Field ".$field->name." has an invalid value ".$value;
                                }

                                if($value != ''  and $valid){
                                    if(isset($field->multiple) and $field->multiple == "yes"){
                                        $id2 = uniqid('',TRUE);
                                        $element[str_ireplace(" ","_",$field->name)][] = ["id"=>$id2,"value"=>$value];
                                    }else{
                                        $element[str_ireplace(" ","_",$field->name)] = $value;
                                    }
                                    $finished = false;
                                }
                            }else{
                                //the multiple fields has multoiple values
                                foreach($value as $val){
                                    $valid = true;
                                    if(isset($field->source) and $val != "" and $field->type == "dropdown|vocabulary" and !$this->validateControlledVocabulary("../Definitions/".$this->target_schema."/".$field->source,$val)){
                                        $valid = false;
                                        $error .= " Field ".$field->name." has an invalid value ".$val;
                                    }

                                    if($val != ''  and $valid){
                                        if(isset($field->multiple) and $field->multiple == "yes"){
                                            $id2 = uniqid('',TRUE);
                                            $element[str_ireplace(" ","_",$field->name)][] = ["id"=>$id2,"value"=>$val];
                                        }else{
                                            $element[str_ireplace(" ","_",$field->name)] = $val;
                                        }
                                        $finished = false;
                                    }
    
                                }
                            }     
                            
                        }
                        
                        if(!$finished){
                            $metadata[$name][]=$element;
                            $id++; 
                        }
                    }
                        
                }
            
            }
        }

        $result = array($cataloguer,$classID,$title,$metadata,$error);
        return $result;
    
    } //function maprecord($source_record){


    function maprecordJSON($source_record,$format){
        $metadata = [];
        $cataloguer = [];
        $classID = 0;
        $objTargetSchema = new Workflow();
        $objTargetSchema->loadFromVersion($this->target_schema);
    
        $error = '';
    
        // GET the cataloger information
        $val = $this->getSourceValue('Cataloguer.email',$source_record,$format ); 
        if($val== ''){
            $error .= 'Cataloguer not defined. ';
        }else{
            $cataloguer = ["email"=>$val];
        }
    
        // get the class information
        $val = $this->getSourceValue('Class.id',$source_record,$format ); 
        if($val == ''){
            $error .= 'class not defined. ';
        }else{
            $classID = $val;
        }  
            
        // get the title (this is because the title is stored twice: on the tabular part of te record for fast access and on the metadata-json part al well)
        $title = $this->getSourceValue('Item_Description/title',$source_record,$format ); 
        if($title == ''){
            $error .= 'Item Contains no title. '; 
        }
    
        foreach($objTargetSchema->steps as $step){ //traverse the target schema and try to fill the schema with the available information
                
            $name = $step->name;
            $type = $step->type;
            $fields = $objTargetSchema->getFields($name);
            $metadata[$name] = [];
            if($type == 'single'){
                foreach($fields as $field){
                    //look if there's a value for the field on the source record                   
                    $value = $this->getSourceValue($name.'/'.$field->name,$source_record,$format );    

                    if($value != ''){
                        if(!is_array($value)){
                            if(isset($field->multiple) and $field->multiple == "yes"){
                                $id = uniqid('',TRUE);
                                $element = ["id"=>$id,"value"=>$value];
                                $metadata[$name][str_ireplace(" ","_",$field->name)][] = $element;
                            }else{
                                $metadata[$name][str_ireplace(" ","_",$field->name)] =  $value;
                            }
                        }else{
                            $element2 = [];
                            foreach($value as  $val){
                                $element2[] = $val;
                            }
                            $metadata[$name][str_ireplace(" ","_",$field->name)] = $element2;

                        }   
                    }
                }
            }else{ //is a multiple step
                $id = 1; // by convention ids on multiple steps always start with 1
                //let's see if the step is also multiple on the source schema
                $step_elements = $this->getSourceStepJSON($name,$source_record,$format,$id);  
                $source_step_name = key($step_elements);
                if(count($step_elements) > 0 ){
                    //the source step is not multiple => make it multiple
                    $keys = array_keys($step_elements[$source_step_name]);
                    if((!isset($keys[0]))||(!is_array($step_elements[$source_step_name][$keys[0]]))){
                        $step_elements_normalized = [];
                        $step_elements_normalized[] = $step_elements[$source_step_name];
                    }else{
                        $step_elements_normalized = $step_elements[$source_step_name];
                    }

                    

                    foreach($step_elements_normalized as $step_element){
                        $element = [];
                        $fields = $objTargetSchema->getFields($name);
                        
                        foreach($fields as $field){
                            $element['id'] = $id;
                            
                            $subrecord = []; // we need to reintroduce step name because getSourceValue needs it
                            
                            $subrecord[$source_step_name] = $step_element;
                           
                            

                            $value = $this->getSourceValue($name.'/'.$field->name,$subrecord ,$format,$id);    

                            

                            
                            if(!is_array($value)){
                                if($value != ''){    

                                    if(isset($field->multiple) and $field->multiple == "yes"){
                                        $field_id = uniqid('',TRUE);
                                        $element[str_ireplace(" ","_",$field->name)][] = ["id"=>$field_id,"value"=>$value];
                                    }else{
                                        $element[str_ireplace(" ","_",$field->name)] = $value;
                                    }
                                    $finished = false;
                                }

                            }else{
                                foreach($value as  $val){
                                   $element[str_ireplace(" ","_",$field->name)][] = $val;
                                   $finished = false;
                                }

                            }

                        }                       
                        
                        $metadata[$name][]=$element;
                        $id++;   

                    }
                }
            }
        }
        
       
        $result = array($cataloguer,$classID,$title,$metadata,$error);
        return $result;
        
    } //function maprecord($source_record){


}

?>
