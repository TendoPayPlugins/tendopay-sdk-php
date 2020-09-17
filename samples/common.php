<?php
spl_autoload_register(static function ($class) {
    $search = [
        '\\',
        'TendoPay/SDK',
    ];

    $replace = [
        DIRECTORY_SEPARATOR,
        'src',
    ];
    $file = __DIR__ . '/../' . str_replace($search, $replace, $class).'.php';
    if (file_exists($file)) {
        require_once $file;
    } else {
        dump('File not found ', $file);
    }
});
session_start();

function dump()
{
    echo '<pre>';
    array_map('print_r', func_get_args());
    echo '</pre>';
}

$credentials = $_SESSION['credentials'] ?? null;
//putenv('MERCHANT_ID='.$credentials->merchant_id);
//putenv('MERCHANT_SECRET='.$credentials->merchant_secret);
//putenv('CLIENT_ID='.$credentials->client_id);
//putenv('CLIENT_SECRET='.$credentials->client_secret);
//putenv('REDIRECT_URL='.$credentials->redirect_url ?? '');
//putenv('ERROR_REDIRECT_URL='.$credentials->error_redirect_url ?? '');
//putenv('TENDOPAY_SANDBOX_ENABLED=true');

$config = [
    'MERCHANT_ID' => $credentials->merchant_id,
    'MERCHANT_SECRET' => $credentials->merchant_secret,
    'CLIENT_ID' => $credentials->client_id,
    'CLIENT_SECRET' => $credentials->client_secret,
    'REDIRECT_URL' => $credentials->redirect_url ?? '',
    'ERROR_REDIRECT_URL' => $credentials->error_redirect_url ?? '',
    'TENDOPAY_SANDBOX_ENABLED' => true,
    'TENDOPAY_DEBUG' => true,
];

