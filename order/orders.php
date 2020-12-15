<?php
include $_SERVER['DOCUMENT_ROOT'] . "/_common/mysql-helper.php";

try
{
    $headerStringValue = "";
    if (isset($_SERVER['X-Workarea-Event'])) $headerStringValue = $_SERVER['X-Workarea-Event'];
    if (isset($_SERVER['HTTP_X-Workarea-Event'])) $headerStringValue = $_SERVER['HTTP_X-Workarea-Event'];
    if ($headerStringValue != "") file_put_contents('requestjsons/order-'.date('m-d-Y_hia').'-WorkAreaEventHeader.txt', $headerStringValue);
    $jsonitem = file_get_contents("php://input");
    //$jsonitem = stripslashes($jsonitem);
    $orderData = json_decode($jsonitem);
    $orderid = "";
    if (isset($orderData->order->_id)) {
        $orderid = $orderData->order->_id;
    } else {
        $orderid = $orderData->_id;
    }
    if (isset($orderid))
    {
        file_put_contents('requestjsons/order-'. $orderid .'.txt', $jsonitem);

        beginOrderFetchLog($orderid);
        $orderData = getAndCacheJSONFiles($orderid);
        updateOrderFetchLog($orderid, "JSON files written", "JSON files have been cached into MySQL", "0");
        writeOrderInformation($orderData);
        updateOrderFetchLog($orderid, "Written To MySQL", "Order information is ready to be processed by SBT", "0");
        triggerVFPInsert($orderid);
    }
    else
    {
        file_put_contents('requestjsons/order-'.date('m-d-Y_hia').'.txt', $jsonitem);
    }

}
catch (Exception $ex)
{
    echo $ex;
}

 
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
    $orderInfo = callWorkAreaAPI("https://cosmomusic.ca/api/admin/orders/" . $orderid, true);
    $columnNames = array("order_id", "order_json");
    $rowValues = array($orderid, $orderInfo);
    $tableName = "workarea.order_json";
    generateSqlQueryForInsert($columnNames, $rowValues, $tableName);
    return json_decode($orderInfo);
}

function writeOrderInformation($orderData)
{
    $customer_email = $orderData->order->email;
    $customer_user_id = $orderData->order->user_id;

    $order_id = $orderData->order->_id;
    $order_ip_address = $orderData->order->ip_address;
    $order_placed_at = date("Y-m-d H:i:s", strtotime($orderData->order->placed_at));

    $order_subtotal_price = $orderData->order->subtotal_price->cents / 100;
    $order_tax_total = $orderData->order->tax_total->cents / 100;
    $order_total_price = $orderData->order->total_price->cents / 100;
    $order_total_value = $orderData->order->total_value->cents / 100;
    $order_token = $orderData->payment->credit_card->token;
    $order_shipping_total = $orderData->order->shipping_total->cents / 100;

    $payment_display_number = $orderData->payment->credit_card->display_number;
    $payment_issuer = $orderData->payment->credit_card->issuer;
    $payment_card_type = null; //$orderData->payment_transactions[0]->response->params->card->type;
    $payment_card_last_digits = null; //$orderData->payment_transactions[0]->response->params->card->lastDigits;
    $payment_card_expiry_month = null; //$orderData->payment_transactions[0]->response->params->card->cardExpiry->month;
    $payment_card_expiry_year = null; //$orderData->payment_transactions[0]->response->params->card->cardExpiry->year;

    $address_bill_id = $orderData->payment->address->_id;
    $address_bill_city = $orderData->payment->address->city;
    $address_bill_company = $orderData->payment->address->company;
    $address_bill_country = $orderData->payment->address->country;
    $address_bill_first_name = $orderData->payment->address->first_name;
    $address_bill_last_name = $orderData->payment->address->last_name;
    $address_bill_phone_extension = $orderData->payment->address->phone_extension;
    $address_bill_phone_number = $orderData->payment->address->phone_number;
    $address_bill_postal_code = $orderData->payment->address->postal_code;
    $address_bill_region = $orderData->payment->address->region;
    $address_bill_street = $orderData->payment->address->street;
    $address_bill_street_2 = $orderData->payment->address->street_2;
    $shipvia = $orderData->shippings[0]->shipping_service->carrier;
    if ($shipvia == "PICKUP_AT_COSMO")
    {
        $shipvia = "PICKUP";
    }
    else
    {
        $shipvia = "";
    }
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

    if (count($orderData->shippings) > 0)
    {
        $address_ship_id = $orderData->shippings[0]->address->_id;
        $address_ship_city = $orderData->shippings[0]->address->city;
        $address_ship_company = $orderData->shippings[0]->address->company;
        $address_ship_country = $orderData->shippings[0]->address->country;
        $address_ship_first_name = $orderData->shippings[0]->address->first_name;
        $address_ship_last_name = $orderData->shippings[0]->address->last_name;
        $address_ship_phone_extension = $orderData->shippings[0]->address->phone_extension;
        $address_ship_phone_number = $orderData->shippings[0]->address->phone_number;
        $address_ship_postal_code = $orderData->shippings[0]->address->postal_code;
        $address_ship_region = $orderData->shippings[0]->address->region;
        $address_ship_street = $orderData->shippings[0]->address->street;
        $address_ship_street_2 = $orderData->shippings[0]->address->street_2;
    }

    $columnNames = array("customer_email", "customer_user_id", "order_id", "order_ip_address", "order_placed_at", "order_subtotal_price", "order_tax_total",
                    "order_total_price", "order_total_value", "order_token", "order_shipping_total", "address_bill_id", "address_bill_city", "address_bill_company", "address_bill_country",
                    "address_bill_first_name", "address_bill_last_name", "address_bill_phone_extension", "address_bill_phone_number", "address_bill_postal_code",
                    "address_bill_region", "address_bill_street", "address_bill_street_2", "address_ship_id", "address_ship_city", "address_ship_company", 
                    "address_ship_country", "address_ship_first_name", "address_ship_last_name", "address_ship_phone_extension", "address_ship_phone_number", 
                    "address_ship_postal_code", "address_ship_region", "address_ship_street", "address_ship_street_2", "payment_display_number", "payment_issuer", "payment_card_type", 
                    "payment_card_last_digits", "payment_card_expiry_month", "payment_card_expiry_year", "shipvia");
    $rowValues = array($customer_email, $customer_user_id, $order_id, $order_ip_address, $order_placed_at, (string)$order_subtotal_price, (string)$order_tax_total,
                    (string)$order_total_price, (string)$order_total_value, $order_token, (string)$order_shipping_total, $address_bill_id, $address_bill_city, $address_bill_company, $address_bill_country,
                    $address_bill_first_name, $address_bill_last_name, $address_bill_phone_extension, $address_bill_phone_number, $address_bill_postal_code,
                    $address_bill_region, $address_bill_street, $address_bill_street_2, $address_ship_id, $address_ship_city, $address_ship_company,
                    $address_ship_country, $address_ship_first_name, $address_ship_last_name, $address_ship_phone_extension, $address_ship_phone_number,
                    $address_ship_postal_code, $address_ship_region, $address_ship_street, $address_ship_street_2, $payment_display_number, $payment_issuer, $payment_card_type,
                    $payment_card_last_digits, $payment_card_expiry_month, $payment_card_expiry_year, $shipvia);
    $tableName = "workarea.order_log";
    generateSqlQueryForInsert($columnNames, $rowValues, $tableName);
    $tableName = "workarea.order_log_item";
    foreach($orderData->order->items as $item)
    {
        $id = $item->_id;
        $item_sku = $item->sku;
        $item_amount = $item->total_price->cents / 100 / $item->quantity;
        $item_tax_code = $item->price_adjustments[0]->data->tax_code;
        $item_quantity = $item->quantity;
        $item_total_price = $item->total_price->cents / 100;

        foreach ($item->price_adjustments as $adj) 
        {
            
            if(strpos($adj->calculator, 'Discount') !== false)
            {
                $disc_amount = $adj->amount->cents / 100;
                $item_amount = $item_amount + $disc_amount / $item_quantity;
            }
        }
        
        $columnNames = array("order_id", "sku", "amount", "taxcode", "quantity", "total_price");
        $rowValues = array($order_id, $item_sku, (string)$item_amount, $item_tax_code, (string)$item_quantity, (string)$item_total_price);
        generateSqlQueryForInsert($columnNames, $rowValues, $tableName);
    }
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

function triggerVFPInsert($orderid)
{
    $columnNames = array("trigger_path", "trigger_filename", "trigger_variables");
    $rowValues = array("E:\pro32\VendorIntegrations", "wa_order_import.prg", "'" . $orderid . "'");
    $tableName = "integration.foxpro_queue";
    generateSqlQueryForInsert($columnNames, $rowValues, $tableName);
}