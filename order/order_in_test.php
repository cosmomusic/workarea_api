<?php
include $_SERVER['DOCUMENT_ROOT'] . "/_common/mysql-helper.php";
ini_set('max_execution_time', '0'); // for infinite time of execution 

$dir = new DirectoryIterator(dirname("../order/requestjsons/requestjsons"));
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot()) {
        if ($fileinfo->getFilename() == "archive") continue;
        $filename = "requestjsons/" . $fileinfo->getFilename();
        $jsonitem = file_get_contents($filename);
        
        $url = "localhost/workarea/order/orders.php";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonitem);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
        $result = curl_exec($ch);
        rename("requestjsons/" . $fileinfo->getFilename(), 'requestjsons/archive/' . $fileinfo->getFilename());
        
    }
}
echo 'complete';

