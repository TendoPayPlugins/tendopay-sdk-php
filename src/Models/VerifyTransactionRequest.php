<?php

namespace TendoPay\SDK\Models;

use TendoPay\SDK\Constants;
use TendoPay\SDK\V2\ConstantsV2;

class VerifyTransactionRequest
{
    private $disposition;

    private $transactionNumber;

    private $verificationToken;

    private $merchantOrderId;

    private $userId;

    private $hash;

    private $request;

    /**
     * TendoPayCallbackRequest constructor.
     * @param array $request
     *
     * [tendopay_disposition] => success
     * [tendopay_tendo_pay_vendor_id] => 3
     * [tendopay_transaction_number] => 595
     * [tendopay_user_id] => 1217
     * [tendopay_verification_token] => 1da024d3-1839-4867-853d-6fd361fb2629
     * [tendopay_customer_reference_1] => OID_5ce43601535e97_69933308
     * [tendopay_customer_reference_2] => OID_5ce43601535e97_69933308
     * [tendopay_hash] => 0e49771cfa020628dacb5c21c039a7d05e7caed99c530701736d588b7c4521ed
     */
    public function __construct(array $request)
    {
        if ($request['tp_transaction_id'] ?? null) {
            //[tp_transaction_status] => FAILED
            //[tp_transaction_id] => 20412
            //[tp_merchant_order_id] => TEST-ORD-1603907027113
            //[tp_message] => Your available TendoPay credit is ₱1. You do not have the sufficient credits for this purchase. Please make a purchase below this amount.
            //[x_signature] => 77daab16115b25454f4d769ea96de2dcfd22ffb362651cc5274e5635c5a4e924
            $this->request = $request;
            $this->disposition = $request[ConstantsV2::STATUS_PARAM] ?? Constants::STATUS_FAILURE;
            $this->transactionNumber = $request[ConstantsV2::TRANSACTION_NO_PARAM] ?? '';
            $this->verificationToken = $request[ConstantsV2::VERIFICATION_TOKEN_PARAM] ?? '';
            $this->merchantOrderId = $request[ConstantsV2::ORDER_ID_PARAM] ?? '';
            $this->userId = $request[ConstantsV2::USER_ID_PARAM] ?? '';
            $this->hash = $request[ConstantsV2::HASH_PARAM] ?? '';
        } else {
            $this->disposition = $request[Constants::DISPOSITION_PARAM] ?? Constants::STATUS_FAILURE;
            $this->transactionNumber = $request[Constants::TRANSACTION_NO_PARAM] ?? '';
            $this->verificationToken = $request[Constants::VERIFICATION_TOKEN_PARAM] ?? '';
            $this->merchantOrderId = $request[Constants::ORDER_ID_PARAM] ?? '';
            $this->userId = $request[Constants::USER_ID_PARAM] ?? '';
            $this->hash = $request[Constants::HASH_PARAM] ?? '';
        }
    }

    /**
     * @return mixed
     */
    public function getDisposition()
    {
        return $this->disposition;
    }

    /**
     * @return mixed
     */
    public function getTransactionNumber()
    {
        return $this->transactionNumber;
    }

    /**
     * @return mixed
     */
    public function getVerificationToken()
    {
        return $this->verificationToken;
    }

    /**
     * @return mixed
     */
    public function getMerchantOrderId()
    {
        return $this->merchantOrderId;
    }

    /**
     * @return mixed
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * return raw request
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

}
