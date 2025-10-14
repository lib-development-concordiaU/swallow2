<?php
include "../query.php";
include "RSS.php";

$metadataQuery = '';
$classid = -1;
$cataloguer = -1;

//function to check if all elementes of an array are present in another array
function array_contains($needles, $haystack){
    foreach($needles as $needle){
        if(!in_array($needle,$haystack)){
            return false;
        }
    }
    return true;
}

//get the categories and generate a query from it
if(isset($_GET['category'])){
    $metadataQuery = str_replace(" ","+AND+",$_GET['category']);
}

if(isset($_GET['subject'])){
    $classid = $_GET['subject'];
}

 
$objQuery = new Query($metadataQuery,$cataloguer,$classid);
$responseJSON = $objQuery->execute();
$responseArray = json_decode($responseJSON,TRUE);


if(!is_array($responseArray)){
    $responseArray = [];
}

//transform the json content into a compatible RSS

$objRSS = new RSS();

$serviceInformationSetted = false; 
foreach($responseArray as $item){
    if(!$serviceInformationSetted){
        $serviceInformationSetted = true;
        $objRSS->title = $item["classification"][0]['label'];
        $objRSS->description = $item["classification"][0]['description'];
        if($item["classification"][0]['URI'] != ""){
            $objRSS->link = $item["classification"][0]['URI'];
        }else{
            $objRSS->link = "https://library.concordia.ca";
        }
        $objRSS->language = "en-us";
        $objRSS->namespaces = array(
            ["name"=>"dc","uri"=>"http://purl.org/dc/elements/1.1/"],
            ["name"=>"atom","uri"=>"http://www.w3.org/2005/Atom"]
        );
    }

    $strcategory = '';
    
    foreach($item['Item_Description']['category'] as $cat){
            $strcategory .= $cat['value'].",";  
    }

    //check if the item is a false positive
    if(array_contains(explode(" AND ",$_GET['category']),explode(",",$strcategory)  )){
        $false_positive = false;
    }else{
        $false_positive = true;
    }

    $extention = [];
    $extention[] = array("name"=>"dc:creator","value"=>$item['cataloguer']['email']);

    $strcategory = substr($strcategory,0,-1);
    $media = [];
    if(isset($item['Item_Description']['image']) and $item['Item_Description']['image'] != ''){
        $media = array("url"=>'https://library.concordia.ca/apps/swallow2/'.$item['Item_Description']['image'],"type"=>"image/jpeg","medium"=>"image");
    }
    
    if(!$false_positive){
        $pubdate = new DateTime($item['last_modified']);
        $title = '';
        $description = '';
        $link = '';

        if(isset($item['Item_Description']['title']) and $item['Item_Description']['title'] != ''){
            $title = $item['Item_Description']['title'];
        }

        if(isset($item['Item_Description']['description']) and $item['Item_Description']['description'] != ''){
            $description = $item['Item_Description']['description'];
        }

        if(isset($item['Item_Description']['link']) and $item['Item_Description']['link'] != ''){
            $link = $item['Item_Description']['link'];
        }

        if($link !== ''){
            $objRSS->addItem($title,$description,$link,$pubdate->format(DateTime::RFC822),$media,'','',$strcategory,$extention);
        }
        
    }
    

} 


$objRSS->sortBy('title','string','asc');

//echo the response
$objRSS->echoRSS();

?>