<?php
require_once "../Model/db.config.php";
require_once "../Model/session.php";
require_once "../Model/cataloguer.php";
require_once "../Model/permission.php";
require_once "./Components/Form/formfield.php";

isLogged($conn);

$objCataloguer = new Cataloguer($conn);
$objCataloguer->select( $_GET['cataloguerid']);
?>

<div>
    <form id="applicationForm">

        <input type="hidden" class="form-control"  id="id" name="id" value="<?php echo($objCataloguer->id)?>">

        <div class="form-group">
            <label for="fname" class="col-sm-2">Name</label>
            <input type="text" class="form-control" id="fname" name="fname" value="<?php echo($objCataloguer->name)?>">
        </div>

        <div class="form-group">
            <label for="lname"  class="col-sm-2">Last Name</label>
            <input type="text" class="form-control" id="lname" name="lname" value="<?php echo($objCataloguer->lastname)?>">
        </div>

        <div class="form-group">
            <label for="email" class="col-sm-2">Email</label>
            <input type="text" class="form-control" id="email" name="email" value="<?php echo($objCataloguer->email)?>">
        </div>

        <div class="form-group">
            <label for="pwd1" class="col-sm-2">Password</label>
            <input type="password" class="form-control" id="pwd1" name="pwd1" value="">
        </div>

         <div class="form-group">
            <label for="pwd2"  class="col-sm-2">Confirm Password</label>
            <input type="password" class="form-control" id="pwd2" name = "pwd2" value="">
        </div>

        <div class="form-group">
            <label for="role" class="col-sm-2">Role</label>
            <?php 
                $checked_cataloguer = "";
                $checked_admin = "";
                $checked_inactive = "";
               
                if($objCataloguer->role == 0){
                    $checked_cataloguer = "selected='selected'";
                }elseif($objCataloguer->role == 1){
                    $checked_admin = "selected='selected'";
                }elseif($objCataloguer->role == 3){
                    $checked_editor = "selected='selected'";
                }else{
                    $checked_inactive = "selected='selected'";  
                }
            ?>

            <select name='role' class='form-control'>
                <option value='0' <?php echo($checked_cataloguer); ?> >Cataloguer</option>
                <option value='1' <?php echo($checked_admin); ?> >Administrator</option>
                <option value='3' <?php echo($checked_editor); ?> >Editor</option>
                <option value='2' <?php echo($checked_inactive); ?> >Inactive</option>
            </select>
        </div>

        <hr>
            <h3>Manage Permissions</h3>
        

        <div id="permissionsSelector" class="form-group">
            <div>
                <?php
                    $options = [];
                    foreach($GLOBALS['availableSchemas'] as $schema){
                        $options[] = ['label'=> $schema, 'value'=> $schema];
                    }
                    $objSchemaSelector = new Formfield('dropdown',$id = 'permission_schema_selector', 'Schema',-1,$options,'changePermissionSchema('.$_GET['cataloguerid'].')');
                    $objSchemaSelector->styles[] = ["id"=>"{label-styles}","css"=>"width:16.66%"];
                    $objSchemaSelector->render();
                ?>
            </div>
            <hr>
            <div id='permissionClasses'>
                <?php // include ('cataloguer-edit-permissions.php');  ?>
            </div>
        </div>

        <div id="currentPermissions">
        <?php  include ('cataloguer-edit-current-permissions.php');  ?>
        </div>

        <hr>

        <div class="form-group">
            <span class="col-sm-2"></span>
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn" onclick="cancel()">Finish</button>
        </div>

    
    </form>
</div>

<script>
   // $('#permissionsSelector').load('View/cataloguer-edit-permissions.php');
   // $('#currentPermissions').load('./View/cataloguer-edit-current-permissions.php');
</script>
<script src="View/cataloguer-edit.js"></script>
<script src="View/breadcrumbs.js"></script>
