<?php

namespace TendoPay\SDK\V2;

use Exception;
use InvalidArgumentException;
use TendoPay\SDK\Constants;
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

    public const STATUS_SUCCESS = ConstantsV2::STATUS_SUCCESS;
    public const STATUS_FAILURE = ConstantsV2::STATUS_FAILURE;

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

//    /**
//     * Redirect URL when the transaction fails
//     * @var string
//     */
//    protected $errorRedirectURL;

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
//        if (isset($this->config['MERCHANT_ID'])) {
//            putenv("MERCHANT_ID=".$this->config['MERCHANT_ID']);
//        }
//        if (isset($this->config['MERCHANT_SECRET'])) {
//            putenv("MERCHANT_SECRET=".$this->config['MERCHANT_SECRET']);
//        }
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
//        $this->errorRedirectURL = $this->config['ERROR_REDIRECT_URL'] ?? (string) getenv('ERROR_REDIRECT_URL', true);
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
                ConstantsV2::get_base_api_url().DIRECTORY_SEPARATOR.$requestURI,
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
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
        ];
        $body = $this->request('POST',
            ConstantsV2::get_bearer_token_endpoint_uri(),
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
     * @deprecated in v2
     */
    protected function getRequestToken($params)
    {
        return $this->request('POST',
            ConstantsV2::get_authorization_endpoint_uri(),
            static::appendV2Hash($params),
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
            ConstantsV2::get_description_endpoint_uri(),
            static::appendV2Hash($params),
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

//    /**
//     * @param  string  $url
//     * @return TendoPayClient
//     */
//    public function setErrorRedirectURL(string $url): self
//    {
//        $this->errorRedirectURL = $url;
//
//        return $this;
//    }

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
            'tp_currency' => $this->payment->getCurrency(),
            ConstantsV2::AMOUNT_PARAM => $this->payment->getRequestAmount(),
            ConstantsV2::ORDER_ID_PARAM => $this->payment->getMerchantOrderId(),
            ConstantsV2::DESC_PARAM => $this->payment->getDescription(),
            ConstantsV2::REDIRECT_URL_PARAM => $this->payment->getRedirectUrl(),
        ];

        $order = $this->createPaymentOrder($params);

        return $order->authorize_url ?? '';
    }

    /**
     * Just validity check with x_signature
     * @return VerifyTransactionResponse
     */
    public function verifyTransaction(): VerifyTransactionResponse
    {
        if (func_num_args() === 2) {
            $request = func_get_arg(1);
        } else {
            $request = func_get_arg(0);
        }

        if (!($request instanceof VerifyTransactionRequest)) {
            throw new InvalidArgumentException('$request should be VerifyTransactionRequest');
        }

        $expected = static::hashForV2($request->getRequest());

        $isHashValid = hash_equals($expected, $request->getHash());

        $status = $request->getDisposition();
        $message = $request->getRequest()['tp_message'] ?? '';

        if (!$isHashValid) {
            $status = ConstantsV2::STATUS_FAILURE;
            $message = 'Invalid signature';
        }

        $data = [
            'is_hash_valid' => $isHashValid,
            Constants::STATUS_PARAM => $status,
            Constants::MESSAGE_PARAM => $message,
            Constants::TRANSACTION_NO_PARAM => $request->getTransactionNumber(),
            Constants::HASH_PARAM => $request->getHash(),
        ];

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
            $request[ConstantsV2::STATUS_PARAM],
            $request[ConstantsV2::TRANSACTION_NO_PARAM]
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
        $params = [
            ConstantsV2::TRANSACTION_NO_PARAM => $transactionNumber,
        ];
        $response = $this->request('POST',
            ConstantsV2::getTransactionDetailEndpointURI($transactionNumber),
            static::appendV2Hash($params),
            $this->getAuthorizationHeader(), true);

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
            ConstantsV2::TRANSACTION_NO_PARAM => $transactionNumber,
        ];

        return $this->request('POST',
            ConstantsV2::get_cancel_payment_endpoint_uri(),
            static::appendV2Hash($params),
            $this->getAuthorizationHeader());
    }

    /**
     * @param $params
     * @return mixed
     * @throws TendoPayConnectionException
     */
    public function createPaymentOrder($params)
    {
        return $this->request('POST',
            ConstantsV2::get_create_payment_order_endpoint_uri(),
            static::appendV2Hash($params),
            $this->getAuthorizationHeader());
    }
}


