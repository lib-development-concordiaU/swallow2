<?php
require_once "../Model/db.config.php";
require_once "../Model/cataloguer.php";
require_once "../Model/item.php";
require_once "../Model/session.php";
isLogged($conn);

$objCataloguer = new Cataloguer($conn);
$objItem = new Item($conn);

?>


<!-- <h2>Dashboard</h2> <hr> -->

<div style="height:20px"></div>

    <div class="db-column">
        <div class="db-card db-cataloguers">
            <p class="db-text">Cataloguers Registered </p>
            <p class="db-number"><?php echo ($objCataloguer->getTotal()) ?></p>
        </div>

        <div class="db-row db-cataloguers-bg ">
        <h3>Top cataloguers</h3>    
            <table class="table">
                <?php
                $cataloguerTable = $objCataloguer->selectTop();
                foreach($cataloguerTable as $row){
                    echo("<tr><td>".$row['name']." ".$row['lastname']." </td><td> ".$row['Total']."</td></tr>");
                    }
                ?>
            </table>
        </div>
    </div>

    
    <div class="db-column">

        <div class="db-card db-items">
            <p class="db-text">Items Described</p>
            <p class="db-number"><?php echo ($objItem->getTotal()) ?></p>
        </div>

        <div class="db-row db-items-bg ">
            <h3>Latests Items Added</h3>    
            <table class="table">
                <?php
                $objItem->selectLatests();
                for($i  = 0; $i < $objItem->total; $i++){
                    $objItem->go($i);
                    echo("<tr><td>".$objItem->title."</td><tr>");
                }
                ?>
            </table>

        </div>

    </div>

    
    
    

    

    


<?php
$conn->close();
?>