<?php

/**
 * Rave Callback File
 *
 *
 * For more information, please refer to the online documentation.
 *
 * @author Oluwole Adebiyi <flamekeed@gmail.com>
 *
 * @copyright Copyright (c) Oluwole Adebiyi 2017
 */
// Require Database Capsule for query
use WHMCS\Database\Capsule;
// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');
// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);
// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

$secretKey = $gatewayParams['testSecretKey'];

if ($gatewayParams['testMode'] != 'on') {
    $secretKey = $gatewayParams['secretKey'];
}


// Retrieve data returned in payment gateway callback
// Varies per payment gateway
$invoiceId = explode('_', $_GET["txref"]);
$invoiceId = $invoiceId[0];
$transactionId = $_GET["txref"];
$paymentAmount = $_GET["a"];
$success = false;

$apiLink = "https://ravesandboxapi.flutterwave.com/";
if ($gatewayParams['testMode'] != 'on') {
    $apiLink = "https://api.ravepay.co/";
}

$isSSL = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443);
$whmcsLink = 'http' . ($isSSL ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . substr(str_replace('/admin/', '/', $_SERVER['REQUEST_URI']), 0, strrpos($_SERVER['REQUEST_URI'], '/'));

/**
 * Validate Callback Invoice ID.
 */

$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);
/**
 * Check Callback Transaction ID, checks for any existing transactions.
 */
checkCbTransID($transactionId);

/**
 * Gets the amount from the server
 */
$cashquery = Capsule::table('tblinvoices')
    ->where('id', '=', $invoiceId)->first();
$cash = $cashquery->total;

/**
 * Gets the currency ID and Code from the server
 */
$result = select_query("tblclients", "tblinvoices.invoicenum,tblclients.currency,tblcurrencies.code", array("tblinvoices.id" => $invoiceId), "", "", "", "tblinvoices ON tblinvoices.userid=tblclients.id INNER JOIN tblcurrencies ON tblcurrencies.id=tblclients.currency");
$data = mysql_fetch_array($result);
$invoice_currency_id = $data['currency'];
$invoice_currency_code = $data['code'];


// /**
//  * Converts to the Currency set if on
//  */
// if ($gatewayParams['convertto']) {
//     $converto_amount = convertCurrency($amount, $gatewayParams['convertto'], $invoice_currency_id);
//     $cash = format_as_currency($converto_amount);
// }

//Removes trailing zeros
$money = $cash + 0;
$requeryCount = 0;

//Verify Transaction
if (isset($_GET['txref'])) {
    return requery();
}

function requery()
{
    $txref = $_GET['txref'];
    $GLOBALS['requeryCount']++;


    $data = array(
        'txref' => $txref,
        'SECKEY' => $GLOBALS['secretKey'],
        'last_attempt' => '1'
        // 'only_successful' => '1'
    );

    // make request to endpoint.
    $data_string = json_encode($data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $GLOBALS['apiLink'] . 'flwv3-pug/getpaidx/api/xrequery');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = curl_exec($ch);

    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);

    curl_close($ch);

    $resp = json_decode($response, false);

    if ($resp && $resp->status === "success") {
        if ($resp && $resp->data && $resp->data->status === "successful") {
            verifyTransaction($resp->data);
        } elseif ($resp && $resp->data && $resp->data->status === "failed") {

            return failed($resp->data);
        } else {
                // I will requery again here. Just incase we have some devs that cannot setup a queue for requery. I don't like this.
            if ($GLOBALS['requeryCount'] > 4) {
                return failed($resp->data);
            } else {
                sleep(3);
                return requery();
            }
        }
    } else {
        if ($GLOBALS['requeryCount'] > 4) {
            return failed($resp->data);
        } else {
            sleep(3);
            return requery();
        }
    }
}

/**
 * Requeries a previous transaction from the Rave payment gateway
 * @param string $referenceNumber This should be the reference number of the transaction you want to requery
 * @return object
 * */
function verifyTransaction($data)
{
    $currency = $GLOBALS['invoice_currency_code'];
    $amount = $GLOBALS['money'];
    $invoiceId = $GLOBALS['invoiceId'];
    $invoice_url = $GLOBALS['whmcsLink'] . '/../../../viewinvoice.php?id=' . rawurlencode($invoiceId);

    if (($data->chargecode == "00" || $data->chargecode == "0") && ($data->amount == $amount) && ($data->currency == $currency)) {
        addInvoicePayment(
            $invoiceId,
            $data->txref,
            $amount,
            null,
            $GLOBALS['gatewayModuleName']
        );
            // Add transaction to Gateway logs
        if ($gatewayParams['gatewayLogs'] == 'on') {
            $log = "Transaction ref: " . $data->txref
                . "\r\nInvoice ID: " . $invoiceId
                . "\r\nStatus: " . $data->status
                . "\r\nCharge Code: " . $data->chargecode
                . "\r\nCurrency: " . $data->currency
                . "\r\namount: " . $amount
                . "\r\nResponse: " . $data;
            logTransaction($GLOBALS['gatewayModuleName'], $log, "Successful");
        }
        header("Location: " . $invoice_url);
        exit;
    } else {
        return failed($data);
    }
}

function failed($data)
{
    $currency = $GLOBALS['invoice_currency_code'];
    $amount = $GLOBALS['money'];
    $invoiceId = $GLOBALS['invoiceId'];
    $invoice_url = $GLOBALS['whmcsLink'] . '/../../../viewinvoice.php?id=' . rawurlencode($invoiceId);

    // Add transaction to Gateway logs
    if ($gatewayParams['gatewayLogs'] == 'on') {
        $log = "Transaction ref: " . $data->txref
            . "\r\nInvoice ID: " . $invoiceId
            . "\r\nStatus: Failed"
            . "\r\nCharge Code: " . $data->chargecode
            . "\r\nCharged Currency: " . $data->currency
            . "\r\nCharged amount: " . $amount
            . "\r\nResponse: " . $data;
        logTransaction($gatewayModuleName, $log, "Failed");
    }
    $error = ($_GET['cancelled'] == true) ? "You cancelled the transaction" : "Transaction Failed";

    echo '<!DOCTYPE html>
    <html lang="">
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet">
            <title>Failed Transaction</title>
    
            <!-- Bootstrap CSS -->
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    
            <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
            <!-- WARNING: Respond.js doesn\'t work if you view the page via file:// -->
            <!--[if lt IE 9]>
                <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.min.js"></script>
                <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
            <![endif]-->
            <style>
            html, body{
                font-family: \'Raleway\', sans-serif;
            }
            h1{
                font-weight: bold;
                color: #f00;
            }
            </style>
        </head>
        <body class="text-center" style="padding-top=20%;">
            <h1>Failed Transaction</h1>
            <p>Response Code - ' . $data->chargecode . '</p>
            <p>Response Message - ' . $data->chargemessage . '</p>
            <p>' . $error . '</p>
            </br>
            <a class="btn btn-primary" href="' . $invoice_url . '">Back to Invoice</a>
    
            <!-- jQuery -->
            <script src="//code.jquery.com/jquery.js"></script>
            <!-- Bootstrap JavaScript -->
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
            <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
             <script src="Hello World"></script>
        </body>
    </html>';
    die();
    exit;
}
