<?php

namespace TendoPay\SDK;

/**
 * Configuration class.
 *
 * @package TendoPay\API
 */
class Constants
{

    public const PAYMENT_FAILED_QUERY_PARAM = 'tendopay_payment_failed';

    public const REDIRECT_URL_PATTERN = '^tendopay-result/?';

    public const HASH_ALGORITHM = 'sha256';

    public const BASE_API_URL = 'https://app.tendopay.ph';
    public const SANDBOX_BASE_API_URL = 'https://sandbox.tendopay.ph';

    public const REDIRECT_URI = 'payments/authorise';
    public const VERIFICATION_ENDPOINT_URI = 'payments/api/v1/verification';
    public const AUTHORIZATION_ENDPOINT_URI = 'payments/api/v1/authTokenRequest';
    public const DESCRIPTION_ENDPOINT_URI = 'payments/api/v1/paymentDescription';
    public const BEARER_TOKEN_ENDPOINT_URI = 'oauth/token';
    public const ORDER_STATUS_TRANSITION_ENDPOINT_URI = 'payments/api/v1/orderUpdate';
    public const CANCEL_PAYMENT_ENDPOINT_URI = 'payments/api/v1/cancelPayment';
    /**
     * Gets the transaction detail endpoint uri
     */
    public const TRANSACTION_DETAIL_ENDPOINT_URI = 'merchants/api/v1/transactions/{transactionNumber}';

    public const TENDOPAY_ICON = 'https://s3.ca-central-1.amazonaws.com/candydigital/images/tendopay/tp-icon-32x32.png';
    public const TENDOPAY_FAQ = 'https://tendopay.ph/page-faq.html';


    /**
     * Below public constant names are used as keys of data send to or received from TP API
     */
    public const AMOUNT_PARAM = 'tendopay_amount';
    public const AUTH_TOKEN_PARAM = 'tendopay_authorisation_token';
    public const ORDER_ID_PARAM = 'tendopay_customer_reference_1';
    public const ORDER_KEY_PARAM = 'tendopay_customer_reference_2';
    public const REDIRECT_URL_PARAM = 'tendopay_redirect_url';
    public const VENDOR_ID_PARAM = 'tendopay_tendo_pay_vendor_id';
    public const VENDOR_PARAM = 'tendopay_vendor';
    public const HASH_PARAM = 'tendopay_hash';
    public const DISPOSITION_PARAM = 'tendopay_disposition';
    public const TRANSACTION_NO_PARAM = 'tendopay_transaction_number';
    public const VERIFICATION_TOKEN_PARAM = 'tendopay_verification_token';
    public const DESC_PARAM = 'tendopay_description';
    public const STATUS_PARAM = 'tendopay_status';
    public const USER_ID_PARAM = 'tendopay_user_id';
    public const ORDER_UPDATE_PARAM = 'tendopay_order_update';

    public const MESSAGE_PARAM = 'tendopay_message';

    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILURE = 'failure';

    /**
     * Notification Parameters
     */
    public const TRANSACTION_STATUS = 'status';
    public const NOTIFIED_AT = 'notified_at';
    public const MERCHANT_ID = 'merchant_id';
    public const MERCHANT_ORDER_ID = 'merchant_order_id';
    public const AMOUNT = 'amount';
    public const CREATED_AT = 'created_at';


    /**
     * Purchase Transaction successfully completed
     */
    public const PURCHASE_TRANSACTION_SUCCESS = 'PTOK';

    /**
     * Purchase Transaction not successfully completed
     */
    public const PURCHASE_TRANSACTION_FAILURE = 'PTNG';

    /**
     * Purchase Transaction has canceled
     */
    public const PURCHASE_TRANSACTION_CANCELED = 'PTCA';

    /**
     * Cancel previous purchase transaction successfully completed
     */
    public const CANCEL_TRANSACTION_SUCCESS = 'CTOK';

    /**
     * Below public constants are the keys of description object that is being sent during request to Description Endpoint
     */
    public const ITEMS_DESC_PROPNAME = 'items';
    public const META_DESC_PROPNAME = 'meta';
    public const ORDER_DESC_PROPNAME = 'order';

    /**
     * Gets the hash algorithm.
     *
     * @return string hash algorithm
     */
    public static function get_hash_algorithm(): string
    {
        return self::HASH_ALGORITHM;
    }

    /**
     * Gets the base api URL. It checks whether to use SANDBOX URL or Production URL.
     *
     * @return string the base api url
     */
    public static function get_base_api_url(): string
    {
        if (self::is_sandbox_enabled()) {
            $sandBoxURL = getenv('SANDBOX_HOST_URL', true);
            return $sandBoxURL ?: self::SANDBOX_BASE_API_URL;
        }
        return self::BASE_API_URL;
    }

    /**
     * Gets the redirect uri. It checks whether to use SANDBOX URI or Production URI.
     *
     * @return string redirect uri
     */
    public static function get_redirect_uri(): string
    {
        return self::get_base_api_url() . DIRECTORY_SEPARATOR . self::REDIRECT_URI;
    }

    /**
     * Gets the view uri pattern. It checks whether to use SANDBOX pattern or Production pattern.
     *
     * @return string view uri pattern
     */
    public static function get_view_uri_pattern(): string
    {
        return self::VIEW_URI_PATTERN;
    }

    /**
     * Gets the verification endpoint uri. It checks whether to use SANDBOX URI or Production URI.
     *
     * @return string verification endpoint uri
     */
    public static function get_verification_endpoint_uri(): string
    {
        return self::VERIFICATION_ENDPOINT_URI;
    }

    /**
     * Gets the authorization endpoint uri. It checks whether to use SANDBOX URI or Production URI.
     *
     * @return string authorization endpoint uri
     */
    public static function get_authorization_endpoint_uri(): string
    {
        return self::AUTHORIZATION_ENDPOINT_URI;
    }

    /**
     * Gets the description endpoint uri. It checks whether to use SANDBOX URI or Production URI.
     *
     * @return string description endpoint uri
     */
    public static function get_description_endpoint_uri(): string
    {
        return self::DESCRIPTION_ENDPOINT_URI;
    }

    /**
     * Gets the bearer token endpoint uri. It checks whether to use SANDBOX URI or Production URI.
     *
     * @return string bearer token endpoint uri
     */
    public static function get_bearer_token_endpoint_uri(): string
    {
        return self::BEARER_TOKEN_ENDPOINT_URI;
    }

    /**
     *
     * @return bool true if sandbox is enabled
     */
    private static function is_sandbox_enabled(): bool
    {
        return (bool)getenv('TENDOPAY_SANDBOX_ENABLED', true);
    }

    /**
     * Gets the transaction detail endpoint uri
     * @param string $transactionNumber
     * @return string
     */
    public static function getTransactionDetailEndpointURI($transactionNumber): string
    {
        return str_replace(
            '{transactionNumber}',
            $transactionNumber,
            self::TRANSACTION_DETAIL_ENDPOINT_URI
        );
    }

    /**
     * Get cancel payment uri
     */
    public static function get_cancel_payment_endpoint_uri(): string
    {
        return self::CANCEL_PAYMENT_ENDPOINT_URI;
    }
}
