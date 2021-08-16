<?php

namespace TendoPay\SDK\Traits;

trait TpConfigTrait
{
    private static $envConfig;

    protected static function getKeys(): array
    {
        return [
            'TENDOPAY_DEBUG',
            'TENDOPAY_SANDBOX_ENABLED',
            'SANDBOX_HOST_URL',
            'REDIRECT_URL',
            'ERROR_REDIRECT_URL',
            'CLIENT_ID',
            'CLIENT_SECRET',
            'MERCHANT_PERSONAL_ACCESS_TOKEN',
        ];
    }

    /**
     * @param  array  $config
     */
    public static function setEnv(array $config = []): void
    {
        if (empty($config)) {
            foreach (self::getKeys() as $key) {
                self::putEnv($key, getenv($key));
            }
        } else {
            self::$envConfig = $config;
        }
    }

    /**
     * @param  string  $key
     *
     * @return string|null
     */
    public static function getEnv(string $key): ?string
    {
        return self::$envConfig[$key] ?? getenv($key) ?? null;
    }

    /**
     * @param  string  $key
     * @param  mixed  $value
     */
    public static function putEnv(string $key, $value): void
    {
        self::$envConfig[$key] = $value;
    }
}
