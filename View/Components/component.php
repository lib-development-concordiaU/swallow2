<?php

abstract class Component{
    public $styles = [];
 
    abstract public function render();

    public function addStyle($id,$styles){
        $this->styles[] = ["id" => $id, "css" => $styles];
    }
}


?>