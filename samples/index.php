<?php

session_start();

$initCredentials = $_SESSION['credentials'] ?? null;

if (!$initCredentials) {
    if (getenv('MERCHANT_ID')) {
        $initCredentials['merchant_id'] = getenv('MERCHANT_ID');
    }
    if (getenv('MERCHANT_SECRET')) {
        $initCredentials['merchant_secret'] = getenv('MERCHANT_SECRET');
    }
    if (getenv('CLIENT_ID')) {
        $initCredentials['client_id'] = getenv('CLIENT_ID');
    }
    if (getenv('CLIENT_SECRET')) {
        $initCredentials['client_secret'] = getenv('CLIENT_SECRET');
    }
}

require __DIR__.'/index.html.php';
