<?php
require_once "db.php";
require_once "log.php";
require_once "metadata.php";

class Clase extends db{
		
	public $id;
	public $label;
	public $uri;
	public $schemaDefinition;
	public $objMetadata = NULL;
	public $parentID;
	public $export;
	public $ancentry = [];	

    public function create($definition = '',$parent_id = 0){
        $sql = "INSERT INTO class (label,schema_definition,parent_id) VALUES ('new','".$definition."',".$parent_id.")";
        $result = $this->conn->query($sql);
        return $this->conn->insert_id;
    }

    public function update(){
        if($this->total > 0){
			$this->id = $this->list[$this->index]["id"];
			$this->label = $this->list[$this->index]["label"];
			$this->uri = $this->list[$this->index]["URI"];
			$this->schemaDefinition = $this->list[$this->index]["schema_definition"];
			$this->export = $this->list[$this->index]["export"];
			$this->parentID = $this->list[$this->index]["parent_id"];

			if( $this->list[$this->index]["metadata"] == NULL ){
				$this->objMetadata = new Metadata($this->conn, $this->schemaDefinition,"{}");
			}else{
				$this->objMetadata = new Metadata($this->conn, $this->schemaDefinition,$this->list[$this->index]["metadata"]);
			}
		}else{
			$this->id = -1;
			$this->label = '';
			$this->uri = '';
			$this->schemaDefinition = '';
			$this->export = 0;
			$this->parentID = 0;	
		}
    }
   
	public function select($in_id){
        $sql = "SELECT * FROM class WHERE id=".$in_id;
		
		$result =  $this->conn->query($sql);
		$this->list = [];
		$this->index = 0;
		$this->total = 0;

		if($result !== false){
			$this->list[] = $result->fetch_assoc();
			$this->index = 0;
			$this->total = $result->num_rows;
			$this->update();
		}
		
    }
    
    public function selectChildren($parentID=0){
        $sql = "SELECT * FROM class WHERE parent_id=".$parentID;
		
		$result =  $this->conn->query($sql);
		$this->list = [];
		$this->index = 0;
		$this->total = 0;

		if($result !== false){
			while ($row= $result->fetch_assoc() ){
				$this->list[] = $row;
			}
			
			$this->total = $result->num_rows;
			$this->update();
		}
		
    }

	public function selectByDefinition($schemaDefinition,$parentID){
		$sql = "SELECT * FROM class WHERE schema_definition LIKE '%/".$schemaDefinition."' and parent_id=".$parentID;	
		$result =  $this->conn->query($sql);
		$this->list = [];
		while ($row= $result->fetch_assoc() ){
			$this->list[] = $row;
        }
		$this->index = 0;
		$this->total = $result->num_rows;
		$this->update();
	}


	public function selectAll(){

    }

	function prepareJson($json){

        $cleaned = str_ireplace( "\\r", "", $json  ); // TN
        $cleaned = str_ireplace( "\\n", "\\\\n", $cleaned ); // TN
        $cleaned = str_ireplace( "\\t", "\\\\t", $cleaned ); // TN
   
        //remove single quotes
        $cleaned = str_ireplace("\'", "'",$cleaned );
        $cleaned = str_ireplace("'", "\\'",$cleaned );

        //make sure the backslashes are properly escapeds        
        $cleaned = str_ireplace('\\\"','\"',$cleaned);
        $cleaned = str_ireplace('\"','\\\"',$cleaned);

        return $cleaned;
    }
	
	public function save(){

		
		
        $sql = "UPDATE class SET label ='".$this->label."', URI='".$this->uri."', schema_definition ='".$this->schemaDefinition."', metadata = '".$this->prepareJson($this->objMetadata->getJson())."', parent_id = ".$this->parentID.", export = ".$this->export." where id = ".$this->id;

		$result =  $this->conn->query($sql);
        return $result;
    }
	
	public function delete($class_id = -1){
		if($class_id == -1){
			$class_id = $this->id;
		}
    
        $sql = "DELETE FROM class where id = ".$class_id;
        $result = $this->conn->query($sql);
		return $result;
            
    }

	/*
	public function getAncentry($parentID = '',$step = 0){
		if($parentID == ''){
			$parentID = $this->parentID;
		}
		$ancentry = [];
		if($parentID > 0){
			$sql = "SELECT id,label,parent_id FROM class WHERE id = ".$parentID;
			$result = $this->conn->query($sql);
			$parent = $result->fetch_assoc();
			
			if($step == 0){
				$ancentry[] = $parent;
			}else{
				$ancentry = $parent;
			}
			
			
			if($parent['parent_id'] != 0){
				$step++;
				$ancentry[] =  $this->getAncentry($parent['parent_id'],$step);
			}
		}

		$this->ancentry = $ancentry;
		return $ancentry;
	}
	*/

	public function getAncentry($parentID = '',$step = 0){
		if($parentID == ''){
			$parentID = $this->parentID;
		}
		$ancentry = [];
		if($parentID > 0){
			$sql = "SELECT id,label,parent_id FROM class WHERE id = ".$parentID;
			$result = $this->conn->query($sql);
			$parent = $result->fetch_assoc();
			$this->ancentry[$step] = $parent;

			if($parent['parent_id'] != 0){
				$step++;
				$this->getAncentry($parent['parent_id'],$step);
			}
		}
		
		return true;
	}

	function isAncentor( $classid){
		$result = false;
		foreach($this->ancentry as $ancestor){
			if($ancestor['id'] == $classid){
				$result = true;
			}
		}
		return $result;
	}

	function getMetadataArray(){
		$resultArray = [];
		$resultArray["label"] = $this->label;
		$resultArray["URI"] = $this->uri;
		$resultArray["id"] = $this->id;
		$resultArray["parent_id"] = $this->parentID;

		if(!is_null($this->objMetadata)){
			foreach($this->objMetadata->getFields() as $field){
				$resultArray[$field['name']] = $this->objMetadata->getValue($field['name']);
			}
		}
		
		return $resultArray;
	}


}

?>
