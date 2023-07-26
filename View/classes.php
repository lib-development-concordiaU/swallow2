<?php
require_once "../Model/db.config.php";
require_once "../Model/taxonomy.php";
require_once "../Model/session.php";
require_once "./Components/taxonomy-tree/tree.php";

isLogged($conn);

$objTaxonomy = new Taxonomy();
$objTaxonomy->load("../Definitions/".$_SESSION['currentSchema']."/Classification/taxonomy.json");

$objTree = new Tree($conn,$objTaxonomy,$_SESSION['swallow_uid']);


?>

<h2>Classes Management</h2> 


<div class="col-sm-5 items-facets" style="">

<?php  
  $objTree->render();
?>

</div>

<div id='right-pane' class="col-sm-6" style="padding-top:20px">

</div>

<script src="View/classes.js"></script>
<script>
  init();
</script>

<?php
if(isset($_GET['classid'])){
  echo("<script>$('#right-pane').load('View/class-edit.php?classid=".$_GET['classid']."')</script>");
}
$conn->close();
?>