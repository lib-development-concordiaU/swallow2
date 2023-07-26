<?php
require_once "../Model/db.config.php";
require_once "../Model/item.php";
require_once "../Model/cataloguer.php";
require_once "../Model/session.php";
require_once "../Model/clase.php";
require_once "../Model/taxonomy.php";
require_once "../Model/permission.php";
require_once "./Components/Form/formfield.php";
require_once "./Components/Pagination/pagination.php";

require_once "./classification-filters.php";

isLogged($conn);

$page = 1;
$pagetotal = 20;
$metadataquery ='';
$objItem = new Item($conn);

$cataloguer = '-1';
$selectedClass = "-1";
$orderby = '';
$schema = '-1';

if(isset($_GET['page']) ){
  $page = $_GET['page'];
  $_SESSION['swallow_items_page'] = $_GET['page'];
}else{
  if(isset($_SESSION['swallow_items_page'])){
    $page = $_SESSION['swallow_items_page'];
  }
}

if(isset($_GET['cataloguer'])){
  $cataloguer = $_GET['cataloguer'];
  $_SESSION['swallow_items_cataloguer'] = $_GET['cataloguer'];
}else{
  if(isset($_SESSION['swallow_items_cataloguer'])){
    $cataloguer = $_SESSION['swallow_items_cataloguer'];
  }else{
    //show the logged in user items by default
    $cataloguer = $_SESSION['swallow_uid'];
  }
}

if(isset($_GET['orderby'])){
  $orderby = $_GET['orderby'];
  $_SESSION['swallow_items_orderby'] = $_GET['orderby'];
}else{
  if(isset($_SESSION['swallow_items_orderby'])){
    $orderby = $_SESSION['swallow_items_orderby'];
  }
}

if(isset($_GET['metadataquery'])){
  $metadataquery = base64_decode($_GET['metadataquery']);
  $_SESSION['swallow_items_metadataquery'] = base64_decode($_GET['metadataquery']);
}else{
  if(isset($_SESSION['swallow_items_metadataquery'])){
    $metadataquery = $_SESSION['swallow_items_metadataquery'];
  }
}

$schema = $_SESSION['currentSchema'];

if(isset($_GET['selectedClass'])){
  $selectedClass = $_GET['selectedClass'];
  $_SESSION['swallow_selected_class'] = $selectedClass;
}else{
  if(isset($_SESSION['swallow_selected_class'])){
    $selectedClass = $_SESSION['swallow_selected_class'];
  }
  
}

$objItem->metadataQuery($metadataquery,$cataloguer,$selectedClass,$page,$orderby,$schema,$pagetotal);

$objCataloguer = new Cataloguer($conn);
$objCataloguer->selectAll();

$objLoggedCataloguer = new Cataloguer($conn);
$objLoggedCataloguer->select($_SESSION['swallow_uid']);

$objPermission = new Permission($conn);


function fillCataloguer($objCataloguer,$cataloguer){

  for($i = 0; $i < $objCataloguer->total; $i++){
    
    $objCataloguer->go($i);
   // if($objCataloguer->institution == $institution){
        
      $selected = '';
      if($objCataloguer->id == $cataloguer){
         $selected = "selected";
      }
      
      echo("
         <option value='".$objCataloguer->id."' $selected>".substr($objCataloguer->name,0,1).". ".$objCataloguer->lastname."</option>
      ");
    }
  //}   
}

?>

<h2>Items </h2> 


<div class="row" id="deposit">
    <div class="col-sm-3 items-facets" >
      <p style="border-bottom: solid; margin-bottom: 20px;">
        <span class="glyphicon glyphicon-filter" style=" font-size: 25px; margin-top:0px"></span>
        <b>Filters</b>  
      </p>
      <p><b>Classification</b></p>
      <?php
        renderClassificationFilters($conn,$selectedClass,1);
      ?>
  <hr />
      <p><b>Cataloguer</b><p> 
      <select id="f_cataloguer" class="form-control" style="min-width:150px;" onchange="filter(1)">
        <option value='-1' >CATALOGUER</option> 
        <?php
          fillCataloguer($objCataloguer,$cataloguer);
        ?>
      </select>

      <p><b>Order By</b><p> 
      <select id="f_orderby" class="form-control sort-style" style="min-width:130px;" onchange="filter(1)">
      <?php
        $f_orderby_selected = array('','','','');
        if(isset($orderby)){
          if($orderby == '') {$f_orderby_selected[0] = 'selected';}
          if($orderby == 'last_modified') {$f_orderby_selected[1] = 'selected';}
          if($orderby == 'create_date') {$f_orderby_selected[2] = 'selected';}
          if($orderby == 'title') {$f_orderby_selected[3] = 'selected';}
        }else{
          $f_orderby_selected[0] = 'selected';
        }
      ?>
      <option value='' <?php echo($f_orderby_selected[0])?> >Default (Fast)</option>
      <option value='last_modified' <?php echo($f_orderby_selected[1])?> >Last Modified</option>
      <option value='create_date' <?php echo($f_orderby_selected[2])?>> Creation Date</option>
      <option value='title' <?php echo($f_orderby_selected[3])?> >Title</option>    
      </select>


      <input type='hidden' id="f_schema" class="form-control"  value='<?php echo($schema)?>'>
      <hr />
      <p><b>Metadata Query</b> <span class="glyphicon glyphicon-question-sign" style="cursor:pointer" data-toggle="modal" data-target="#helpModal" onclick="loadhelp('./View/help_search.php')"></span>
      <p>
      <div class="form-inline">
        <input type='text' class='form-control' id='metadaquery' style="width:80%"  name='metadaquery' value="<?php echo($metadataquery)?>">
        <button type='button' class='btn btn-primary' style="width:15%;padding-left:2px;padding-right:2px" onclick="query(1)">GO</button>
      </div>


      <?php
      if(isAdmin($conn)){
      ?>
        <hr/>
          
          <div class="border-box" style="text-align:left; padding:10px; background-color:#ccc">
              <h3>Batch Actions</h3>
              <hr>
              <b>Delete all these items  </b>
              <button type='button' class='btn btn-primary' onclick="deletedataset()" style="margin-left: 20px">Delete
              </button>
              <div id="batchDeleteStatus"></div>
              <hr>
              <p><b>Change Class</b></p>
              <input type='text' class='form-control'  style = "padding: 5px;width: 50%;" id='batch_class' name='batch_class'  maxlength ="3" placeholder="class ID">
              <button type='button' class='btn btn-primary' onclick="changeClass()" >Set Class</button>
              <div id="batchChangeStatus"></div>
             

          </div>

      <?php
        }
      ?>
    </div>

    <div class="col-sm-8" id="stepContainer" >
      <table class="table">
        <thead>
          <tr>
            <th scope="col" >ID</th>
            <th scope="col" >Class</th>
            <th scope="col" >Cataloguer</th>
            <th scope="col" >Version</th>
            <th scope="col" >Title</th>
            
            <th scope="col"colspan="4"> <button id="btn_create_cataloguer" class="btn btn-primary" style="width:100%" onclick="createItem()">New Item</button></th>

          </tr>
        </thead>
        <tbody>
          <?php
          $objClase = new Clase($conn);
          for($i = 0; ($i < $objItem->total && $i < $pagetotal) ; $i++){
            $objItem->go($i);
            $objClase->select($objItem->class_id);
            $objCataloguer->select($objItem->cataloguer_id);
          ?>
              <tr>
              <td><?php echo($objItem->id)?></td>
              <td><?php echo($objClase->label) ?></td>
              <td><?php echo( substr($objCataloguer->name,0,1) . ". " . $objCataloguer->lastname) ?></td>
              <td><?php echo($objItem->schema_definition)?></td>
              <td><?php echo($objItem->title)?></td>
              <td style="width: 30px; text-aling: left" class="link">
                  <span class="glyphicon glyphicon-search" aria-hidden="true" data-toggle="modal" data-target="#previewModal" onclick="preview('<?php echo($objItem->id)?>')"></span>
              </td>

              <?php

              $hasPermission = $objPermission->hasAccess($_SESSION['swallow_uid'],$objItem->class_id);

              $hasAccess = false;
              /* Conditions:
              1) Has Permission and the item is unlock
              2) Is an administrator
              3) is the owner
              */
              if( ($objItem->locked == 0  and $hasPermission)  or isAdmin($conn) or ($objItem->cataloguer_id == $_SESSION['swallow_uid'])  ){
                $hasAccess = true;
              }
              ?>

              <td style="width: 30px; text-aling: left" class="link">
                  <?php if($hasAccess) {?>
                    <span class="glyphicon glyphicon-pencil" aria-hidden="true" onclick="editItem('<?php echo($objItem->id)?>')"></span>
                  <?php } ?>
              </td>
              <td style="width: 30px; text-aling: left" class="link">
                <?php if($hasAccess) {?>
                  <span class="glyphicon glyphicon-duplicate" aria-hidden="true" onclick="duplicateItem('<?php echo($objItem->id)?>')"></span>
                <?php } ?>
              </td>
              
              <td style="text-aling: left" class="link">
                <?php if(isAdmin($conn) or ($objItem->cataloguer_id == $_SESSION['swallow_uid'])) {?>
                  <span class="glyphicon glyphicon-trash" aria-hidden="true" onclick="deleteItem('<?php echo($objItem->id)?>')"></span></td>
                  <?php } ?>
              </tr>
                

        <?php
           
        } //for($i = 0; ($i < $objItem->total && $i < $pagetotal) ; $i++){
        ?>
          
        <tbody>
      <table>

      <!-- --------------------------------------------  Pagination  ------------------------------------------------- -->
      <?php
        $objPagination = new Pagination($objItem->query_total,$page,$pagetotal);
        $objPagination->render();
      ?>

</div>

<!-- ---------------------------------------- PREVIEW MODAL WINDOW ----------------------------------------------- -->

<!-- Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="previewModalTitle"></h5>

        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>

      </div>

      <div id="modal-main" class="modal-body">
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>




</div>
<script src="View/items.js"></script>


<!-- Modal -->
<div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="helpModalTitle"></h5>

        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>

      </div>

      <div id="modal-main" class="modal-body">
      <iframe name="helpframe" width="100%" height="600" frameborder="0"></iframe>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
function loadhelp(in_url){
    document.getElementsByName('helpframe')[0].src = in_url;
}

</script>


<?php

$conn->close();

?>
