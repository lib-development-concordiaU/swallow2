<?php
require_once "Components/component.php";

class Shorttext extends Component{
    public $id;
    public $label;
    public $value;

    function __construct($id = null, $label='',$value = ''){
        $this->id = $id;
        $this->label = $label;
        $this->value = $value;
    }

    function render(){
        $html = file_get_contents('Components/Form/shorttext/template-shorttext.html');
        
        if(!is_null($this->id) ){
            $html = str_replace('{id}',$this->id,$html);    
        }
        if(!is_null($this->label) ){
            $html = str_replace('{label}',$this->label,$html);    
        }else{
            $html = str_replace('{label}','',$html);    
        }
        if(!is_null($this->value) ){
            $html = str_replace('{value}',$this->value,$html);
        }else{
            $html = str_replace('{value}','',$html);
        }
        
        echo($html);
    }
    
}

?>
