<?php
require_once "../Model/db.config.php";
require_once "../Model/session.php";
isLogged($conn);
?>

<h2>Import</h2>


<div class="col-sm-6 items-facets" style="">
    <form id="applicationForm" method="post">
 
        <div class="form-group">
            <label for="fname" class="col-sm-3">Source File:</label>
            <input type="file" id="fname" name="fname">
        </div>

        <div class="form-group">
            <label for="map" class="col-sm-3">Mapping Function</label>
            <select name='map' class='form-control'>
                <option value=''>Select an option</option>
                <option value='../Maps/Import/swallow-json-v3.json'>Json from Swallow1</option>
                <option value='../Maps/Import/scuttlejson.json'>Json from scuttle</option>
            </select>
        </div>

        <div class="form-group">
            <label class="col-sm-3">Preview </label>    
            <input type="checkbox" class="form-check-input" id="is_preview" name="is_preview">
        </div>

        <div class="form-group">
            <span class="col-sm-3"></span>
                <button type="submit" class="btn btn-primary">Import</button>
                <div id="batchImportStatus"></div>
        </div>
    </form>
</div>

<div id='right-pane' class="col-sm-5" style="padding-top:20px">
    <div id="report">
    </div>
</div>

<script src='View/import.js'></script>

<?php
    $conn->close();
?>
