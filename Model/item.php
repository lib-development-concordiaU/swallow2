<?php
require_once "db.php";
require_once "log.php";
require_once "clase.php";

class Item extends db{

    public $id;
    public $title;
    public $cataloguer_id;
    public $class_id;
    public $schema_definition;
    public $metadata = [];
    public $conn;
    public $query_total;
    public $locked;
    public $create_date;
    public $last_modified;
	
	public function __contructor($in_conn){
		$this->conn = $in_conn;
    }
    

    function updateValue($path,$key,$value){

        if($value != -1){
            $this->metadata[$path][$key] = $value; 
        }else{
            if(isset($this->metadata[$path]) and isset($this->metadata[$path][$key])){
               unset($this->metadata[$path][$key]);
            }
        }
    }

    function addElement($path,$keyValueArray){      
        $this->metadata[$path]=$keyValueArray;
    }    

    function addElementMultiple($path,$keyValueArray,$path2='',$parentid=''){
        
        if(!isset($keyValueArray['id'])){
            $keyValueArray['id']=uniqid('',TRUE);
        }

        if($path2 == ''){
            $this->metadata[$path][]=$keyValueArray;
        }else{
            if($parentid == 0){
                $this->metadata[$path][$path2][]=$keyValueArray;
            }else{
                //find the parent element
                $cont = 0;

                foreach($this->metadata[$path] as $element){
                    if($element['id'] == $parentid){
                        $element[$path2][]=$keyValueArray;
                        //make the element is properly formated to avoid json incompatibility issues (special charcarters)
                        $this->metadata[$path][$cont] = $element;
                    }
                    $cont++;
                }
            }
            
        }
        
    }


    function deleteElement($path,$id,$path2 = '',$parentid = 0){
        $newArray = [];
        $elementList = [];

        if($parentid == 0){ 
            if($path2 == ''){
                $elementList = $this->metadata[$path];
            }else{
                $elementList = $this->metadata[$path][$path2];
            } 

            foreach ($elementList  as $element){
                if($element['id'] != $id){
                    $newArray[] = $element;
                }
            }
    
            if($path2 == ''){
                $this->metadata[$path] = $newArray;
            }else{
                $this->metadata[$path][$path2] = $newArray;
            }

        }else{ // is multiple field in a multiple step
            $stepElements = $this->metadata[$path];
            foreach($stepElements as $stepElement){
                if($stepElement['id'] != $parentid){
                    $newArray[] = $stepElement;
                }else{
                    $fieldElements = $stepElement[$path2];
                    $newFieldElements = [];
                    foreach($fieldElements as $fieldElement){
                        if($fieldElement['id'] != $id){
                            $newFieldElements[] = $fieldElement;
                        }
                    }
                    $stepElement[$path2] = $newFieldElements;
                    $newArray[] = $stepElement;
                }
            }

            $this->metadata[$path] = $newArray;
 
        }
        
    }

    

    function getElement($path){
        if(is_array($this->metadata) and isset($this->metadata[$path])){
            return  $this->metadata[$path];
        }else{
            return  [];
        }
    }
    
    function getValue($path,$key){
        $encoded_key = str_replace(' ','_',$key);
       
        if(is_array($this->metadata) and isset($this->metadata[$path])){
            if(isset($this->metadata[$path][$encoded_key])){
                return $this->metadata[$path][$encoded_key];
            }else{
                return NULL;
            }
        }else{
            return NULL;
        }       
    }


    function select($id){
        $sql = "SELECT id,title,cataloguer_id,class_id,schema_definition, CAST(metadata as CHAR) as metadata, locked, create_date,last_modified  FROM item WHERE id = ".$id;
		
        $result =  $this->conn->query($sql);
        if($result != false){
            while ($row= $result->fetch_assoc() ){
                $this->list[] = $row;
            }
            $this->index = 0;
            $this->total = $result->num_rows;
            $this->update();

        }		
    } 


    function exists($in_title,$in_class_id){
        //escape the single quotes in the title
        $in_title = str_ireplace("'","\'",$in_title);
        $sql = "SELECT id FROM item WHERE title = '".trim($in_title)."' and class_id = ".$in_class_id;
        $result =  $this->conn->query($sql);
        if($result == false){
            return false;
        }else{
            if($result->num_rows > 0){
                $row= $result->fetch_assoc();
                return $row['id'];
            }else{
                return false;
            }
            
        }
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

    function save(){
        $date = new DateTime();

        $jsonStr = json_encode($this->metadata,JSON_UNESCAPED_UNICODE);

        $title = str_ireplace("\'", "'",trim($this->title)); 
        $title = str_ireplace("'", "\'",$title); 


        $sql = "UPDATE item  SET  title = '".$title."', cataloguer_id = ".$this->cataloguer_id.", class_id = ".$this->class_id.", schema_definition = '".$this->schema_definition."',  metadata = '".$this->prepareJson($jsonStr)."' , last_modified = '".$date->format('Y-m-d H:i:s')."', locked = ".$this->locked." WHERE id = ".$this->id;
    
        $result =  $this->conn->query($sql);

        return $result;

    }

    function update(){

        if($this->total > 0){
            $this->id = $this->list[$this->index]["id"];
            $this->title = $this->list[$this->index]["title"];
            $this->cataloguer_id = $this->list[$this->index]["cataloguer_id"];
            $this->class_id = $this->list[$this->index]["class_id"];
            $this->schema_definition = $this->list[$this->index]["schema_definition"];
            $this->locked = $this->list[$this->index]["locked"];
            $this->create_date = $this->list[$this->index]["create_date"];
            $this->last_modified = $this->list[$this->index]["last_modified"];
            $this->metadata = json_decode($this->list[$this->index]["metadata"] ?? '',true);
        }
    }

    function selectAll($sortby = "date",$limit = 100){

        $sortbystr = "";
        switch ($sortby){
            case "date":
                break;
            case "title":
                break;

        }

        $sql = "SELECT id,title,cataloguer_id,class_id,locked,schema_definition,create_date,last_modified, CAST(metadata as CHAR) as metadata  FROM item LIMIT $limit" ;
		
        $result =  $this->conn->query($sql);

        if($result != false)  {     
            while ($row= $result->fetch_assoc() ){
                $this->list[] = $row;
            }   
        }

		$this->total = $result->num_rows;
		$this->index = 0;
		
		$this->update();
    }

    function selectLatests($limit = 10){

        $sql = "SELECT id,title,cataloguer_id,class_id,locked,schema_definition,create_date,last_modified, CAST(metadata as CHAR) as metadata  FROM item ORDER BY create_date DESC LIMIT $limit " ;
		
        $result =  $this->conn->query($sql);

        if($result != false)  {     
            while ($row= $result->fetch_assoc() ){
                $this->list[] = $row;
            }   
        }

		$this->total = $result->num_rows;
		$this->index = 0;
		
		$this->update();
    }

    function classQuery($class){
        $conditions = "";
        $objClass = new Clase($this->conn);
        $objClass->selectChildren($class);
        for($i=0; $i < $objClass->total; $i++){
            $objClass->go($i);
            $conditions .= " OR item.class_id = ".$objClass->id;
            $conditions .= " ".$this->classQuery($objClass->id);
        }
         return $conditions;
    }

    function metadataQuery($in_query,$cataloguer = -1,$class = -1,$page=-1,$orderby='',$schema = -1,$page_size=10){
        //parse the input query string
        if($page != -1){
            $offset = ($page -1) * $page_size;
            $limit = " LIMIT $offset,$page_size";
        }else{
           $limit = " LIMIT 1000";
        }

        $conditions = '';
        $whereClause = "";

        if($in_query !== ''){
            $whereClause = "WHERE ";
            $tokens = explode('+',$in_query);
      
            $operators = array('AND','OR');
            foreach ($tokens as $token){
               // $whereClause .= "(";
                if($token != ''){
                    
                    if(in_array(trim($token),$operators) ){
                        $whereClause .= " ".$token." ";
                    } else{
                        $token = str_ireplace("'","\'",$token);
                        $whereClause .= "(JSON_SEARCH(item.metadata,'all','%".trim($token)."%') IS NOT NULL )";
                    }

                    
                }
              //  $whereClause .= ")";
                
            }
        }
        
        //Cataloguer is selected 
        if($cataloguer != -1){
            if(strpos($whereClause,"WHERE ") !== false or (strpos($conditions,"WHERE "))){
                $conditions .= " and item.cataloguer_id = $cataloguer";
            }else{
                $conditions .= " WHERE item.cataloguer_id = $cataloguer";
            }
        }

        //class is selected 
        if($class != -1){
            if(strpos($whereClause,"WHERE ") !== false or (strpos($conditions,"WHERE ")) ) {
                $conditions .= " and ( item.class_id = $class ".$this->classQuery($class).")";
            }else{
                $conditions .= " WHERE ( item.class_id = $class ".$this->classQuery($class).")";
            }
            
        }

         //Schema is selected 
         if($schema != -1){
            if(strpos($whereClause,"WHERE ") !== false or (strpos($conditions,"WHERE ")) ) {
                $conditions .= " and item.schema_definition = \"$schema\"";
            }else{
                $conditions .= " WHERE item.schema_definition = \"$schema\"";
            }
            
        }

        //orderby
        if($orderby != ''){
            if($orderby == 'create_date' or $orderby == 'last_modified'){
                $orderbystr = 'ORDER BY item.'.$orderby." DESC";
            }else{
                $orderbystr = 'ORDER BY item.'.$orderby;
            }
            
        }else{
            $orderbystr = '';
        }

        $sql = "SELECT DISTINCT item.id AS id,item.title AS title,item.cataloguer_id as cataloguer_id,item.class_id as class_id,item.schema_definition as schema_definition, CAST(item.metadata as CHAR) as metadata, item.locked as locked, item.create_date as create_date, item.last_modified as last_modified FROM item,class $whereClause $conditions  $orderbystr  $limit";

       //echo($sql);
        $result =  $this->conn->query($sql);
    

        if($result != false)  {     
            while ($row= $result->fetch_assoc() ){
                $this->list[] = $row;
            } 
            
            $this->total = $result->num_rows;    
        }else{
            $this->total = 0;
        }

        $this->index = 0;		
		$this->update();

        
        $sql = "SELECT COUNT(DISTINCT item.id) AS total FROM item,class $whereClause $conditions ORDER BY item.title ";
        $total = $this->conn->query($sql);
        if($total !== false){
            $totalAssoc = $total->fetch_assoc() ;
            $this->query_total = $totalAssoc['total'];
        }else{
            $this->query_total = 0;
        }
        
    }

	
	function delete($item_id = -1){
        if($item_id == -1){
            $item_id = $this->id;
        }
        $sql = "DELETE FROM item WHERE id=".$item_id;
        $result =  $this->conn->query($sql);
        return $result;
    }

    function deletelist(){
        for($i=0;$i < $this->total; $i++){
            $this->go($i);
            $sql = "DELETE FROM item WHERE id=".$this->id;
            $this->conn->query($sql);
        }
    }

    function deleteByClassID($class_id){
        $sql = 'DELETE FROM item WHERE class_id = '.$class_id;
        $this->conn->query($sql);
    }

    function deleteByCataloguerID($cataloguer_id){
        $sql = 'DELETE FROM item WHERE cataloguer_id = '.$cataloguer_id;
        $this->conn->query($sql);
    }

    function selectCataloguerID($cataloguer_id){
        $sql = "SELECT id,title,cataloguer_id,class_id,schema_definition,create_date,last_modified, CAST(metadata as CHAR) as metadata, locked  FROM item WHERE cataloguer_id = ".$cataloguer_id;
		
		$result =  $this->conn->query($sql);
		while ($row= $result->fetch_assoc() ){
			$this->list[] = $row;
		}
		
		$this->index = 0;
		$this->total = $result->num_rows;
		$this->update();
    }

    function selectClassID($class_id){
        $sql = "SELECT id,title,cataloguer_id,class_id,schema_definition,create_date,last_modified, CAST(metadata as CHAR) as metadata, locked  FROM item WHERE class_id = ".$class_id;
		
		$result =  $this->conn->query($sql);
		while ($row= $result->fetch_assoc() ){
			$this->list[] = $row;
		}
		
		$this->index = 0;
		$this->total = $result->num_rows;
		$this->update();
    }

    function duplicate($class_id=''){
        if($class_id == ''){
            $class_id = $this->class_id;
        }

        $jsonStr = json_encode($this->metadata,JSON_UNESCAPED_UNICODE);

        $title = str_ireplace("\'", "'",trim($this->title)); 
        $title = str_ireplace("'", "\'",$title); 

        $sql = "INSERT INTO item (title,cataloguer_id,class_id,schema_definition,metadata) VALUES ('".$this->title."',$this->cataloguer_id,$class_id,'".$this->schema_definition."','".$this->prepareJson($jsonStr)."')";
        $result =  $this->conn->query($sql);
		return $this->conn->insert_id;
    }

    
    function create($in_cataloguer_id,$in_schema_definition){
        $sql = "INSERT INTO item (cataloguer_id,schema_definition,title) VALUES (".$in_cataloguer_id.",'".$in_schema_definition."','new item')";
        $result =  $this->conn->query($sql);
		return $this->conn->insert_id;
    }

    public function getTotal(){
		$sql = "SELECT COUNT(id) as total FROM item";
		$result =  $this->conn->query($sql);
		$total = $result->fetch_assoc();
		return $total['total'];
	}

}


?>
