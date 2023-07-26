<?php
require_once "db.php";
require_once "log.php";
require_once "clase.php";

class Permission extends db{

    public $cataloguer_id;
    public $class_id;
    public $role;

    public function update(){
		if($this->total > 0){
			$this->cataloguer_id = $this->list[$this->index]["cataloguer_id"];
			$this->class_id = $this->list[$this->index]["class_id"];
			$this->role = $this->list[$this->index]["role"];
		}
	}

	// Not going to implement this functions
    public function select($id){}	
	public function selectAll(){}
	public function delete($id){}
	public function save(){}


	public function selectByCataloguer($in_cataloguer_id){
		$sql = "SELECT * FROM permission WHERE cataloguer_id = ".$in_cataloguer_id;

		$result =  $this->conn->query($sql);
		$this->list = [];
		while ($row= $result->fetch_assoc() ){
			$this->list[] = $row;
		}
		
		$this->index = 0;
		$this->total = $result->num_rows;
		$this->update();
	}

	public function add($in_cataloguer_id,$in_class_id,$in_role = 1){
		$sql = "INSERT INTO permission (cataloguer_id,class_id,role) values ($in_cataloguer_id,$in_class_id,$in_role)";
		$this->conn->query($sql);
		return $this->conn->insert_id ;
	}

	public function deleteCatClass($in_cataloguer_id,$in_class_id){
		$sql = "DELETE FROM permission WHERE cataloguer_id = $in_cataloguer_id and class_id = $in_class_id";
		$this->conn->query($sql);
	}

	public function deleteByClassID($in_class_id){
		$sql = 'DELETE FROM permission WHERE class_id = '.$in_class_id;
		$this->conn->query($sql);
	}

	public function deleteByCataloguer($in_cataloguer_id){
		$sql = 'DELETE FROM permission WHERE cataloguer_id = '.$in_cataloguer_id;
		$this->conn->query($sql);
	}

	public function hasAccess($in_cataloguer_id,$in_class_id){
		//check if the user is admin
		$sql = "SELECT * FROM cataloguer WHERE id =".$in_cataloguer_id;
		$result = $this->conn->query($sql);
		if($result !== false and $result->num_rows >= 1){
			$row = $result->fetch_assoc();
			if($row['role'] == 1 or $row['role'] == 3 ){ // user is admin or editor  -> return true
				return true;
			}else{
				//check if the user has explicit permission on the current class
				$sql = "SELECT * FROM permission WHERE cataloguer_id = $in_cataloguer_id and class_id = $in_class_id";
				$result =  $this->conn->query($sql);	
				if($result !== false and $result->num_rows >= 1){ //user has explicit permission for this class -> return true
					return true;
				}else{	//check if the user has permission on one of the parent classes
					$objClass = new Clase($this->conn);
					$objClass->select($in_class_id);
					$ancentry = $objClass->getAncentry();
					$hasAccess = false;	
					if(is_array($ancentry)){
						foreach($ancentry as $ancentor){
							$sql = "SELECT * FROM permission WHERE cataloguer_id = $in_cataloguer_id and class_id =". $ancentor['id'];
							
							$result =  $this->conn->query($sql);
							if($result !== false and $result->num_rows >= 1){
								$hasAccess =  true;
							}
						}
					}
					
					return $hasAccess;
				}
			}
		}else{ // user cannot be found -> return false
			return false;
		}
	}

}

?>
