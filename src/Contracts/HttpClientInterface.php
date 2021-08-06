<?php

namespace TendoPay\SDK\Contracts;

interface HttpClientInterface
{
    public const GET = 'GET';
    public const POST = 'POST';

    /**
     * @param  string  $method
     * @param  string  $endPointURL
     * @param  array|string|null  $data
     * @param  array|null  $headers
     * @param  bool  $debug
     *
     * @return mixed
     */
    public function sendRequest(
        string $method,
        string $endPointURL,
        $data,
        ?array $headers,
        bool $debug = false
    );
}
