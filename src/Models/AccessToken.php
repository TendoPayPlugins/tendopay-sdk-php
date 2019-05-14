<?php


namespace TendoPay\SDK\Models;


class AccessToken
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $expire;

    /**
     * @var string
     */
    private $token;

    /**
     * AccessToken constructor.
     * @param array $params
     *
     * @example $params = [
     *  "token_type":"Bearer",
     *  "expires_in":604800,
     *  "access_token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJ"
     * ]
     */
    public function __construct(array $params)
    {
        $this->type = $params['token_type'] ?? '';
        $this->expire = time() + $params['expires_in'] ?? time();
        $this->token = $params['access_token'] ?? '';
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return bool
     */
    public function isExpired(): bool {
        return time() >= $this->expire;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
