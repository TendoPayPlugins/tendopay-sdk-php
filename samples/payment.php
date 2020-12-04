<?php

require_once __DIR__.'/common.php';

use TendoPay\SDK\Exception\TendoPayConnectionException;
use TendoPay\SDK\Models\Payment;

### Merchant Transaction Example Start
$merchant_order_id = $_POST['tp_merchant_order_id'];
$request_order_amount = $_POST['tp_amount'];
$request_order_title = $_POST['tp_description'];
$_SESSION['merchant_order_id'] = $merchant_order_id;
$redirectUrl = $_POST['tp_redirect_url'] ?? '';
### Merchant Transaction Example End

global $config;
//echo "<pre>";print_r($config);echo "</pre>";exit;
//echo "<pre>";print_r($_REQUEST);echo "</pre>";exit;

$client = $config['TP_SDK_VERSION'] === 'v2' ?
    new \TendoPay\SDK\V2\TendoPayClient($config) :
    new \TendoPay\SDK\TendoPayClient($config);

try {
    $payment = new Payment();
    $payment->setMerchantOrderId($merchant_order_id)
        ->setDescription($request_order_title)
        ->setRequestAmount($request_order_amount);

    if ($redirectUrl) {
        $payment->setCurrency('PHP')
            ->setRedirectUrl($redirectUrl);
    }

    $client->setPayment($payment);

    $authUrl = $client->getAuthorizeLink();
    header('Location: '.$authUrl);
} catch (TendoPayConnectionException $e) {
    dump('Connection Error:'.$e->getMessage());
} catch (Exception $e) {
    dump('Runtime Error:'.$e->getMessage());
}
