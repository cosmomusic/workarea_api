<?php
$json = file_get_contents('php://input');

file_put_contents('order.txt', $json);

$ch = curl_init();
$url = "order_in.php";
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 

$result = curl_exec($ch);

//$answer = array("response"=>"Hi how are ya?");
//$response = json_encode($answer,1);

//print_r($response);
//print_r($json);
?>