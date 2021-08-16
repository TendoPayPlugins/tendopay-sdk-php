<?php
// @codingStandardsIgnoreLine
namespace Units;

use PHPUnit\Framework\TestCase;
use TendoPay\SDK\Models\VerifyTransactionResponse;

class VerifyTransactionResponseTest extends TestCase
{

    /**
     * @test
     */
    public function toArrayShouldBeArray(): void
    {
        $data = [
            'tendopay_status' => 'success',
            'tendopay_transaction_number' => 613,
            'tendopay_user_id' => 1217,
            'tendopay_message' => 'Payment Successful',
            'tendopay_hash' => '7286e8932e34be151d105747d15553047be69ae841fb5375d2816608978f40b7'
        ];

        $verifyTransactionResponse = new VerifyTransactionResponse($data);

        $this->assertIsArray($verifyTransactionResponse->toArray());
    }
}
