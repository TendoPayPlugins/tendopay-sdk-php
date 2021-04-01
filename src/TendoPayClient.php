<?php

namespace TendoPay\SDK;

use Exception;
use InvalidArgumentException;
use TendoPay\SDK\Exception\TendoPayConnectionException;
use TendoPay\SDK\Exception\VerifyTransactionException;
use TendoPay\SDK\Models\AccessToken;
use TendoPay\SDK\Models\Payment;
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

    public const STATUS_SUCCESS = Constants::STATUS_SUCCESS;
    public const STATUS_FAILURE = Constants::STATUS_FAILURE;

    /**
     * Configuration
     * @var array
     */
    protected $config;

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

    /**
     * TendoPayClient constructor.
     * @param  array  $config
     */
    public function __construct($config = [])
    {
        $this->config = $config;
        $this->debug = $config['TENDOPAY_DEBUG'] ?? getenv('TENDOPAY_DEBUG') ?? false;
        $this->initCredentials();
        $this->setSandBoxMode($config['TENDOPAY_SANDBOX_ENABLED'] ?? getenv('TENDOPAY_SANDBOX_ENABLED', true) ?? false);
        $this->initRedirectURL();
    }

    /**
     * Set Sandbox configuration
     * @param $bool
     */
    protected function setSandBoxMode($bool): void
    {
        putenv('TENDOPAY_SANDBOX_ENABLED='.$bool);
    }

    /**
     * Set credentials
     */
    protected function initCredentials(): void
    {
        if (isset($this->config['MERCHANT_ID'])) {
            putenv("MERCHANT_ID=".$this->config['MERCHANT_ID']);
        }
        if (isset($this->config['MERCHANT_SECRET'])) {
            putenv("MERCHANT_SECRET=".$this->config['MERCHANT_SECRET']);
        }
        if (isset($this->config['CLIENT_ID'])) {
            putenv("CLIENT_ID=".$this->config['CLIENT_ID']);
        }
        if (isset($this->config['CLIENT_SECRET'])) {
            putenv("CLIENT_SECRET=".$this->config['CLIENT_SECRET']);
        }
    }

    /**
     * Set Redirect urls
     */
    protected function initRedirectURL(): void
    {
        $this->redirectURL = $this->config['REDIRECT_URL'] ?? (string) getenv('REDIRECT_URL', true);
        $this->errorRedirectURL = $this->config['ERROR_REDIRECT_URL'] ?? (string) getenv('ERROR_REDIRECT_URL', true);
    }

    /**
     * @param $method
     * @param $endPointURL
     * @param $data
     * @param $headers
     * @param  false  $debug
     * @return mixed
     */
    protected function requestCurl($method, $endPointURL, $data, $headers, $debug = false)
    {
        $ch = curl_init();

        if (strtoupper($method) == 'GET') {
            if ($data) {
                $endPointURL .= '?'.http_build_query($data);
            }
        } else {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $curlHeaders = [];
        foreach ($headers as $k => $v) {
            $curlHeaders[] = "$k: $v";
        }

        curl_setopt($ch, CURLOPT_URL, $endPointURL);
        curl_setopt($ch, CURLOPT_VERBOSE, $debug);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);

        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);


        if ((int) $status >= 400) {
            $response = json_decode($body, false);
            $error = $response->error ?? $response->message ?? curl_error($ch);
            curl_close($ch);
            throw new \UnexpectedValueException($error, $status);
        }

        curl_close($ch);
        return $body;
    }

    /**
     * @param $method
     * @param $requestURI
     * @param $params
     * @param  array  $headers
     * @param  bool  $rawOutput
     * @return mixed
     * @throws TendoPayConnectionException
     */
    protected function request($method, $requestURI, $params, $headers = [], $rawOutput = false)
    {
        try {
            $data = $method === 'GET' ? $params : json_encode($params);
            $response = $this->requestCurl(
                $method,
                Constants::get_base_api_url().DIRECTORY_SEPARATOR.$requestURI,
                $data,
                array_merge([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'X-Using' => 'TendoPay_PHP_SDK_Client/1.0',
                ], $headers), $this->debug);

            return $rawOutput ? $response : json_decode($response, false);
        } catch (\Exception $e) {
            throw new TendoPayConnectionException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param  bool  $usePersonalAccessToken
     * @return array
     * @throws TendoPayConnectionException
     */
    protected function getAuthorizationHeader($usePersonalAccessToken = false): array
    {
        $accessToken = $usePersonalAccessToken ?
            $this->getPersonalAccessToken() :
            $this->getAccessToken();

        return [
            'Authorization' => 'Bearer '.$accessToken,
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
            'client_id' => static::getClientId(),
            'client_secret' => static::getClientSecret(),
        ];
        $body = $this->request('POST',
            Constants::get_bearer_token_endpoint_uri(),
            $params, [], true);

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
        $token = $this->config['MERCHANT_PERSONAL_ACCESS_TOKEN'] ?? (string) getenv('MERCHANT_PERSONAL_ACCESS_TOKEN',
                true);
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
        return $this->request('POST',
            Constants::get_authorization_endpoint_uri(),
            static::appendHash($params),
            $this->getAuthorizationHeader());
    }

    /**
     * @param $params
     * @return array|string
     * @throws TendoPayConnectionException
     */
    protected function requestPaymentDescription($params)
    {
        return $this->request('POST',
            Constants::get_description_endpoint_uri(),
            static::appendHash($params),
            $this->getAuthorizationHeader(),
            true);
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
     * @param  string  $url
     * @return TendoPayClient
     */
    public function setRedirectURL(string $url): self
    {
        $this->redirectURL = $url;

        return $this;
    }

    /**
     * @param  string  $url
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
     * @param  bool  $bool
     * @return TendoPayClient
     */
    public function enableSandBox(bool $bool = true): self
    {
        $this->setSandBoxMode($bool);

        return $this;
    }

    /**
     * @param  Payment  $payment
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
            Constants::VENDOR_ID_PARAM => static::getMerchantId(),
        ];

        $requestToken = $this->getRequestToken($params);

        $params = array_merge($params, [
            Constants::AUTH_TOKEN_PARAM => $requestToken,
        ]);

        $this->requestPaymentDescription($params);

        $params = array_merge($params, [
            Constants::REDIRECT_URL_PARAM => $this->redirectURL,
            Constants::VENDOR_PARAM => static::getMerchantId(),
        ]);

        return Constants::get_redirect_uri().'?'.http_build_query(static::appendHash($params));
    }

    /**
     * @param $orderId
     * @param  VerifyTransactionRequest  $request
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
            Constants::VENDOR_ID_PARAM => static::getMerchantId(),
            Constants::ORDER_ID_PARAM => $orderId,
        ];

        $response = $this->request('GET',
            Constants::get_verification_endpoint_uri(),
            static::appendHash($params),
            $this->getAuthorizationHeader(), true);

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
        $usePersonalAccessToken = false;
        try {
            $usePersonalAccessToken = (bool) $this->getPersonalAccessToken();
        } catch (\InvalidArgumentException $e) {}

        $params = [];
        $response = $this->request('GET',
            Constants::getTransactionDetailEndpointURI($transactionNumber),
            $params,
            $this->getAuthorizationHeader($usePersonalAccessToken), true);

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

        return $this->request('POST',
            Constants::get_cancel_payment_endpoint_uri(),
            static::appendHash($params),
            $this->getAuthorizationHeader());
    }
}


