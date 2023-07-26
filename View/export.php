<?php
require_once "../Model/db.config.php";
require_once "../Model/item.php";
require_once "../Model/clase.php";
require_once "../Model/cataloguer.php";
require_once "../Model/session.php";

require_once "../Model/taxonomy.php";
require_once "./Components/Form/formfield.php";
require_once "./classification-filters.php";

isLogged($conn);

$page = 1;
$pagetotal = 15;
$metadataquery ='';
$objItem = new Item($conn);

$institution = '-1';
$cataloguer = '-1';
$selectedClass = '-1';
$schema = '-1';
$orderby = '';

if(isset($_GET['page'])){
  $page = $_GET['page'];
}

if(isset($_GET['cataloguer'])){
  $cataloguer = $_GET['cataloguer'];
}

if(isset($_GET['selectedClass'])){
  $selectedClass = $_GET['selectedClass'];
}

$schema = $_SESSION['currentSchema'];

if(isset($_GET['metadataquery'])){
  $metadataquery = base64_decode($_GET['metadataquery']);  
}

$objItem->metadataQuery($metadataquery,$cataloguer,$selectedClass,$page,$orderby,$schema);

$objCataloguer = new Cataloguer($conn);

function fillCataloguer($objCataloguer,$cataloguer){
  $objCataloguer->selectAll();
  for($i = 0; $i < $objCataloguer->total; $i++){
    
    $objCataloguer->go($i);
//    if($objCataloguer->institution == $institution){
        
      $selected = '';
      if($objCataloguer->id == $cataloguer){
         $selected = "selected";
      }
      
      echo("
         <option value='".$objCataloguer->id."' $selected>".substr($objCataloguer->name,0,1).". ".$objCataloguer->lastname."</option>
      ");
    }
//  }   
}

/*
function fillSchema($schema){
  // this should be put in config file
  $schema_list = array("2","3");
  
  foreach($schema_list as $item){
    $selected = '';
    if($item == $schema){
      $selected = 'selected = selected';
    }
    echo("<option value=\"$item\" $selected>V$item</option>");
  }
}
*/
?>

<h2>Export</h2> 


<div class="col-sm-3 items-facets" >
  <p style="border-bottom: solid; margin-bottom: 20px;">
    <span class="glyphicon glyphicon-filter" style=" font-size: 25px; margin-top:0px"></span>
    <b>Filters</b>  
  </p>
  <p><b>Classification</b></p>
  <?php
    renderClassificationFilters($conn,$selectedClass,$page);
  ?>

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

  <input type="hidden" id="f_schema"" name="f_schema"" value="<?php echo($schema)?>">

  <hr />
  <p><b>Metadata Query</b> <span class="glyphicon glyphicon-question-sign" style="cursor:pointer" data-toggle="modal" data-target="#helpModal" onclick="loadhelp('/View/help_search.php')"></span>
  <p>
  <div class="form-inline">
    <input type='text' class='form-control' id='metadaquery' style="width:80%"  name='metadaquery' value="<?php echo($metadataquery)?>">
    <button type='button' class='btn btn-primary' style="width:15%;padding-left:2px;padding-right:2px" onclick="query(1)">GO</button>
  </div>

</div>


    

<div class="col-sm-8" id="stepContainer" >
  <div class="border-box" style="text-align:center; padding:10px; background-color:#ccc">

  <b>Export current dataset as: </b>
  <select id="export_format" name="export_format" class="form-control" style="width:250px;display: inline-block;margin-left:30px:margin-right:30px" >
      <option value='-1' >Select the format</option>
      <option value='1'>Swallow Json</option>
      <option value='2'>Swallow triplets</option>
  </select>
  <button type='button' class='btn btn-primary' onclick="exportdataset()">Export</button>
  </div>
  
  <br /> <br />

  <table class="table">
    <thead>
      <tr>
        <th scope="col" >ID</th>
        <th scope="col" >Cataloguer</th>
        <th scope="col" >Schema</th>
        <th scope="col" >Title</th>
        
        <th scope="col"></th>

      </tr>
    </thead>
    <tbody>
      <?php

      for($i = 0; ($i < $objItem->total && $i < $pagetotal) ; $i++){
        $objItem->go($i);

        $objCataloguer->gotoID($objItem->cataloguer_id);        
      ?>
          <tr>
          <td><?php echo($objItem->id)?></td>
          <td><?php echo( substr($objCataloguer->name,0,1) . ". " . $objCataloguer->lastname) ?></td>
          <td><?php echo($objItem->schema_definition)?></td>
          <td><?php echo($objItem->title)?></td>
          <td style="width: 30px; text-aling: left" class="link">
              <span class="glyphicon glyphicon-search" aria-hidden="true" data-toggle="modal" data-target="#previewModal" onclick="preview('<?php echo($objItem->id)?>')"></span>
          </td>

          </tr>
      <?php
          }
      ?>

      <tbody>
  <table>

  <!-- --------------------------------------------  Pagination  ------------------------------------------------- -->
  <div class="pagination-box">
      <?php 
        $total_pages = ceil($objItem->query_total / 15);
        for($i = 1; $i < $total_pages + 1; $i++){
            if($i == $page){
              $active = 'pagination-active';
            }else{
              $active = '';
            }
            echo("
              <a class='pagination-link ".$active."' onclick='filter(".$i.")'> $i</a>
            ");
        }
      ?>
  </div>
 

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
<script src="View/export.js"></script>


<?php
$conn->close();
?>
