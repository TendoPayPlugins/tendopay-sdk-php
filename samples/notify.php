<?php
require __DIR__ . '/../vendor/autoload.php';

function dump()
{
    echo '<pre>';
    array_map('print_r', func_get_args());
    echo '</pre>';
}

use TendoPay\SDK\Constants;
use TendoPay\SDK\Models\NotifyRequest;
use TendoPay\SDK\TendoPayClient;


$client = new TendoPayClient();
$client->enableSandBox();

try {
    $notifyRequest = new NotifyRequest($_REQUEST);
    $transaction = $client->getTransactionDetail($notifyRequest->getTransactionNumber());

    $merchant_order_id = $transaction->getMerchantOrderId();
    $status = $transaction->getStatus();
    $amount = $transaction->getAmount();

    // dump(compact('merchant_order_id', 'status', 'amount'));
    // Search Merchant side transaction by $transaction->getMerchantOrderId()
    // Check if the transaction is already processed
    // The process should stop here if this transaction is already done.
    // return 200 if this is a duplicated notification


    switch ($status) {
        case Constants::PURCHASE_TRANSACTION_SUCCESS:
            // The transaction is successfully completed
            // Do merchant job here
            break;
        case Constants::PURCHASE_TRANSACTION_FAILURE:
            // The transaction is unsuccessfully completed.
            // Do merchant job here
            break;
        case Constants::CANCEL_TRANSACTION_SUCCESS:
            // the previous transaction is successfully cancelled
            // Do merchant job here
            break;
    }

    // After all merchant side process done, return 200
    http_response_code(200);
} catch (Exception $e) {
    // other wise return error
    dump($e->getMessage());
    http_response_code(500);
}


