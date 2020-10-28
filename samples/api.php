<?php

require_once __DIR__.'/common.php';

global $config;

## Main
$request = json_decode(file_get_contents('php://input'), false);
$job = $request->job ?? null;

try {
    switch ($job) {
        case 'SAVE_CREDENTIALS':
            $_SESSION['credentials'] = $request->credentials ?? null;

            return json();
        case 'GET_CREDENTIALS':
            return json($_SESSION['credentials'] ?? null);
        case 'GET_TRANSACTIONS':
            return json($_SESSION['transactions'] ?? []);
        case 'GET_TRANSACTION':
            $client = ($config['TP_SDK_VERSION'] ?? null) === 'v2' ?
                new \TendoPay\SDK\V2\TendoPayClient($config) :
                new \TendoPay\SDK\TendoPayClient($config);
            $response = $client->getTransactionDetail($request->transactionNumber);

            return json($response->toArray());
        case 'CANCEL_TRANSACTION':
            $transactionNumber = $request->transactionNumber;
            $client = ($config['TP_SDK_VERSION'] ?? null) === 'v2' ?
                new \TendoPay\SDK\V2\TendoPayClient($config) :
                new \TendoPay\SDK\TendoPayClient($config);
            $client->cancelPayment($transactionNumber);

            $_SESSION['transactions'] = array_filter($_SESSION['transactions'] ?? [],
                static function ($transaction) use ($transactionNumber) {
                    return $transaction['transactionNumber'] != $transactionNumber;
                });

            return json($_SESSION['transactions']);
        default:
            // do nothing
            throw new InvalidArgumentException('Invalid parameters', 422);
    }
} catch (\Exception $e) {
    $code = $e->getCode() ?: 500;

    return json(['error' => $e->getMessage()], $code);
}



