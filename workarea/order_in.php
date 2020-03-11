<?php
include $_SERVER['DOCUMENT_ROOT'] . "/_common/mysql-helper.php";

$orderid = (isset($_GET['order_id']) === true) ? $_GET['order_id'] : "";
beginOrderFetchLog($orderid);
$orderData = getAndCacheJSONFiles($orderid);
updateOrderFetchLog($orderid, "JSON files written", "JSON files have been cached into MySQL", "0");
writeOrderInformation($orderData);
updateOrderFetchLog($orderid, "Written To MySQL", "Order information is ready to be processed by SBT", "0");

function beginOrderFetchLog($orderid)
{
    $columnNames = array("order_id", "status", "message", "success");
    $rowValues = array($orderid, "Received", "Received Order Ping", "0");
    $tableName = "workarea.order_fetch_log";
    generateSqlQueryForInsert($columnNames, $rowValues, $tableName);
}

function updateOrderFetchLog($orderid, $status, $message, $success)
{
    $columnsToUpdate = array("status", "message", "success");
    $valuesToUpdate = array($status, $message, $success);
    $columnsToQueryAgainst = array("order_id");
    $valuesToQueryAgainst = array($orderid);
    generateSqlQueryForUpdate('workarea.order_fetch_log', $columnsToUpdate, $valuesToUpdate, $columnsToQueryAgainst, $valuesToQueryAgainst);
}

function getAndCacheJSONFiles($orderid)
{
    $orderInfo = callWorkAreaAPI("https://www.staging.cosmomusic.weblinc.com/api/admin/orders/" . $orderid, true);
    $columnNames = array("order_id", "order_json");
    $rowValues = array($orderid, $orderInfo);
    $tableName = "workarea.order_json";
    generateSqlQueryForInsert($columnNames, $rowValues, $tableName);
    return json_decode($orderInfo);
}

function writeOrderInformation($orderData)
{
    $customer_email = "";
    $customer_user_id = "";

    $order_id = "";
    $order_subtotal_price = "";
    $order_tax_total = "";
    $order_total_price = "";
    $order_total_value = "";
    $order_token = "";
    $order_shipping_total = "";
    $order_placed_at = "";
    $order_ip_address = "";
    $order_ip_address = "";
    $order_placed_at = "";

    $address_bill_id = "";
    $address_bill_city = "";
    $address_bill_company = "";
    $address_bill_country = "";
    $address_bill_first_name = "";
    $address_bill_last_name = "";
    $address_bill_phone_extension = "";
    $address_bill_phone_number = "";
    $address_bill_postal_code = "";
    $address_bill_region = "";
    $address_bill_street = "";
    $address_bill_street_2 = "";

    $address_ship_id = "";
    $address_ship_city = "";
    $address_ship_company = "";
    $address_ship_country = "";
    $address_ship_first_name = "";
    $address_ship_last_name = "";
    $address_ship_phone_extension = "";
    $address_ship_phone_number = "";
    $address_ship_postal_code = "";
    $address_ship_region = "";
    $address_ship_street = "";
    $address_ship_street_2 = "";

    //loop
    $item_amount = "";
    $item_tax_code = "";
    $item_quantity = "";
    $item_sku = "";
    $item_total_price = "";
}

function callWorkAreaAPI($url, $returnFullJson = false)
{
    $result = makeGETRequest($url);
    if ($returnFullJson) {
        return $result["content"];
    } else {
        return json_decode($result["content"]);
    }
}

function makeGETRequest($url)
{
    //dGVzdF9oaW5lczpCLXFhMi0wLTVjN2U4NjkzLTAtMzAyYzAyMTQ1OThkMWFkODE2NzAwMWQ0MzEwYTNhMzAxNjljNTM1ZTk2OTliYjg4MDIxNDYwODVjZGY0NTY0NDc2MTBjNzA0Y2E1NGE4Y2RlOGYxZDg2MmE2MjU=
    $ch = curl_init();
    $request_headers = [
        //'Accept: application/json',
        //'Accept-Encoding: gzip, deflate',
        //"Connection: keep-alive",
        "Content-Type: application/json",
        "Authorization: Basic cGV0ZXJAY29zbW8uY2E6S2F0eWlhbiEx",
    ];
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_RETURNTRANSFER => true,
        //   CURLOPT_FOLLOWLOCATION => true,
        //   CURLOPT_USERAGENT => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:53.0) Gecko/20100101 Firefox/53.0",
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        //   CURLOPT_AUTOREFERER => true,
        //   CURLOPT_COOKIESESSION => true,
        //   CURLOPT_FILETIME => true,
        //   CURLOPT_FRESH_CONNECT => true,
        CURLOPT_HTTPHEADER => $request_headers,
        //   CURLOPT_COOKIESESSION => true,
        //   CURLOPT_ENCODING => "gzip, deflate, scdh",
        //   CURLOPT_POSTFIELDS => $jsondata,
        //   CURLOPT_POST => 1,
    ];
    curl_setopt_array($ch, $options);
    $result['content'] = curl_exec($ch);
    $result['header'] = curl_getinfo($ch);
    $result['error'] = curl_error($ch);
    return $result;
}
