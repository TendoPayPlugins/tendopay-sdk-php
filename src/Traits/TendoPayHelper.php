<?php


namespace TendoPay\SDK\Traits;


use TendoPay\SDK\Constants;

trait TendoPayHelper
{

    /**
     * @param array $data
     * @return string
     * @see Hash_Calculator::calculate()
     */
    public static function hash(array $data): string
    {
        $hash_keys_exclusion_list = [Constants::HASH_PARAM];
        $secret = getenv('MERCHANT_SECRET');

        $data = array_map('trim', $data);
        $exclusion_list = $hash_keys_exclusion_list;

        $data = array_filter($data, static function ($value, $key) use ($exclusion_list) {
            return !in_array($key, $exclusion_list, false) && !empty($value);
        }, ARRAY_FILTER_USE_BOTH);

        ksort($data);

        $message = implode('', $data);

        return hash_hmac(Constants::get_hash_algorithm(), $message, $secret);
    }

    /**
     * @param  array  $payload
     * @return string
     */
    public static function hashForV2(array $payload): string
    {
        ksort($payload);
        $message = array_reduce(array_keys($payload), static function ($p, $k) use ($payload) {
            return strpos($k, 'tp_') === 0 ? $p.$k.trim($payload[$k]) : $p;
        }, '');
        return hash_hmac('sha256', $message, static::getClientSecret());
    }

    /**
     * @param array $params
     * @return array
     */
    public static function appendHash(array $params): array
    {
        $hash = static::hash($params);
        return array_merge($params, [
            'tendopay_hash' => $hash
        ]);
    }

    /**
     * @param  array  $params
     * @return array
     */
    public static function appendV2Hash(array $params): array
    {
        $hash = static::hashForV2($params);
        return array_merge($params, [
            'x_signature' => $hash
        ]);
    }

    /**
     * Generate compatible legacy tendopay_description
     * @return string
     */
    public function getTendoPayDescription(): string
    {
        $items = array_map(static function ($item) {
            return $item->toArray();
        }, $this->payment->getItems());

        return json_encode(['items' => $items]);
    }

    /**
     * @return string
     */
    public static function getClientId(): string
    {
        return (string)getenv('CLIENT_ID');
    }

    /**
     * @return string
     */
    public static function getClientSecret(): string
    {
        return (string)getenv('CLIENT_SECRET');
    }

    /**
     * @return string
     */
    public static function getMerchantId(): string
    {
        return (string)getenv('MERCHANT_ID');
    }
}
