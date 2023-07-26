<?php
require_once "Components/component.php";

class Hidden extends Component{
    public $id;
    public $name;
    public $value;

    function __construct($id = null, $name='',$value = ''){
        $this->id = $id;
        $this->name = $name;
        $this->value = $value;
    }

    function render(){
        $html = file_get_contents('Components/Form/hidden/template-hidden.html');
        $html = str_replace('{id}',$this->id,$html);
        $html = str_replace('{name}',$this->name,$html);
        $html = str_replace('{value}',$this->value,$html);
        echo($html);
    }
    
}

?>