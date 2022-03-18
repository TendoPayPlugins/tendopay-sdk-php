# TendoPay SDK for PHP (v2)

If you find a document for v1, please go to [TendoPay SDK for PHP (v1)](./README_v1.md)

## Requirements

PHP 7.0 and later.

## Upgrade

[UPGRADE from v1](./UPGRADE.md)

## Installation

### Using Composer

You can install the sdk via [Composer](http://getcomposer.org/). Run the following command:

```bash
composer require tendopay/tendopay-sdk-php
```

## Run SDK Tester

- Run a sample server
```bash
php -s localhost:8000 -t vendor/tendopay/tendopay-sdk-php/samples
```

- Open browser and goto
```bash
http://localhost:8000/
```

## Code Examples

### Create TendoPayClient

- Using .env
  > MERCHANT_ID,MERCHANT_SECRET for test can get them at [TendoPay Sandbox](https://sandbox.tendopay.ph)

```bash
## Client Credentials
CLIENT_ID=
CLIENT_SECRET=

## Redirect URI when the transaction is processed 
REDIRECT_URL=https://localhost:8000/purhase.php

## Enable Sandbox, it must be false in production
TENDOPAY_SANDBOX_ENABLED=false
```

```php
use TendoPay\SDK\TendoPayClient;

$client = new TendoPayClient();
```

- Using $config variable

```php
use TendoPay\SDK\TendoPayClient;

$config = [
    'CLIENT_ID' => '',
    'CLIENT_SECRET' => '',
    'REDIRECT_URL' => '',
    'TENDOPAY_SANDBOX_ENABLED' => false,
];
$client = new TendoPayClient($config);
```


### Make Payment

```php
use TendoPay\SDK\Exception\TendoPayConnectionException;
use TendoPay\SDK\Models\Payment;
use TendoPay\SDK\V2\TendoPayClient;

### S:Merchant set proper values
$merchant_order_id = $_POST['tp_merchant_order_id'];
$request_order_amount = $_POST['tp_amount'];
$request_order_title = $_POST['tp_description'];
$redirectUrl = $_POST['tp_redirect_url'] ?? '';
### E:Merchant set proper values

$client = new TendoPayClient();

try {
    $payment = new Payment();
    $payment->setMerchantOrderId($merchant_order_id)
        ->setDescription($request_order_title)
        ->setRequestAmount($request_order_amount)
        ->setCurrency('PHP')
        ->setRedirectUrl($redirectUrl);


    $client->setPayment($payment);

    $redirectURL = $client->getAuthorizeLink();
    header('Location: '.$redirectURL);
} catch (TendoPayConnectionException $e) {
    echo 'Connection Error:'.$e->getMessage();
} catch (Exception $e) {
    echo 'Runtime Error:'.$e->getMessage();
}
```

### Callback (redirected page)

```php
use TendoPay\SDK\Exception\TendoPayConnectionException;
use TendoPay\SDK\Models\VerifyTransactionRequest;
use TendoPay\SDK\V2\TendoPayClient;

$client = new TendoPayClient();

try {
    if (TendoPayClient::isCallBackRequest($_REQUEST)) {
        $transaction = $client->verifyTransaction(new VerifyTransactionRequest($_REQUEST));

        if (!$transaction->isVerified()) {
            throw new UnexpectedValueException('Invalid signature for the verification');
        }

        if ($transaction->getStatus() == \TendoPay\SDK\V2\ConstantsV2::STATUS_SUCCESS) {
            // PAID
            // Save $transactionNumber here
            // Proceed merchant post order process
        } else if ($transaction->getStatus() == \TendoPay\SDK\V2\ConstantsV2::STATUS_FAILURE) {
            // FAILED
            // do something in failure case
            // error message $transaction->getMessage()
        }       
    }
} catch (TendoPayConnectionException $e) {
    echo 'Connection Error:'.$e->getMessage();
} catch (Exception $e) {
    echo 'Runtime Error:'.$e->getMessage();
}
```

### Cancel Payment

```php
use TendoPay\SDK\Exception\TendoPayConnectionException;
use TendoPay\SDK\V2\TendoPayClient;

$client = new TendoPayClient();

try {
    $client->cancelPayment($transactionNumber);
    // merchant process here

} catch (TendoPayConnectionException $e) {
    echo 'Connection Error:'.$e->getMessage();
} catch (Exception $e) {
    echo 'Runtime Error:'.$e->getMessage();
}
```


### Show Transaction Detail

```php
use TendoPay\SDK\Exception\TendoPayConnectionException;
use TendoPay\SDK\V2\TendoPayClient;

$client = new TendoPayClient();

try {

    $transaction = $client->getTransactionDetail($transactionNumber);

    // merchant process here
    // $transaction->getMerchantId();
    // $transaction->getMerchantOrderId();
    // $transaction->getAmount();
    // $transaction->getTransactionNumber();
    // $transaction->getCreatedAt();
    // $transaction->getStatus();
    
} catch (TendoPayConnectionException $e) {
    echo 'Connection Error:'.$e->getMessage();
} catch (Exception $e) {
    echo 'Runtime Error:'.$e->getMessage();
}
```
