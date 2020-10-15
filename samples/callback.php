<?php

require_once __DIR__.'/common.php';

use TendoPay\SDK\Exception\TendoPayConnectionException;
use TendoPay\SDK\Models\VerifyTransactionRequest;
use TendoPay\SDK\TendoPayClient;

global $config;
$client = new TendoPayClient($config);

try {
    if (TendoPayClient::isCallBackRequest($_REQUEST)) {
        $merchant_order_id = $_SESSION['merchant_order_id'] ?? null;
        $transaction = $client->verifyTransaction($merchant_order_id, new VerifyTransactionRequest($_REQUEST));

        if (!$transaction->isVerified()) {
            throw new UnexpectedValueException('Invalid signature for the verification');
        }

//        dump('verificationResult:', $transaction->toArray());
        // @Note check request amount with approved amount. it can be different but must be enough to purchase
        // Save $transactionNumber here
        $transactions = $_SESSION['transactions'] ?? [];
        $transactions[] = $transaction->toArray();
        $_SESSION['transactions'] = $transactions;
        header('Location: /');
    }
} catch (TendoPayConnectionException $e) {
    dump('Connection Error:'.$e->getMessage());
} catch (Exception $e) {
    dump('Runtime Error:'.$e->getMessage());
}
