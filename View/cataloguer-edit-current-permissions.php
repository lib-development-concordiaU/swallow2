<?php
include_once "../Model/db.config.php";
include_once "../Model/permission.php";
include_once "../Model/clase.php";

$objPermission = new Permission($conn);
if(isset($_GET['cataloguerid'])){
    $objPermission->selectByCataloguer($_GET['cataloguerid']);
}
?>

<table class="table" style='width:80%'>
    <thead>
        <tr>
            <th>Current Access</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php
    $objClass = new Clase($conn);
    $objAncentor = new Clase($conn);
    for($i = 0; $i < $objPermission->total; $i++){
        $objPermission->go($i);
        $objClass->select($objPermission->class_id);
        $onclick = "deletePermission(".$objClass->id.",".$_GET['cataloguerid'].")";

        $ancentry = $objClass->getAncentry();
        $ancentry_label = "";


        if(is_array($ancentry)){
            foreach($ancentry as $ancentor){
                 $ancentry_label .= $ancentor['label'] . " > ";
            }
        }
        

        $schema_label = explode("/",$objClass->schemaDefinition);

    ?>
        <tr>
            <td><?php echo( $schema_label[0]." > " .$ancentry_label . $objClass->label)?></td>
            <td style="text-aling: left" class="link">
                <span class="glyphicon glyphicon-trash" aria-hidden="true" onclick="<?php echo($onclick) ?>"></span></td>
            </tr>
        </tr>
    <?php
    }
    ?>
    </tbody>
</table>