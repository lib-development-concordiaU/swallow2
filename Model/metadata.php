<?php

class Metadata {

    public $schemaArray = [];
    public $valuesArray = [];
    public $conn;
    
    function __construct($in_conn,$schemaPath = NULL, $values = NULL){
        $this->conn = $in_conn;

        if($schemaPath != NULL){
            $contents = file_get_contents($GLOBALS['baseURL'].'/Definitions/'.$schemaPath); 
            //change encoding of $contents to  utf-8
            $contents = mb_convert_encoding($contents, 'UTF-8', 'UTF-8');
            $this->schemaArray = json_decode($contents,true);
        }
        if($values != NULL){
            $this->valuesArray = json_decode($values,true);
        }
    }

    public function getFields(){
        return $this->schemaArray['fields'];
    }

    public function getValue($key){
        if(isset($this->valuesArray[$key])){
            return $this->valuesArray[$key];
        }else{
            return "";
        }
        
    }

    public function setValue($key,$value){
        $this->valuesArray[$key] = $value; 
    }

    public function getJson(){
        return json_encode($this->valuesArray);
    }



}

?>
