<?php
include $_SERVER['DOCUMENT_ROOT'] . "/_common/mysql-helper.php";
ini_set('max_execution_time', '0'); // for infinite time of execution 

$orderid = "";
if (isset($_GET['orderid'])) {
    $orderid = $_GET['orderid'];
} else {
    echo "please provide orderid";
    exit;
}
$json = getOrderJson("https://cosmomusic.ca/api/admin/orders/" . $orderid, true);
writeFile($json, $orderid);
callWebhookAndArchiveFile($json);

function getOrderJson($url, $returnFullJson = false)
{
    $result = makeGETRequest($url);
    if ($returnFullJson) {
        return $result["content"];
    } else {
        return json_decode($result["content"]);
    }
}

function writeFile($json, $orderid)
{
    $filename = "requestjsons/order-" . $orderid . ".txt";
    $myfile = fopen($filename, "w");
    fwrite($myfile, $json);
    fclose($myfile);
}

function callWebhookAndArchiveFile($json)
{
    $url = "http://192.168.200.236/api/workarea/order/orders.php";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
    $result = curl_exec($ch);
    rename("requestjsons/" . $fileinfo->getFilename(), 'requestjsons/archive/' . $fileinfo->getFilename());
}

function makeGETRequest($url)
{
    $ch = curl_init();
    $request_headers = [
        //'Accept: application/json',
        //'Accept-Encoding: gzip, deflate',
        //"Connection: keep-alive",
        "Content-Type: application/json",
        "Authorization: Basic c2J0aW50ZWdyYXRpb25AY29zbW8uY2E6Q29zbW8yMDIwJA==",
    ];
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => $request_headers,
    ];
    curl_setopt_array($ch, $options);
    $result['content'] = curl_exec($ch);
    $result['header'] = curl_getinfo($ch);
    $result['error'] = curl_error($ch);
    return $result;
}