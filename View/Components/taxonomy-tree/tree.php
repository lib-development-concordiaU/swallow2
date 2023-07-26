<?php
include "Components/component.php";
include_once "../Model/taxonomy.php";
include_once "../Model/clase.php";
include_once "../Model/permission.php";

class Tree extends Component {
    public $template_ul = '';
    public $template_li = '';
    public $template_li_instance = '';
    public $taxonomy;
    public $cataloguer_id;
    public $conn;


    function __construct($in_conn,$in_taxonomy,$in_cataloguer_id){
        $this->conn = $in_conn;
        $this->cataloguer_id = $in_cataloguer_id;

        $this->template_ul = file_get_contents('Components/taxonomy-tree/template-ul.html');
        $this->template_li = file_get_contents('Components/taxonomy-tree/template-li.html');
        $this->template_li_instance = file_get_contents('Components/taxonomy-tree/template-li-instance.html');
        $this->taxonomy = $in_taxonomy;
    }

    public function build($parentid,$hidden = false){
        $lis = "";
        $objPermission = new Permission($this->conn);
        for($i = 0; $i < $this->taxonomy->siblingsCount; $i++){
            
            $li = str_replace("{li-id}",$this->taxonomy->current->label,$this->template_li);
            $li = str_replace("{li-text}",$this->taxonomy->current->label,$li);
            $li = str_replace("{definition}",$this->taxonomy->current->definition,$li);
            $li = str_replace("{parentid}",$parentid,$li);

            $instances = "";
            $objInstances = new Clase($this->conn);
            $objInstances->selectByDefinition($this->taxonomy->current->definition,$parentid);

            for($j = 0; $j < $objInstances->total; $j++){
                $instance = '';
                $objInstances->go($j);
                $instance = str_replace("{li-id}",$objInstances->id,$this->template_li_instance);
                $instance = str_replace("{li-text}",$objInstances->label,$instance);
                $instance = str_replace("{instanceid}",$objInstances->id,$instance);
                if($objPermission->hasAccess($this->cataloguer_id,$objInstances->id)){
                    $instance = str_replace("{display}","",$instance);
                }else{
                    $instance = str_replace("{display}","display:none",$instance);
                }

                $childrenTree = '';
                
                if($this->taxonomy->hasChildren){
                    $instance = str_replace("{li-expand-icon}","glyphicon-expand",$instance);
                    $children = $this->taxonomy->getChildren();
                    $objChildTree = new Tree($this->conn,$children,$this->cataloguer_id);
                    $childrenTree .= $objChildTree->build($objInstances->id,true);
                }else{
                    $instance = str_replace("{li-expand-icon}","glyphicon-unchecked",$instance);
                }

                $instance = str_replace("{children}",$childrenTree,$instance);
                $instances .= $instance;
            }
            
            $li = str_replace("{instances}",$instances,$li);


            $lis .= $li;
            $this->taxonomy->nextSibling();
        }

        $root_ul = str_replace("{ul-id}","",$this->template_ul);
        $root_ul = str_replace("{lis}",$lis,$root_ul);
        if($hidden){
            $root_ul = str_replace("{ul-ccs}","class-tree-hidden",$root_ul);
        }else{
            $root_ul = str_replace("{ul-ccs}","",$root_ul);
        }
        

        return($root_ul);
    }

    public function render(){
        echo( $this->build(0) );
    }

}

?>