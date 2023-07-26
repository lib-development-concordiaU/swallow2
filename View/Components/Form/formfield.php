<?php
require_once "Components/component.php";
require_once "Components/Form/shorttext/shorttext.php";
require_once "Components/Form/longtext/longtext.php";
require_once "Components/Form/dropdown/dropdown.php";
require_once "Components/Form/hidden/hidden.php";

class Formfield extends Component{
    public $id;
    public $label;
    public $value;
    public $type;
    public $options;
    public $function;

    function __construct($type = '',$id = null, $label='',$value = '',$options = [],$function=''){
        $this->type = $type;
        $this->id = $id;
        $this->label = str_replace("_"," ",$label);
        $this->value = $value;
        $this->options = $options;
        $this->function = $function;
    }

    function render(){
        if($this->type == 'shorttext'){
            $objField = new Shorttext($this->id,$this->label,$this->value);
        }elseif($this->type == 'dropdown'){
            $objField = new Dropdown($this->id,$this->label,$this->value,$this->options,$this->function);
        }elseif($this->type == 'hidden'){
            $objField = new Hidden($this->id,$this->label,$this->value);
        }elseif($this->type == 'longtext'){
            $objField = new Longtext($this->id,$this->label,$this->value);
        }

        $objField->styles = $this->styles;
        $objField->render();
    }

}

?>