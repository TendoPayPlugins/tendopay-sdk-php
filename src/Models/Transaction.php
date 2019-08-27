<?php


namespace TendoPay\SDK\Models;

use TendoPay\SDK\Constants;
use TendoPay\SDK\Exception\TendoPayParameterException;

/**
 * Class Transaction
 * @package TendoPay\SDK\Models
 *
 * #### Data Structure
 * | Field | Data Type | Description |
 * | --- | --- | --- |
 * | merchant_id | Number | Merchant ID |
 * | merchant_order_id | String | Merchant Order ID |
 * | amount | Number | Request Amount |
 * | tendopay_transaction_number | String | Transaction Number |
 * | created_at | ISO8601 Datetime | Datetime of the transaction |
 * | status | String | Status of the transaction |
 */
class Transaction
{
    protected $merchantId;
    protected $merchantOrderId;
    protected $amount;
    protected $transactionNumber;
    protected $createdAt;
    protected $status;

    public function __construct(array $response = [])
    {
        $this->merchantId = $response[Constants::MERCHANT_ID] ?? null;
        $this->merchantOrderId = $response[Constants::MERCHANT_ORDER_ID] ?? null;
        $this->amount = $response[Constants::AMOUNT] ?? null;
        $this->transactionNumber = $response[Constants::TRANSACTION_NO_PARAM] ?? null;
        $this->status = $response[Constants::TRANSACTION_STATUS] ?? null;
        $this->createdAt = $response[Constants::CREATED_AT] ?? null;

        if (!$this->transactionNumber || !$this->merchantOrderId) {
            throw new TendoPayParameterException(sprintf('%s, %s cannot be null',
                Constants::TRANSACTION_NO_PARAM,
                Constants::MERCHANT_ORDER_ID
            ));
        }
    }


    /**
     * @return null
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @return null
     */
    public function getMerchantOrderId()
    {
        return $this->merchantOrderId;
    }

    /**
     * @return null
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return null
     */
    public function getTransactionNumber()
    {
        return $this->transactionNumber;
    }

    /**
     * @return null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return null
     */
    public function getStatus()
    {
        return $this->status;
    }

}
