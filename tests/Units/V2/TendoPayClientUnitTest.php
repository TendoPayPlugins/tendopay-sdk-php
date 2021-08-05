<?php declare(strict_types=1);
// @codingStandardsIgnoreLine
namespace Units\V2;

use PHPUnit\Framework\TestCase;
use TendoPay\SDK\Models\Payment;
use TendoPay\SDK\Traits\TpConfigTrait;
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
                    'ERROR_REDIRECT_URL' => 'ENV_ERROR_REDIRECT_URL',
                ],
                'config' => [
                    'CLIENT_ID' => 'CONFIG_CLIENT_ID',
                    'CLIENT_SECRET' => 'CONFIG_CLIENT_SECRET',
                    'SANDBOX_HOST_URL' => 'CONFIG_SANDBOX_HOST_URL',
                    'TENDOPAY_SANDBOX_ENABLED' => true,
                    'ERROR_REDIRECT_URL' => 'CONFIG_ERROR_REDIRECT_URL',
                ],
                'expect' => [
                    'CLIENT_ID' => 'CONFIG_CLIENT_ID',
                    'CLIENT_SECRET' => 'CONFIG_CLIENT_SECRET',
                    'SANDBOX_HOST_URL' => 'CONFIG_SANDBOX_HOST_URL',
                    'ERROR_REDIRECT_URL' => 'CONFIG_ERROR_REDIRECT_URL',
                ],
            ],
            // env should work if config is empty
            [
                'env' => [
                    'CLIENT_ID' => 'ENV_CLIENT_ID',
                    'CLIENT_SECRET' => 'ENV_CLIENT_SECRET',
                    'SANDBOX_HOST_URL' => 'ENV_SANDBOX_HOST_URL',
                    'TENDOPAY_SANDBOX_ENABLED' => true,
                    'ERROR_REDIRECT_URL' => 'ENV_ERROR_REDIRECT_URL',
                ],
                'config' => [],
                'expect' => [
                    'CLIENT_ID' => 'ENV_CLIENT_ID',
                    'CLIENT_SECRET' => 'ENV_CLIENT_SECRET',
                    'SANDBOX_HOST_URL' => 'ENV_SANDBOX_HOST_URL',
                    'ERROR_REDIRECT_URL' => 'ENV_ERROR_REDIRECT_URL',

                ],
            ],
            // Sandbox should be production if sandbox disabled
            [
                'env' => [
                    'CLIENT_ID' => 'ENV_CLIENT_ID',
                    'CLIENT_SECRET' => 'ENV_CLIENT_SECRET',
                    'SANDBOX_HOST_URL' => 'ENV_SANDBOX_HOST_URL',
                    'TENDOPAY_SANDBOX_ENABLED' => false,
                    'ERROR_REDIRECT_URL' => 'ENV_ERROR_REDIRECT_URL',
                ],
                'config' => [],
                'expect' => [
                    'CLIENT_ID' => 'ENV_CLIENT_ID',
                    'CLIENT_SECRET' => 'ENV_CLIENT_SECRET',
                    'SANDBOX_HOST_URL' => 'https://app.tendopay.ph',
                    'ERROR_REDIRECT_URL' => 'ENV_ERROR_REDIRECT_URL',
                ],
            ],
            // Sandbox should be sandbox if sandbox url is not set
            [
                'env' => [
                    'CLIENT_ID' => 'ENV_CLIENT_ID',
                    'CLIENT_SECRET' => 'ENV_CLIENT_SECRET',
                    'TENDOPAY_SANDBOX_ENABLED' => true,
                    'ERROR_REDIRECT_URL' => 'ENV_ERROR_REDIRECT_URL',
                ],
                'config' => [],
                'expect' => [
                    'CLIENT_ID' => 'ENV_CLIENT_ID',
                    'CLIENT_SECRET' => 'ENV_CLIENT_SECRET',
                    'SANDBOX_HOST_URL' => 'https://sandbox.tendopay.ph',
                    'ERROR_REDIRECT_URL' => 'ENV_ERROR_REDIRECT_URL',
                ],
            ],
        ];
    }
}
