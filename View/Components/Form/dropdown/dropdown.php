<?php
require_once "Components/component.php";

class Dropdown extends Component{
    public $id;
    public $label;
    public $value;
    public $options;
    public $onchange;

    function __construct($id = null, $label='',$value = '',$options = [],$onchange=''){
        $this->id = $id;
        $this->label = $label;
        $this->value = $value;
        $this->options = $options;
        $this->onchange = $onchange;
    }

    public function render(){
        $select = file_get_contents('Components/Form/dropdown/template-select.html');
        $select = str_replace("{id}", $this->id,$select);
        $select = str_replace("{label}", $this->label,$select);
        $select = str_replace("{onchange}", $this->onchange,$select);

        $optionsHTML = "<option value='-1'>Select one</option>";
        $optionsTemplate = file_get_contents('Components/Form/dropdown/template-option.html');
        foreach($this->options as $option){
            $optionHTML = str_replace('{value}',$option['value'],$optionsTemplate);
            $optionHTML = str_replace('{label}',$option['label'],$optionHTML);
            
            if($option['value'] == $this->value){
                $optionHTML = str_replace('{selected}','selected',$optionHTML);
            }else{
                $optionHTML = str_replace('{selected}','',$optionHTML);
            }
            $optionsHTML .= $optionHTML;
        }
        $select = str_replace("{options}",$optionsHTML,$select);

        foreach($this->styles as $style){
            
            $select = str_replace($style['id'],$style['css'],$select);
        }
        echo($select);
    }

}
?>