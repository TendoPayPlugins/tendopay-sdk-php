<?php


namespace TendoPay\SDK\Traits\V2;

use TendoPay\SDK\V2\ConstantsV2;

trait TendoPayHelper
{
    /**
     * @param  array  $payload
     *
     * @return string
     */
    public static function hashForV2(array $payload): string
    {
        ksort($payload);
        $message = array_reduce(array_keys($payload), static function ($p, $k) use ($payload) {
            return strpos($k, 'tp_') === 0 ? $p.$k.trim($payload[$k]) : $p;
        }, '');

        $secret = (string) ConstantsV2::getEnv('CLIENT_SECRET');

        return hash_hmac('sha256', $message, $secret);
    }

    /**
     * @param  array  $params
     *
     * @return array
     */
    public static function appendV2Hash(array $params): array
    {
        $hash = static::hashForV2($params);

        return array_merge($params, [
            'x_signature' => $hash,
        ]);
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return (string) ConstantsV2::getEnv('CLIENT_ID');
    }

    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        return (string) ConstantsV2::getEnv('CLIENT_SECRET');
    }

    /**
     * @param $val
     *
     * @return bool
     */
    public static function toBoolean($val): bool
    {
        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }
}
