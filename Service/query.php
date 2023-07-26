<?php
require_once '../../Model/global.config.php';
require_once '../../Model/item.php';
require_once '../../Model/taxonomy.php';
require_once '../../Model/clase.php';

Class Query {
    public $metadaQuery;
    public $cataloguer;
    public $class;
    public $orderby;
    public $swallowID;
    public $conn;

    function __construct($in_metadataquery = '',$in_cataloguer = -1, $in_class = -1, $in_orderby = '',$swallowID='-1'){
        $this->metadataQuery = $in_metadataquery;
        $this->cataloguer = $in_cataloguer;
        $this->class = $in_class;
        $this->orderby = $in_orderby;
        $this->swallowID = $swallowID;
    }

    //write a function that check for sql injection on a string and return true if there is one
    function checkForSQLInjection($i_param){
        $i_param_initial = $i_param;
        $i_param = strtolower($i_param);
        $i_param = str_replace(' ','',$i_param);
        $i_param = str_replace('select','',$i_param);
        $i_param = str_replace('from','',$i_param);
        $i_param = str_replace('where','',$i_param);
        $i_param = str_replace('and','',$i_param);
        $i_param = str_replace('or','',$i_param);
        $i_param = str_replace('union','',$i_param);
        $i_param = str_replace('order','',$i_param);
        $i_param = str_replace('by','',$i_param);
        $i_param = str_replace('group','',$i_param);
        $i_param = str_replace('having','',$i_param);
        $i_param = str_replace('limit','',$i_param);
        $i_param = str_replace('into','',$i_param);
        $i_param = str_replace('insert','',$i_param);
        $i_param = str_replace('update','',$i_param);
        $i_param = str_replace('delete','',$i_param);
        $i_param = str_replace('drop','',$i_param);
        $i_param = str_replace('create','',$i_param);
        $i_param = str_replace('alter','',$i_param);
        $i_param = str_replace('table','',$i_param);
        $i_param = str_replace('database','',$i_param);
        $i_param = str_replace('index','',$i_param);
        $i_param = str_replace('view','',$i_param);
        $i_param = str_replace('procedure','',$i_param);
        $i_param = str_replace('function','',$i_param);
        $i_param = str_replace('trigger','',$i_param);
        $i_param = str_replace('grant','',$i_param);
        $i_param = str_replace('revoke','',$i_param);
        $i_param = str_replace('commit','',$i_param);
        $i_param = str_replace('rollback','',$i_param);
        $i_param = str_replace('savepoint','',$i_param);
        $i_param = str_replace('lock','',$i_param);
        $i_param = str_replace('unlock','',$i_param);
        $i_param = str_replace('start','',$i_param);
        $i_param = str_replace('transaction','',$i_param);
        if($i_param_initial == $i_param){
            return true;
        }else{  
            return false;
        }
    }

    function execute(){
        $_SESSION['swallow_uid'] = $GLOBALS['serviceUserID'];
        $controllerCall = $GLOBALS['baseURL'] . '/Controller/export.php?query='.base64_encode($this->metadataQuery)."&cataloguer=".$this->cataloguer."&class=".$this->class."&orderby=".$this->orderby."&swallowID=".$this->swallowID;
        $response = file_get_contents($controllerCall);
        return $response;
    }

    function getInstances($objTaxonomy, $parentid){
        $response = [];
        for($i = 0; $i < $objTaxonomy->siblingsCount; $i++){
            $objInstances = new Clase($this->conn); 
            $objInstances->selectByDefinition($objTaxonomy->current->definition,$parentid);
           
           for($j = 0; $j < $objInstances->total; $j++){               
                $objInstances->go($j);
                if($objTaxonomy->hasChildren){ // THIS IS UNTESTED - DON'T KNOW IF OT WORKS
                    $children = $objTaxonomy->getChildren();
                    $response[] = $this->getInstances($children,$objInstances->id);
                }else{
                    
                    $response[] = ["id"=>$objInstances->id, "label" => $objInstances->label ];
                }
               
            }

        return $response;
        }
    }

    function getClasses($schemaName){
        $objTaxonomy = new Taxonomy();
        $path = $GLOBALS['baseURL'] ."Definitions/".$schemaName."/Classification/taxonomy.json";
      
        $objTaxonomy->load($path);
        $response = $this->getInstances($objTaxonomy,0);
        return json_encode($response);
    }

}

?>
