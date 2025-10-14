<?php
/*
$method = $_GET['method'];
$argument = $_GET['argument'];
*/
$_POST = json_decode(file_get_contents('php://input'), true);
$method = $_POST['method'];
$argument = $_POST['argument'];

$endpoint = 'wikdata-tagging-endpoint-URL';


$url = $endpoint.$method.'/'.urlencode($argument);
$response = file_get_contents($url);

//echo ($url);
header('Content-Type: application/json; charset=utf-8');
echo( $response );

?>