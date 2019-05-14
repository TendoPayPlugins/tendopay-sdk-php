<?php
require __DIR__ . '/../vendor/autoload.php';

function dump()
{
    echo '<pre>';
    array_map('print_r', func_get_args());
    echo '</pre>';
}

use TendoPay\SDK\Exception\TendoPayConnectionException;
use TendoPay\SDK\Models\Payment;
use TendoPay\SDK\Models\VerifyTransactionRequest;
use TendoPay\SDK\TendoPayClient;

### Merchant Transaction Example Start
$merchant_order_id = 'TEST-OID-1234567890';
$request_order_amount = 2000;
$request_order_title = 'Test Order #3';
$item_price = $request_order_amount;
$item_title = 'Item #1';
### Merchant Transaction Example End


$client = new TendoPayClient();
$client->enableSandBox();

try {

    if (TendoPayClient::isCallBackRequest($_REQUEST)) {

        $transaction = $client->verifyTransaction($merchant_order_id, new VerifyTransactionRequest($_REQUEST));

        if ($transaction->isVerified()) {
            dump('verificationResult:', $transaction->toArray());
            // @Note check request amount with approved amount. it can be different but must be enough to purchase
            // Save $transactionNumber here
        }

    } else {
        // Request authorization
        $payment = new Payment();
        $payment->setMerchantOrderId($merchant_order_id)
            ->setDescription($request_order_title)
            ->setRequestAmount($request_order_amount);

        $client->setPayment($payment);

        $redirectURL = $client->getAuthorizeLink();
        header('Location: ' . $redirectURL);
    }
} catch (TendoPayConnectionException $te) {
    echo '<pre>';
    echo 'Connection Error:' . $te->getMessage() . PHP_EOL;
    echo '</pre>';
} catch (Exception $e) {
    echo '<pre>';
    echo 'Unknown Error:' . $e->getMessage() . PHP_EOL;
    echo '</pre>';
}


