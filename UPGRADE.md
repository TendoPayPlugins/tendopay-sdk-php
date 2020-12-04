# Upgrade Guide

## # Upgrading to v2 from 0.8.x

### Changes

##### New Client ID/ Client Secret
    Merchant should create a new Client App(REST V2) in the Merchant Dashboard to use SDK 2.x
    Existing credentils is not compatible with SDK 2.x 

##### MERCHANT_ID/MERCHANT_SECRET
    MERCHANT_ID/MERCHANT_SECRET has been removed

##### ERROR_REDIRECT_URL
    ERROR_REDIRECT_URL has been removed. 
    All successful or unsuccessful response will be redirected to REDIRECT_URL
    
##### Backend Notification
    When a merchant creates an app (REST V2), 
    if 'NOTIFICATION_URL' is set, TendoPay notifies some changes of transactions to the notification callback URL.
    This callback is asynchronous back-end API request. 
    'PAID', 'FAILED', 'CANCELLED' events of transactions are triggered.  
              
##### Change the namespace of TendoPayClient
```php
    # TendoPayClient for v1
    use TendoPay\SDK\TendoPayClient;
    
    # TendoPayClient for v2
    use TendoPay\SDK\V2\TendoPayClient;
````
