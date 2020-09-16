<?php

namespace TendoPay\SDK;

use Dotenv\Dotenv;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use TendoPay\SDK\Exception\TendoPayConnectionException;
use TendoPay\SDK\Exception\VerifyTransactionException;
use TendoPay\SDK\Models\AccessToken;
use TendoPay\SDK\Models\Payment;
use TendoPay\SDK\Models\PurchaseTransaction;
use TendoPay\SDK\Models\Transaction;
use TendoPay\SDK\Models\VerifyTransactionRequest;
use TendoPay\SDK\Models\VerifyTransactionResponse;
use TendoPay\SDK\Traits\TendoPayHelper;

/**
 *  A sample class
 *
 *  Use this section to define what this class is doing, the PHPDocumentator will use this
 *  to automatically generate an API documentation using this information.
 *
 * @author yourname
 */
class TendoPayClient
{

    use TendoPayHelper;

    private const SRC_PATH = __DIR__ . '/../';
    private const VENDOR_PATH = __DIR__ . '/../../../../vendor';

    public const STATUS_SUCCESS = Constants::STATUS_SUCCESS;
    public const STATUS_FAILURE = Constants::STATUS_FAILURE;

    protected $log;

    /**
     * @var Payment
     */
    protected $payment;

    /**
     * @var AccessToken
     */
    protected $accessToken;

    /**
     * Redirect URL when the transaction succeeds
     * @var string
     */
    protected $redirectURL;

    /**
     * Redirect URL when the transaction fails
     * @var string
     */
    protected $errorRedirectURL;

    /**
     * @var bool
     */
    protected $debug = false;

    public function __construct($options = [])
    {
        $this->initEnvironment();
        $this->setSandBoxMode(false);
        $this->initRedirectURL();
    }

    /**
     * Set Sandbox configuration
     * @param $bool
     */
    protected function setSandBoxMode($bool): void
    {
        putenv('TENDOPAY_SANDBOX_ENABLED=' . $bool);
    }

    /**
     *
     */
    protected function initEnvironment(): void
    {
        $path = file_exists(static::VENDOR_PATH) ? static::VENDOR_PATH . '/../' : static::SRC_PATH;

        $env = Dotenv::create($path);
        $env->load();
        $env->required([
            'MERCHANT_ID',
            'MERCHANT_SECRET',
            'CLIENT_ID',
            'CLIENT_SECRET',
        ]);
    }

    /**
     *
     */
    protected function initRedirectURL(): void
    {
        $this->redirectURL = (string)getenv('REDIRECT_URL', true);
        $this->errorRedirectURL = (string)getenv('ERROR_REDIRECT_URL', true);
    }

    /**
     * @param $method
     * @param $requestURI
     * @param $params
     * @param array $headers
     * @return string
     * @throws TendoPayConnectionException
     */
    protected function request($method, $requestURI, $params, $headers = []): string
    {
        try {
            $http = new Client([
                'base_uri' => Constants::get_base_api_url(),
                'headers' => array_merge([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'X-Using' => 'TendoPay_PHP_SDK_Client/1.0',
                ], $headers),
                'debug' => $this->debug,
            ]);

            $response = $http->request($method, $requestURI, [
                'json' => $params
            ]);

            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            throw new TendoPayConnectionException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param bool $usePersonalAccessToken
     * @return array
     * @throws TendoPayConnectionException
     */
    protected function getAuthorizationHeader($usePersonalAccessToken = false): array
    {
        $accessToken = $usePersonalAccessToken ?
            $this->getPersonalAccessToken() :
            $this->getAccessToken();

        return [
            'Authorization' => 'Bearer ' . $accessToken,
        ];
    }

    /**
     * @return string
     * @throws TendoPayConnectionException
     */
    protected function getAccessToken(): string
    {

        if ($this->accessToken instanceof AccessToken
            && !$this->accessToken->isExpired()) {
            return $this->accessToken->getToken();
        }

        $params = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
        ];
        $body = $this->request('POST',
            Constants::get_bearer_token_endpoint_uri(),
            $params);
        $token = json_decode($body, true);

        $this->accessToken = new AccessToken($token);
        return $this->accessToken->getToken();
    }

    /**
     * Return Personal Access Token
     * @return string
     */
    protected function getPersonalAccessToken(): string
    {
        $token = (string)getenv('MERCHANT_PERSONAL_ACCESS_TOKEN', true);
        if (!$token) {
            throw new InvalidArgumentException('MERCHANT_PERSONAL_ACCESS_TOKEN does not exists');
        }
        return $token;
    }

    /**
     * @param $params
     * @return mixed|string
     * @throws TendoPayConnectionException
     */
    protected function getRequestToken($params)
    {
        $body = $this->request('POST',
            Constants::get_authorization_endpoint_uri(),
            static::appendHash($params),
            $this->getAuthorizationHeader());
        return json_decode($body, false) ?? '';
    }

    /**
     * @param $params
     * @return array|string
     * @throws TendoPayConnectionException
     */
    protected function requestPaymentDescription($params)
    {
        $data = $this->request('POST',
            Constants::get_description_endpoint_uri(),
            static::appendHash($params),
            $this->getAuthorizationHeader());
        return $data;
    }

    /** Public Methods **/

    /**
     * Simple ping to check the server is alive or not
     *
     * @return bool
     */
    public function ping(): bool
    {
        //@TODO check server is alive or throw an Exception
        return true;
//        throw new TendoPayConnectionException('There is some problem to communicate with the server');
    }

    /**
     * @param string $url
     * @return TendoPayClient
     */
    public function setRedirectURL(string $url): self
    {
        $this->redirectURL = $url;
        return $this;
    }

    /**
     * @param string $url
     * @return TendoPayClient
     */
    public function setErrorRedirectURL(string $url): self
    {
        $this->errorRedirectURL = $url;
        return $this;
    }

    /**
     * Enable Sandbox mode
     *
     * @param bool $bool
     * @return TendoPayClient
     */
    public function enableSandBox(bool $bool = true): self
    {
        $this->setSandBoxMode($bool);
        return $this;
    }

    /**
     * @param Payment $payment
     * @return TendoPayClient
     */
    public function setPayment(Payment $payment): self
    {
        $this->payment = $payment;
        return $this;
    }

    /**
     * @return string
     * @throws TendoPayConnectionException
     */
    public function getAuthorizeLink(): string
    {
        $params = [
            Constants::AMOUNT_PARAM => $this->payment->getRequestAmount(),
            Constants::ORDER_ID_PARAM => $this->payment->getMerchantOrderId(),
            Constants::DESC_PARAM => $this->getTendoPayDescription(),
            Constants::VENDOR_ID_PARAM => $this->getMerchantId(),
        ];

        $requestToken = $this->getRequestToken($params);


        $params = array_merge($params, [
            Constants::AUTH_TOKEN_PARAM => $requestToken,
        ]);

        $this->requestPaymentDescription($params);

        $params = array_merge($params, [
            Constants::REDIRECT_URL_PARAM => $this->redirectURL,
            Constants::VENDOR_PARAM => $this->getMerchantId(),
        ]);

        return Constants::get_redirect_uri() . '?' . http_build_query(static::appendHash($params));
    }

    /**
     * @param $orderId
     * @param VerifyTransactionRequest $request
     * @return VerifyTransactionResponse
     * @throws TendoPayConnectionException
     * @throws VerifyTransactionException
     */
    public function verifyTransaction($orderId, VerifyTransactionRequest $request): VerifyTransactionResponse
    {
        if ($orderId !== $request->getMerchantOrderId()) {
            throw new VerifyTransactionException('Invalid orderId');
        }

        $params = [
            Constants::DISPOSITION_PARAM => $request->getDisposition(),
            Constants::TRANSACTION_NO_PARAM => $request->getTransactionNumber(),
            Constants::USER_ID_PARAM => $request->getUserId(),
            Constants::VERIFICATION_TOKEN_PARAM => $request->getVerificationToken(),
            Constants::VENDOR_ID_PARAM => $this->getMerchantId(),
            Constants::ORDER_ID_PARAM => $orderId,
        ];

        $response = $this->request('GET',
            Constants::get_verification_endpoint_uri(),
            static::appendHash($params),
            $this->getAuthorizationHeader());

        $data = json_decode($response, true) ?? [];
        return new VerifyTransactionResponse($data);
    }

    /**
     * Check required fields of the callback request
     * if the given request is a callback request or not
     *
     * @param $request
     * @return bool
     */
    public static function isCallbackRequest($request): bool
    {
        return isset(
            $request[Constants::DISPOSITION_PARAM],
            $request[Constants::TRANSACTION_NO_PARAM],
            $request[Constants::VERIFICATION_TOKEN_PARAM],
            $request[Constants::HASH_PARAM]
        );
    }

    /**
     * Retrieve Transaction Details with the transactionNumber
     * @param $transactionNumber
     * @return Transaction
     * @throws Exception
     */
    public function getTransactionDetail($transactionNumber): Transaction
    {
        $params = [];
        $response = $this->request('GET',
            Constants::getTransactionDetailEndpointURI($transactionNumber),
            $params,
            $this->getAuthorizationHeader(true));

        $transaction = json_decode($response, true);
        return new Transaction($transaction);
    }

    /**
     * Cancel transaction
     * @param $transactionNumber
     * @return mixed|string
     * @throws TendoPayConnectionException
     */
    public function cancelPayment($transactionNumber)
    {
        $params = [
            Constants::TRANSACTION_NO_PARAM => $transactionNumber,
        ];

        $body = $this->request('POST',
            Constants::get_cancel_payment_endpoint_uri(),
            static::appendHash($params),
            $this->getAuthorizationHeader());
        return json_decode($body, false) ?? '';
    }
}


