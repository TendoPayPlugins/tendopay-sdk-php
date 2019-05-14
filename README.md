# TendoPay SDK for PHP

## Requirements

PHP 7.0 and later.

## Installation

### Using Composer

You can install the sdk via [Composer](http://getcomposer.org/). Run the following command:

```bash
composer require tendopay/tendopay-sdk-php
```

then imports

```php
require_once 'vendor/autoload.php';
```

## Run Sample code

- Copy sample page to in the local
```bash
cp -av vendor/tendopay/tendopay-sdk-php/samples .
```

- Add some environment variables into .env
  > MERCHANT_ID,MERCHANT_SECRET for test can get them at [TendoPay Sandbox](https://sandbox.tendopay.ph)
```bash
## Merchant Credentials
MERCHANT_ID=
MERCHANT_SECRET=

## Client Credentials
CLIENT_ID=
CLIENT_SECRET=

## Redirect URI when the transaction succeed
REDIRECT_URL=https://localhost:8000/purhase.php

## Redirect URI when the transaction fails
ERROR_REDIRECT_URL=https://localhost:8000/purchase.php
```

- Run a sample server
```bash
php -s localhost:8000 -t samples
```
- Open browser and goto
```bash
http://localhost:8000/cart.html
```

## Dependencies
- guzzle
- phpdotenv
