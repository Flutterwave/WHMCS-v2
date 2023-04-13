# WHMCS-Rave-payment-module

This is a payment Module for WHMCS that allows you to accept payments using Flutterwave Rave

- **Tags:** rave, flutterwave, payment gateway, bank account, credit card, debit card, nigeria, kenya,tanzania, ghana, international, mastercard, visa, whmcs

## Requirements

- WHMCS installation.

## Description

Accept Credit card, Debit card and Bank account payment directly on WHMCS with the Rave payment gateway.

Signup for an account [here](https://rave.flutterwave.com)

Rave is available in:

- **Nigeria**
- **Ghana**
- **Kenya**
- **South Africa**
- **Tanzania**

## Install

1. Copy [rave.php](modules/gateways/rave.php?raw=true) in [modules/gateways](modules/gateways) to the `/modules/gateways/` folder of your WHMCS installation.

2. Copy [rave.php](modules/gateways/callback/rave.php?raw=true) in [modules/gateways/callback](modules/gateways/callback) to the `/modules/gateways/callback` folder of your WHMCS installation.

3. Go to your WHMCS admin page, and click on `Setup > Payments > Payment Gateways`
   ![Rave Installation Screenshot](https://raw.githubusercontent.com/kingflamez/WHMCS-Rave-payment-module/master/img/admin.jpg)

4. Click on all payment gateways and click on Rave Payment to activate
   ![Rave Installation Screenshot](https://raw.githubusercontent.com/kingflamez/WHMCS-Rave-payment-module/master/img/rave.jpg)

5. Enter your details and save
   ![Rave Installation Screenshot](https://raw.githubusercontent.com/kingflamez/WHMCS-Rave-payment-module/master/img/settings.jpg)

> Test Card

```bash
5438898014560229
cvv 564
Expiry: 10/20
Pin 3310
otp 12345
```

> Test Bank Account

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

- **Company/Business Name** - (Compulsory) customize the title of the Pay Modal.
- **Company/Business Description** - (Compulsory) customize the description on the Pay Modal.
- **Logo** - (Optional) customize the logo on the Pay Modal. Enter a full url (with 'http'). Default is Rave logo.
- **Payment Method** - (Compulsory) Card Only, Account only, USSD only or All.
- **Public Key** - Enter your public key which can be retrieved from "Pay Buttons" page on your Rave account dashboard.
- **Secret Key** - Enter your public key which can be retrieved from "Pay Buttons" page on your Rave account dashboard.
- **Payment Way** - This specifies if you want a pop up modal of rave(inline) or it should redirect(hosted)
- **Pay Button Text** - (Optional) The text to display on the button. Default: "PAY NOW".
- **Test Mode** - Tick to enable test mode.
- **Gateway Logs** - Tick to enable gateway logs.
- Click **Save Changes** to save your changes.

## ToDo

- Recurring Payments
