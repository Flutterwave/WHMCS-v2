<?php

/**
 * Rave by Flutterwave Payment Gateway Module
 *
 * This Payment Gateway module allows you to integrate Rave payment solutions with the
 * WHMCS platform.
 *
 * For more information, please refer to the online documentation.
 *
 * @author Oluwole Adebiyi <flamekeed@gmail.com>
 *
 * @copyright Copyright (c) Oluwole Adebiyi 2017
 */



if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function rave_MetaData()
{
    return array(
        'DisplayName' => 'Rave by Flutterwave',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    );
}

function rave_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Rave by Flutterwave',
        ),

        'cBname' => array(
            'FriendlyName' => 'Company/Business Name',
            'Type' => 'text',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Enter company/business name here',
        ),

        'cBdescription' => array(
            'FriendlyName' => 'Company/Business Description',
            'Type' => 'text',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Enter company/business description here',
        ),

        'whmcsLogo' => array(
            'FriendlyName' => 'Logo',
            'Type' => 'text',
            'Size' => '80',
            'Default' => '',
            'Description' => 'Enter the link to your logo, square size',
        ),

        'paymentMethod' => array(
            'FriendlyName' => 'Payment Method',
            'Type' => 'dropdown',
            'Options' => array(
                'both' => 'All',
                'card' => 'Card Only',
                'account' => 'Account Only',
                'ussd' => 'USSD Only'
            ),
            'Description' => 'Choose your payment method!',
        ),

        'country' => array(
            'FriendlyName' => 'Country',
            'Type' => 'dropdown',
            'Options' => array(
                'NG' => 'Nigeria',
                'GH' => 'Ghana',
                'KE' => 'Kenya'
            ),
            'Description' => 'Choose your country!',
        ),

        'PBFPubKey' => array(
            'FriendlyName' => 'Live Public Key',
            'Type' => 'text',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Enter public key here',
        ),

        'secretKey' => array(
            'FriendlyName' => 'Live Secret Key',
            'Type' => 'text',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Enter secret key here',
        ),

        'testPBFPubKey' => array(
            'FriendlyName' => 'Test Public Key',
            'Type' => 'text',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Enter public key here',
        ),

        'testSecretKey' => array(
            'FriendlyName' => 'Test Secret Key',
            'Type' => 'text',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Enter secret key here',
        ),

        'paymentWay' => array(
            'FriendlyName' => 'Payment Way',
            'Type' => 'dropdown',
            'Options' => array(
                'redirection' => 'Redirection (Hosted) - Good for opera mini phones',
                'modal' => 'Modal (Inline)'
            ),
            'Description' => 'Choose the way you want the rave form to show',
        ),

        'payButtonText' => array(
            'FriendlyName' => 'Pay Button Text',
            'Type' => 'text',
            'Size' => '25',
            'Default' => 'Pay Now',
            'Description' => 'Text to display on your payment button',
        ),

        'testMode' => array(
            'FriendlyName' => 'Test Mode',
            'Type' => 'yesno',
            'Description' => 'Tick to enable test mode',
        ),

        'gatewayLogs' => array(
            'FriendlyName' => 'Gateway logs',
            'Type' => 'yesno',
            'Description' => 'Select to enable gateway logs',
            'Default' => '0'
        ),
    );
}

function rave_link($params)
{
    $stagingUrl = 'https://ravesandboxapi.flutterwave.com';
    $liveUrl = 'https://api.ravepay.co';
    $isSSL = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443);


    $PBFPubKey = $params['testPBFPubKey'];
    $secretKey = $params['testSecretKey'];

    if ($params['testMode'] != 'on') {
        $PBFPubKey = $params['PBFPubKey'];
        $secretKey = $params['secretKey'];
    }

    
    $payButtonText = $params['payButtonText'];
    $cBname = $params['cBname'];
    $cBdescription = $params['cBdescription'];
    $whmcsLink = 'http' . ($isSSL ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . substr(str_replace('/admin/', '/', $_SERVER['REQUEST_URI']), 0, strrpos($_SERVER['REQUEST_URI'], '/'));
    $whmcsLogo = $params['whmcsLogo'];
    $paymentMethod = $params['paymentMethod'];
    $country = $params['country'];


    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];
    $strippedAmount = $amount + 0;


    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $phone = $params['clientdetails']['phonenumber'];

    $txref = $invoiceId . '_' . time();

    $postfields = array();
    $postfields['PBFPubKey'] = $PBFPubKey;
    $postfields['customer_email'] = $email;
    $postfields['customer_firstname'] = $firstname;
    $postfields['custom_logo'] = $whmcsLogo;
    $postfields['customer_lastname'] = $lastname;
    $postfields['custom_description'] = $cBdescription;
    $postfields['custom_title'] = $cBname;
    $postfields['customer_phone'] = $phone;
    $postfields['country'] = $country;
    $postfields['txref'] = $invoiceId . '_' . time();
    $postfields['payment_method'] = $paymentMethod;
    $postfields['amount'] = $strippedAmount;
    $postfields['currency'] = $currencyCode;
    if ($params['paymentWay'] == 'redirection') {
        $postfields['redirect_url'] = $whmcsLink . '/modules/gateways/callback/rave.php';
        $postfields['hosted_payment'] = 1;
    }
    

    ksort($postfields);
    $stringToHash = "";
    foreach ($postfields as $key => $val) {
        $stringToHash .= $val;
    }


    $stringToHash .= $secretKey;

    $hashedValue = hash('sha256', $stringToHash);

    $env = "staging";

    $baseUrl = $stagingUrl;

    if ($params['testMode'] != 'on') {
        $baseUrl = $liveUrl;
    }

    $transactionData = array_merge($postfields, array('integrity_hash' => $hashedValue));
    $json = json_encode($transactionData);

    $datas = "";

    foreach ($transactionData as $key => $value) {
        $datas.= $key.": '". $value."',";
    }

    if ($params['paymentWay'] == 'redirection') {
        $htmlOutput = "<form onsubmit='event.preventDefault(); pay();'>
          <button type='submit' class='btn btn-primary' style='cursor:pointer;' value='" . $payButtonText . "' id='ravepaybutton'>" . $payButtonText . "</button>
        </form>
        <script type='text/javascript' src='" . $baseUrl . "/flwv3-pug/getpaidx/api/flwpbf-inline.js'></script>
        <script>
        function pay() {
        var data = JSON.parse('" . json_encode($transactionData = array_merge($postfields, array('integrity_hash' => $hashedValue))) . "');
        getpaidSetup(data);}
        pay();
        </script>
        ";
    }

    else{
        $htmlOutput = "<form onsubmit='event.preventDefault(); pay();'>
          <button type='submit' class='btn btn-primary' style='cursor:pointer;' value='" . $payButtonText . "' id='ravepaybutton'>" . $payButtonText . "</button>
        </form>
        <script type='text/javascript' src='" . $baseUrl . "/flwv3-pug/getpaidx/api/flwpbf-inline.js'></script>
        <script>
        function pay() {
        var data = JSON.parse('" . json_encode($transactionData = array_merge($postfields, array('integrity_hash' => $hashedValue))) . "');
        getpaidSetup({".
            $datas
        . "
        onclose: function() {
            window.location = '" . $whmcsLink . "/modules/gateways/callback/rave.php?txref=" . $postfields['txref'] . "&cancelled=true';
        },
          callback: function(response) {
            var flw_ref = response.tx.flwRef; // collect flwRef returned and pass to a                  server page to complete status check.
            console.log('This is the response returned after a charge', response);
            if (
              response.tx.chargeResponseCode == '00' ||
              response.tx.chargeResponseCode == '0'
            ) {
              window.location = '".$whmcsLink ."/modules/gateways/callback/rave.php?txref=".$postfields['txref']."';
            } else {
              window.location = '".$whmcsLink ."/modules/gateways/callback/rave.php?txref=".$postfields['txref']."';
            }
          }

    });}
    pay();
        </script>
        ";
    }

    return $htmlOutput;
}