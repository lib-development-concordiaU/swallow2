<?php
require_once "../Model/db.config.php";
require_once "../Model/session.php";
require_once "../Model/clase.php";
require_once "./Components/Form/formfield.php";

isLogged($conn);

$classid = $_GET['classid'];
$objClass = new Clase($conn);
$objClass->select($classid);

//generate the breadcrums
$ancentry = $objClass->getAncentry();
$breadcrumbs = "";
if(is_array($ancentry)){
    foreach(array_reverse($ancentry) as $ancestor){
        $breadcrumbs .= $ancestor['label']." > ";
    }
}

$breadcrumbs .= $objClass->label;
?>

<h3><?php echo($breadcrumbs)?></h3>
<hr/>
<div>
    
    <form id="applicationForm">
        <input type="hidden" class="form-control"  id="id" name="classid" value="<?php echo($classid)?>">
        <?php 

            $objFormfield = new Formfield('shorttext',"disabled","Swallow Class ID",$classid);
            $objFormfield->render();

            $objFormfield = new Formfield('shorttext',"label","label",$objClass->label);
            $objFormfield->render();

            $objFormfield = new Formfield('shorttext',"uri","URI",$objClass->uri);
            $objFormfield->render();


            foreach($objClass->objMetadata->getFields() as $field){
                $objFormfield = new Formfield($field['type'],str_replace(" ","_",$field['name']),$field['name'],$objClass->objMetadata->getValue($field['name']));
                $objFormfield->render();
            }

            $objFormfield = new Formfield('dropdown',"export","Export",$objClass->export,
            [['value' => '1','label' => 'Yes'],['value' => '0','label' => 'No']]);
            $objFormfield->render();

        ?>

        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>


<script src="View/class-edit.js"></script>
<?php 
$conn->close();
?>
