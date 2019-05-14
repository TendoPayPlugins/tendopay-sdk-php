<?php

namespace TendoPay\SDK\Models;

use TendoPay\SDK\Constants;

class VerifyTransactionResponse
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $transactionNumber;

    /**
     * @var string
     */
    private $hash;

    /**
     * VerifyTransactionResult constructor.
     * @param array $response
     *
     * {
     *     "tendopay_status":"success",
     *     "tendopay_transaction_number":613,
     *     "tendopay_user_id":1217,
     *     "tendopay_message":"Payment Successful",
     *     "tendopay_hash":"7286e8932e34be151d105747d15553047be69ae841fb5375d2816608978f40b7"
     * }
     */
    public function __construct(array $response)
    {
        $this->status = $response[Constants::STATUS_PARAM] ?? 'failure';
        $this->message = $response[Constants::MESSAGE_PARAM] ?? '';
        $this->transactionNumber = $response[Constants::TRANSACTION_NO_PARAM] ?? '';
        $this->hash = $response[Constants::HASH_PARAM] ?? '';
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getTransactionNumber(): string
    {
        return $this->transactionNumber;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->status === Constants::STATUS_SUCCESS;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $out = [];
        foreach (get_class_vars(__CLASS__) as $key => $v) {
            $method = 'get' . ucfirst($key);
            if (method_exists($this, $method)) {
                $out[$key] = $this->$method();
            }
        }
        return $out;
    }
}
