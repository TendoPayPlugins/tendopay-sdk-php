<?php


namespace TendoPay\SDK\Models;

use Prophecy\Doubler\Generator\Node\ClassNode;
use TendoPay\SDK\Constants;
use TendoPay\SDK\Exception\TendoPayParameterException;

/**
 * "transactionNumber":1184,
 * "status":"PURCHASE_TRANSACTION_CANCELED",
 * "notifiedAt":"2019-08-23T23:21:36+08:00"
 * Class NotifyRequest
 * @package TendoPay\SDK\Models
 */
class NotifyRequest
{
    /**
     * The transaction number that status changed
     * @var
     */
    protected $transactionNumber;

    /**
     * The transaction status
     * @var
     */
    protected $status;

    /**
     * Notification datetime
     * @var
     */
    protected $notifiedAt;

    /**
     * NotifyRequest constructor.
     * @param array $request
     * @throws TendoPayParameterException
     */
    public function __construct(array $request)
    {
        $this->transactionNumber = $request[Constants::TRANSACTION_NO_PARAM] ?? '';
        $this->status = $request[Constants::TRANSACTION_STATUS] ?? '';
        $this->notifiedAt = $request[Constants::NOTIFIED_AT] ?? '';

        if (!$this->transactionNumber ||
            !$this->status ||
            !$this->notifiedAt) {
            throw new TendoPayParameterException(sprintf('%s, %s, %s are required.',
                Constants::TRANSACTION_NO_PARAM,
                Constants::TRANSACTION_STATUS,
                Constants::NOTIFIED_AT
            ));
        }
    }

    /**
     * @return mixed|string
     */
    public function getTransactionNumber()
    {
        return $this->transactionNumber;
    }

    /**
     * @return mixed|string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed|string
     */
    public function getNotifiedAt()
    {
        return $this->notifiedAt;
    }
}
