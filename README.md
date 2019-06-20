# WHMCS-Rave-payment-module
This is a payment Module for WHMCS that allows you to accept payments using Flutterwave Rave
 - **Tags:** rave, flutterwave, payment gateway, bank account, credit card, debit card, nigeria, kenya, ghana, international, mastercard, visa, whmcs

## Requirements

- WHMCS installation.

## Description

Accept Credit card, Debit card and Bank account payment directly on WHMCS with the Rave payment gateway.

Signup for an account [here](https://rave.flutterwave.com)

Rave is available in:

* __Nigeria__
* __Ghana__
* __Kenya__
* __South Africa__

## Install
1. Copy [rave.php](modules/gateways/rave.php?raw=true) in [modules/gateways](modules/gateways) to the `/modules/gateways/` folder of your WHMCS installation.

2. Copy [rave.php](modules/gateways/callback/rave.php?raw=true) in [modules/gateways/callback](modules/gateways/callback) to the `/modules/gateways/callback` folder of your WHMCS installation.

3. Go to your WHMCS admin page, and click on `Setup > Payments > Payment Gateways`
![Rave Installation Screenshot](https://raw.githubusercontent.com/kingflamez/WHMCS-Rave-payment-module/master/img/admin.jpg)

4. Click on all payment gateways and click on Rave Payment to activate
![Rave Installation Screenshot](https://raw.githubusercontent.com/kingflamez/WHMCS-Rave-payment-module/master/img/rave.jpg)

5. Enter your details and save
![Rave Installation Screenshot](https://raw.githubusercontent.com/kingflamez/WHMCS-Rave-payment-module/master/img/settings.jpg)

>Test Card

```bash
5438898014560229
cvv 564
Expiry: 10/20
Pin 3310
otp 12345
```

>Test Bank Account

```bash
Access Bank
Account number: 0690000004
otp: 12345
```

```bash
Providus Bank
Account number: 5900102340, 5900002567
otp: 12345
```

* __Company/Business Name__ - (Compulsory) customize the title of the Pay Modal.
* __Company/Business Description__ - (Compulsory) customize the description on the Pay Modal.
* __Logo__ - (Optional) customize the logo on the Pay Modal. Enter a full url (with 'http'). Default is Rave logo.
* __Payment Method__ - (Compulsory) Card Only, Account only, USSD only or All. 
* __Public Key__ - Enter your public key which can be retrieved from "Pay Buttons" page on your Rave account dashboard.
* __Secret Key__ - Enter your public key which can be retrieved from "Pay Buttons" page on your Rave account dashboard.
* __Payment Way__ - This specifies if you want a pop up modal of rave(inline) or it should redirect(hosted)
* __Pay Button Text__ - (Optional) The text to display on the button. Default: "PAY NOW".
* __Test Mode__ - Tick to enable test mode.
* __Gateway Logs__ - Tick to enable gateway logs.
* Click __Save Changes__ to save your changes.

## ToDo
- Recurring Payments

