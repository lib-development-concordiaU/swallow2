<?php

$category = $_GET['category'];

$categoriesArray = json_decode(file_get_contents('vocabulary.json'));
if(!in_array($category,$categoriesArray)){
    $categoriesArray[] = $category;
}

file_put_contents('vocabulary.json',json_encode($categoriesArray));

?>