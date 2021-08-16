<?php

namespace TendoPay\SDK\V2;

use Exception;
use InvalidArgumentException;
use TendoPay\SDK\Constants;
use TendoPay\SDK\Contracts\HttpClientInterface;
use TendoPay\SDK\Exception\TendoPayConnectionException;
use TendoPay\SDK\Models\AccessToken;
use TendoPay\SDK\Models\Payment;
use TendoPay\SDK\Models\Transaction;
use TendoPay\SDK\Models\VerifyTransactionRequest;
use TendoPay\SDK\Models\VerifyTransactionResponse;
use TendoPay\SDK\Traits\V2\TendoPayHelper;
use TendoPay\SDK\Utils\CurlClient;

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
     * @var Payment
     */
    protected $payment = null;

    /**
     * @var AccessToken
     */
    protected $accessToken;

    /**
     * Redirect URL when the transaction succeeds
     *
     * @var string
     */
    protected $redirectURL;

    /**
     * @var bool
     */
    protected $debug = false;

    protected $client;

    /**
     * TendoPayClient constructor.
     *
     * @param  array  $config
     */
    public function __construct(array $config = [])
    {
        ConstantsV2::setEnv($config);
        $this->debug = self::toBoolean(ConstantsV2::getEnv('TENDOPAY_DEBUG'));
        $this->setSandBoxMode(self::toBoolean(ConstantsV2::getEnv('TENDOPAY_SANDBOX_ENABLED')));
        $this->initRedirectURL();

        $this->client = new CurlClient();
    }

    /**
     * @param  HttpClientInterface  $client
     */
    public function setClient(HttpClientInterface $client): void
    {
        $this->client = $client;
    }

    /**
     * Set Sandbox configuration
     *
     * @param  bool  $bool
     */
    protected function setSandBoxMode(bool $bool): void
    {
        ConstantsV2::putEnv('TENDOPAY_SANDBOX_ENABLED', $bool);
    }

    /**
     * Set Redirect urls
     */
    protected function initRedirectURL(): void
    {
        $this->redirectURL = ConstantsV2::getEnv('REDIRECT_URL');
    }

    /**
     * @param  string  $method
     * @param  string  $endPointURL
     * @param  array|string|null  $data
     * @param  array|null  $headers
     * @param  false  $debug
     *
     * @return bool|string
     */
    protected function requestCurl(
        string $method,
        string $endPointURL,
        $data,
        ?array $headers,
        bool $debug = false
    ) {
        return $this->client->sendRequest($method, $endPointURL, $data, $headers, $debug);
    }

    /**
     * @param  string  $method
     * @param  string  $requestURI
     * @param  array|null  $params
     * @param  array|null  $headers
     * @param  bool  $rawOutput
     *
     * @return mixed
     * @throws \TendoPay\SDK\Exception\TendoPayConnectionException
     */
    protected function request(
        string $method,
        string $requestURI,
        ?array $params,
        ?array $headers = [],
        bool $rawOutput = false
    ) {
        try {
            $data = $method === 'GET' ? $params : json_encode($params);
            $response = $this->requestCurl(
                $method,
                ConstantsV2::get_base_api_url().DIRECTORY_SEPARATOR.$requestURI,
                $data,
                array_merge(
                    [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'X-Using' => 'TendoPay_PHP_SDK_Client/2.0',
                    ],
                    $headers
                ),
                $this->debug
            );

            return $rawOutput ? $response : json_decode($response, false);
        } catch (\Exception $e) {
            throw new TendoPayConnectionException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param  bool  $usePersonalAccessToken
     *
     * @return array
     * @throws TendoPayConnectionException
     */
    protected function getAuthorizationHeader(bool $usePersonalAccessToken = false): array
    {
        $accessToken = $usePersonalAccessToken
            ? $this->getPersonalAccessToken()
            : $this->getAccessToken();

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
            && ! $this->accessToken->isExpired()
        ) {
            return $this->accessToken->getToken();
        }

        $params = [
            'grant_type' => 'client_credentials',
            'client_id' => static::getClientId(),
            'client_secret' => static::getClientSecret(),
        ];
        $body = $this->request(
            'POST',
            ConstantsV2::get_bearer_token_endpoint_uri(),
            $params,
            [],
            true
        );

        $token = json_decode($body, true);
        $this->accessToken = new AccessToken($token);

        return $this->accessToken->getToken();
    }

    /**
     * Return Personal Access Token
     *
     * @return string
     */
    protected function getPersonalAccessToken(): string
    {
        $token = ConstantsV2::getEnv('MERCHANT_PERSONAL_ACCESS_TOKEN');
        if (! $token) {
            throw new InvalidArgumentException('MERCHANT_PERSONAL_ACCESS_TOKEN does not exists');
        }

        return $token;
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
     *
     * @return TendoPayClient
     */
    public function setRedirectURL(string $url): self
    {
        $this->redirectURL = $url;

        return $this;
    }

    /**
     * Enable Sandbox mode
     *
     * @param  bool  $bool
     *
     * @return TendoPayClient
     */
    public function enableSandBox(bool $bool = true): self
    {
        $this->setSandBoxMode($bool);

        return $this;
    }

    /**
     * @param  Payment  $payment
     *
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
            'tp_meta' => json_encode($this->payment->getMeta()),
        ];

        $order = $this->createPaymentOrder($params);

        return $order->authorize_url ?? '';
    }

    /**
     * Just validity check with x_signature
     *
     * @return VerifyTransactionResponse
     */
    public function verifyTransaction(): VerifyTransactionResponse
    {
        if (func_num_args() === 2) {
            $request = func_get_arg(1);
        } else {
            $request = func_get_arg(0);
        }

        if (! ($request instanceof VerifyTransactionRequest)) {
            throw new InvalidArgumentException(
                '$request should be VerifyTransactionRequest'
            );
        }

        $expected = static::hashForV2($request->getRequest());

        $isHashValid = hash_equals($expected, $request->getHash());

        $status = $request->getDisposition();
        $message = $request->getRequest()['tp_message'] ?? '';

        if (! $isHashValid) {
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
     * @param  array|null  $request
     *
     * @return bool
     */
    public static function isCallbackRequest(?array $request): bool
    {
        return isset(
            $request[ConstantsV2::STATUS_PARAM],
            $request[ConstantsV2::TRANSACTION_NO_PARAM]
        );
    }

    /**
     * Retrieve Transaction Details with the transactionNumber
     *
     * @param  numeric|string  $transactionNumber
     *
     * @return Transaction
     * @throws Exception
     */
    public function getTransactionDetail($transactionNumber): Transaction
    {
        $params = [
            ConstantsV2::TRANSACTION_NO_PARAM => $transactionNumber,
        ];
        $response = $this->request(
            'POST',
            ConstantsV2::getTransactionDetailEndpointURI($transactionNumber),
            static::appendV2Hash($params),
            $this->getAuthorizationHeader(),
            true
        );

        $transaction = json_decode($response, true);

        return new Transaction($transaction);
    }

    /**
     * Cancel transaction
     *
     * @param  numeric|string  $transactionNumber
     *
     * @return mixed|string
     * @throws TendoPayConnectionException
     */
    public function cancelPayment($transactionNumber)
    {
        $params = [
            ConstantsV2::TRANSACTION_NO_PARAM => $transactionNumber,
        ];

        return $this->request(
            'POST',
            ConstantsV2::get_cancel_payment_endpoint_uri(),
            static::appendV2Hash($params),
            $this->getAuthorizationHeader()
        );
    }

    /**
     * @param  array  $params
     *
     * @return mixed
     * @throws TendoPayConnectionException
     */
    public function createPaymentOrder(array $params)
    {
        return $this->request(
            'POST',
            ConstantsV2::get_create_payment_order_endpoint_uri(),
            static::appendV2Hash($params),
            $this->getAuthorizationHeader()
        );
    }
}
