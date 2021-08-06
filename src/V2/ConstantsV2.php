<?php

namespace TendoPay\SDK\V2;

use TendoPay\SDK\Traits\TpConfigTrait;
use TendoPay\SDK\Traits\V2\TendoPayHelper;

/**
 * Configuration class.
 *
 * @package TendoPay\API
 */
class ConstantsV2
{
    use TpConfigTrait;
    use TendoPayHelper;

    public const PAYMENT_FAILED_QUERY_PARAM = 'tendopay_payment_failed';

    public const REDIRECT_URL_PATTERN = '^tendopay-result/?';

    public const HASH_ALGORITHM = 'sha256';

    public const BASE_API_URL = 'https://app.tendopay.ph';
    public const SANDBOX_BASE_API_URL = 'https://sandbox.tendopay.ph';

    public const REDIRECT_URI = 'payments/authorise';
    public const BEARER_TOKEN_ENDPOINT_URI = 'oauth/token';
    public const CANCEL_PAYMENT_ENDPOINT_URI = 'payments/api/v2/cancelPayment';
    public const CREATE_PAYMENT_ORDER_ENDPOINT_URI = 'payments/api/v2/order';
    /**
     * Gets the transaction detail endpoint uri
     */
    public const TRANSACTION_DETAIL_ENDPOINT_URI = 'payments/api/v2/showTransaction';

    public const TENDOPAY_ICON = 'https://s3.ca-central-1.amazonaws.com/candydigital/images/tendopay/tp-icon-32x32.png';
    public const TENDOPAY_FAQ = 'https://tendopay.ph/page-faq.html';


    /**
     * Below public constant names are used as keys of data send to or received from TP API
     */
    public const AMOUNT_PARAM = 'tp_amount';
    public const ORDER_ID_PARAM = 'tp_merchant_order_id';
    public const REDIRECT_URL_PARAM = 'tp_redirect_url';
    public const HASH_PARAM = 'x_signature';
    public const TRANSACTION_NO_PARAM = 'tp_transaction_id';
    public const VERIFICATION_TOKEN_PARAM = 'x_signature';
    public const DESC_PARAM = 'tp_description';
    public const STATUS_PARAM = 'tp_transaction_status';
    public const USER_ID_PARAM = 'tp_user_id';

    public const MESSAGE_PARAM = 'tendopay_message';

    public const STATUS_SUCCESS = 'PAID';
    public const STATUS_FAILURE = 'FAILED';
    public const STATUS_CANCELED = 'CANCELED';

    /**
     * Notification Parameters
     */
    public const TRANSACTION_STATUS = 'tp_transaction_status';
    public const NOTIFIED_AT = 'notified_at';
    public const MERCHANT_ID = 'tp_merchant_id';
    public const MERCHANT_ORDER_ID = 'tp_merchant_order_id';
    public const AMOUNT = 'tp_amount';
    public const CREATED_AT = 'tp_created_at';


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
     * Below public constants are the keys of description object that is being sent
     * during request to Description Endpoint
     */
    public const ITEMS_DESC_PROPNAME = 'items';
    public const META_DESC_PROPNAME = 'meta';
    public const ORDER_DESC_PROPNAME = 'order';

    // phpcs:disable

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
            $sandBoxURL = self::getEnv('SANDBOX_HOST_URL');

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
        return self::get_base_api_url().DIRECTORY_SEPARATOR.self::REDIRECT_URI;
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
        return self::toBoolean(self::getEnv('TENDOPAY_SANDBOX_ENABLED'));
    }

    /**
     * Get cancel payment uri
     */
    public static function get_cancel_payment_endpoint_uri(): string
    {
        return self::CANCEL_PAYMENT_ENDPOINT_URI;
    }

    public static function get_create_payment_order_endpoint_uri(): string
    {
        return self::CREATE_PAYMENT_ORDER_ENDPOINT_URI;
    }
    // phpcs:enable

    /**
     * Gets the transaction detail endpoint uri
     *
     * @param  string  $transactionNumber
     *
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
}
