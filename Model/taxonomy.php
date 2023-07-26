<?php

class Taxonomy{
    public $version = '';
    public $hierarchy = [];
    public $current = [];
    public $siblingsCount = 0;
    public $currentSibling = 0;
    public $hasChildren = false;


    public function load($url){
        $contents = file_get_contents($url); 
         //change encoding of $contents to  utf-8
        $contents = mb_convert_encoding($contents, 'UTF-8', 'UTF-8');
        $decoded = json_decode($contents);
        
        if (json_last_error() === JSON_ERROR_NONE) { 
            $this->version = $decoded->version;
            $this->hierarchy = $decoded->hierarchy;
            $this->current = $this->hierarchy[0];
            $this->siblingsCount = count($this->hierarchy);
            if(count($this->current->sub_classes) > 0 ){
                $this->hasChildren = true;
            }else{
                $this->hasChildren = false;
            }
            return true;

        } else { 
            return false;
        } 
    }

    public function getChildren(){
        $children = new Taxonomy();

        if($this->hasChildren){    
            $children->version = $this->version;
            $children->hierarchy = $this->current->sub_classes;

            $children->current = $children->hierarchy[0];
            
            $children->siblingsCount = count($children->hierarchy);
            
            if(count($children->current->sub_classes) > 0){
                $children->hasChildren = true;
            }else{
                $children->hasChildren = false;
            }
            $children->currentSibling = 0;
            
        }

        return $children;
        
    }

    public function nextSibling(){
        if($this->currentSibling + 1 < $this->siblingsCount){
            $this->currentSibling += 1;
            $this->current = $this->hierarchy[$this->currentSibling];
            if(count($this->current->sub_classes) > 0 ){
                $this->hasChildren = true;
            }else{
                $this->hasChildren = false;
            }
        }
    }
 
     
}

?>