<?php

if(isset($_GET['schema'])){
    session_start();
    $_SESSION['currentSchema'] = $_GET['schema'];

    $_SESSION['swallow_items_page'] = '1';
    $_SESSION['swallow_items_cataloguer'] = '-1';
    $_SESSION['swallow_items_orderby'] = '';
    $_SESSION['swallow_items_metadataquery'] = '';
    $_SESSION['swallow_selected_class'] = '-1';
}


?>