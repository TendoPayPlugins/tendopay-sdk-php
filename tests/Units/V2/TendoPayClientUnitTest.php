<?php

declare(strict_types=1);
// @codingStandardsIgnoreLine
namespace Units\V2;

use PHPUnit\Framework\TestCase;
use TendoPay\SDK\Traits\TpConfigTrait;
use TendoPay\SDK\Utils\CurlClient;
use TendoPay\SDK\V2\ConstantsV2;
use TendoPay\SDK\V2\TendoPayClient;

/**
 * TendoPayClient Unit Test
 */
class TendoPayClientUnitTest extends TestCase
{
    use TpConfigTrait;

    protected function tearDown(): void
    {
        parent::tearDown();
        // Reset env variables
        foreach (self::getKeys() as $key) {
            putenv($key);
        }

        \Mockery::close();
    }

    /**
     * @param  array  $env
     */
    private function initEnv(array $env = [])
    {
        foreach ($env as $k => $v) {
            putenv(sprintf('%s=%s', $k, $v));
        }
    }

    private function createMockClient(): array
    {
        $client = new TendoPayClient([
            'CLIENT_ID' => 'CONFIG_CLIENT_ID',
            'CLIENT_SECRET' => 'CONFIG_CLIENT_SECRET',
            'SANDBOX_HOST_URL' => 'CONFIG_SANDBOX_HOST_URL',
            'TENDOPAY_SANDBOX_ENABLED' => true,
            'REDIRECT_URL' => 'CONFIG_REDIRECT_URL',
        ]);

        $baseUrl = ConstantsV2::get_base_api_url().DIRECTORY_SEPARATOR;

        $mock = \Mockery::mock(CurlClient::class);

        $mock->shouldReceive('sendRequest')
            ->once()
            ->withSomeOfArgs($baseUrl.ConstantsV2::get_bearer_token_endpoint_uri())
            ->andReturn(json_encode([
                'token_type' => 'Bearer',
                'expires_in' => 604800,
                'access_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJ',
            ]));

        return [
            $client,
            $mock,
            $baseUrl,
        ];
    }


    /**
     * @param  array  $env
     * @param  array  $config
     * @param  array  $expect
     *
     * @dataProvider configDataProvider
     */
    public function testEnvWithConfig(array $env, array $config, array $expect): void
    {
        $this->initEnv($env);
        $client = new TendoPayClient($config);

        $this->assertInstanceOf(\TendoPay\SDK\V2\TendoPayClient::class, $client);

        $baseUrl = ConstantsV2::get_base_api_url();
        $this->assertEquals($expect['SANDBOX_HOST_URL'], $baseUrl);
        $this->assertEquals($expect['CLIENT_ID'], $client->getClientId());
        $this->assertEquals($expect['CLIENT_SECRET'], $client->getClientSecret());
    }

    /**
     * @return array
     */
    public function configDataProvider(): array
    {
        return [
            // config should be higher priority than env
            [
                'env' => [
                    'CLIENT_ID' => 'ENV_CLIENT_ID',
                    'CLIENT_SECRET' => 'ENV_CLIENT_SECRET',
                    'SANDBOX_HOST_URL' => 'ENV_SANDBOX_HOST_URL',
                    'TENDOPAY_SANDBOX_ENABLED' => true,
                    'REDIRECT_URL' => 'ENV_REDIRECT_URL',
                ],
                'config' => [
                    'CLIENT_ID' => 'CONFIG_CLIENT_ID',
                    'CLIENT_SECRET' => 'CONFIG_CLIENT_SECRET',
                    'SANDBOX_HOST_URL' => 'CONFIG_SANDBOX_HOST_URL',
                    'TENDOPAY_SANDBOX_ENABLED' => true,
                    'REDIRECT_URL' => 'CONFIG_REDIRECT_URL',
                ],
                'expect' => [
                    'CLIENT_ID' => 'CONFIG_CLIENT_ID',
                    'CLIENT_SECRET' => 'CONFIG_CLIENT_SECRET',
                    'SANDBOX_HOST_URL' => 'CONFIG_SANDBOX_HOST_URL',
                    'REDIRECT_URL' => 'CONFIG_REDIRECT_URL',
                ],
            ],
            // env should work if config is empty
            [
                'env' => [
                    'CLIENT_ID' => 'ENV_CLIENT_ID',
                    'CLIENT_SECRET' => 'ENV_CLIENT_SECRET',
                    'SANDBOX_HOST_URL' => 'ENV_SANDBOX_HOST_URL',
                    'TENDOPAY_SANDBOX_ENABLED' => true,
                    'REDIRECT_URL' => 'ENV_REDIRECT_URL',
                ],
                'config' => [],
                'expect' => [
                    'CLIENT_ID' => 'ENV_CLIENT_ID',
                    'CLIENT_SECRET' => 'ENV_CLIENT_SECRET',
                    'SANDBOX_HOST_URL' => 'ENV_SANDBOX_HOST_URL',
                    'REDIRECT_URL' => 'ENV_REDIRECT_URL',

                ],
            ],
            // Sandbox should be production if sandbox disabled
            [
                'env' => [
                    'CLIENT_ID' => 'ENV_CLIENT_ID',
                    'CLIENT_SECRET' => 'ENV_CLIENT_SECRET',
                    'SANDBOX_HOST_URL' => 'ENV_SANDBOX_HOST_URL',
                    'TENDOPAY_SANDBOX_ENABLED' => false,
                    'REDIRECT_URL' => 'ENV_REDIRECT_URL',
                ],
                'config' => [],
                'expect' => [
                    'CLIENT_ID' => 'ENV_CLIENT_ID',
                    'CLIENT_SECRET' => 'ENV_CLIENT_SECRET',
                    'SANDBOX_HOST_URL' => 'https://app.tendopay.ph',
                    'REDIRECT_URL' => 'ENV_REDIRECT_URL',
                ],
            ],
            // Sandbox should be sandbox if sandbox url is not set
            [
                'env' => [
                    'CLIENT_ID' => 'ENV_CLIENT_ID',
                    'CLIENT_SECRET' => 'ENV_CLIENT_SECRET',
                    'TENDOPAY_SANDBOX_ENABLED' => true,
                    'REDIRECT_URL' => 'ENV_REDIRECT_URL',
                ],
                'config' => [],
                'expect' => [
                    'CLIENT_ID' => 'ENV_CLIENT_ID',
                    'CLIENT_SECRET' => 'ENV_CLIENT_SECRET',
                    'SANDBOX_HOST_URL' => 'https://sandbox.tendopay.ph',
                    'REDIRECT_URL' => 'ENV_REDIRECT_URL',
                ],
            ],
        ];
    }

    /**
     * @return mixed
     */
    public function testCreatePaymentOrder()
    {
        $params = [
            'tp_currency' => 'PHP',
            'tp_amount' => '1500',
            'tp_merchant_order_id' => 'TEST-ORD-1628175875240',
            'tp_description' => 'Invoice #TEST-ORD-1628175875240',
            'tp_redirect_url' => 'http=>//localhost=>8000/callback.php',
            'x_signature' => 'e5f3b6db87df06c8b9fa7a842a8e1cf8ce694da2eb9c9bb7a941c875eb330837',
            'oauth_client_id' => '923b6886-f3f8-4790-9c90-b12b5d34b880',
        ];

        $expect = (object) [
            'tp_order_token' => '0c9e2d7b-c3bb-4661-ba54-8e0542ecc482',
            'authorize_url' => 'CONFIG_SANDBOX_HOST_URL/payments/authorise/0c9e2d7b-c3bb-4661-ba54-8e0542ecc482',
        ];

        [$client, $mock, $baseUrl] = $this->createMockClient();

        $mock->shouldReceive('sendRequest')
            ->once()
            ->withSomeOfArgs($baseUrl.ConstantsV2::get_create_payment_order_endpoint_uri())
            ->andReturn(json_encode((array) $expect));


        $client->setClient($mock);

        $response = $client->createPaymentOrder($params);

        $this->assertEquals($expect, $response);

        return $response;
    }
}
