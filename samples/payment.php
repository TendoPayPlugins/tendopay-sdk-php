<?php

require_once __DIR__.'/common.php';

use TendoPay\SDK\Exception\TendoPayConnectionException;
use TendoPay\SDK\Models\Payment;
use TendoPay\SDK\TendoPayClient;

### Merchant Transaction Example Start
$merchant_order_id = $_POST['tp_merchant_order_id'];
$request_order_amount = $_POST['tp_amount'];
$request_order_title = $_POST['tp_description'];
$_SESSION['merchant_order_id'] = $merchant_order_id;
### Merchant Transaction Example End

global $config;
$client = new TendoPayClient($config);

try {
    $payment = new Payment();
    $payment->setMerchantOrderId($merchant_order_id)
        ->setDescription($request_order_title)
        ->setRequestAmount($request_order_amount);

    $client->setPayment($payment);

    $redirectURL = $client->getAuthorizeLink();
    header('Location: '.$redirectURL);
} catch (TendoPayConnectionException $e) {
    dump('Connection Error:'.$e->getMessage());
} catch (Exception $e) {
    dump('Runtime Error:'.$e->getMessage());
}
