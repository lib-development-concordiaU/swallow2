<?php
require_once "../Model/db.config.php";
require_once "../Model/cataloguer.php";
require_once "../Model/session.php";
isLogged($conn);

$objCataloguer = new Cataloguer($conn);
$objCataloguer->selectAll();

?>

<h2>Cataloguers</h2> 

<div class="col-sm-6 items-facets" style="">

  <table class="table">
    <thead>
      <tr>
        <th scope="col" >Last Name</th>
        <th scope="col" >First Name</th>
        <th scope="col" >email</th>
        <th scope="col"colspan="3"> <button id="btn_create_cataloguer" class="btn btn-primary" onclick="createCataloguer()">Create cataloguer</button></th>
      </tr>
    </thead>
    <tbody>
      <?php
      for($i = 0; $i < $objCataloguer->total; $i++){
          $objCataloguer->go($i);
      ?>
          <tr>
            <th scope="row"><?php echo($objCataloguer->lastname)?></th>
            <td><?php echo($objCataloguer->name)?></td>
            <td><?php echo($objCataloguer->email)?></td>

            <td style="width: 30px; text-aling: left" class="link">
                <span class="glyphicon glyphicon-pencil" aria-hidden="true" onclick="editCataloguer('<?php echo($objCataloguer->id)?>')"></span>
            </td>
            <td style="width: 30px;text-aling: left" class="link">
                <span class="glyphicon glyphicon-trash" aria-hidden="true" onclick="deleteCataloguer('<?php echo($objCataloguer->id)?>')"></span>
            </td>
            <td style="text-aling: left" class="link">
                <span class="glyphicon glyphicon-share" aria-hidden="true" onclick="mailCataloguer('<?php echo($objCataloguer->id)?>')"></span>
            </td>  
          </tr>
      <?php
          }
      ?>

      <tbody>
        </table>
</div>

<div id='right-pane' class="col-sm-5" style="padding-top:20px">

</div>



<script src="View/cataloguers.js"></script>