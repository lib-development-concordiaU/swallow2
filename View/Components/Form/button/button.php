<?php
require_once "Components/component.php";

class Button extends Component{
    public $id;
    public $label;
    public $type;
    public $onClick;
    public $primary;

    function __construct($id = '',$primary=false, $label='',$type = '',$onClick=''){
        $this->id = $id;
        $this->label = $label;
        $this->type = $type;
        $this->onClick = $onClick;
        $this->primary = $primary;
    }

    function render(){
        $html = file_get_contents('Components/Form/button/template-button.html');
        $html = str_replace('{id}',$this->id,$html);
        $html = str_replace('{label}',$this->label,$html);
        $html = str_replace('{type}',$this->type,$html);
        $html = str_replace('{onClick}',$this->onClick,$html);
        if($this->primary){
            $class = "btn btn-primary";
        }else{
            $class = "btn";
        }
        $html = str_replace('{class}',$class,$html);
        echo($html);
    }
    
}

?>