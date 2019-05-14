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
    public function getClientId(): string
    {
        return (string)getenv('CLIENT_ID');
    }

    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        return (string)getenv('CLIENT_SECRET');
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return (string)getenv('MERCHANT_ID');
    }
}
