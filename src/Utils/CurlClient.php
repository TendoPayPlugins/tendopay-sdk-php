<?php

namespace TendoPay\SDK\Utils;

use TendoPay\SDK\Contracts\HttpClientInterface;
use UnexpectedValueException;

class CurlClient implements HttpClientInterface
{

    /**
     * @param  string  $method
     * @param  string  $endPointURL
     * @param  array|string|null  $data
     * @param  array|null  $headers
     * @param  bool  $debug
     *
     * @return mixed
     */
    public function sendRequest(string $method, string $endPointURL, $data, ?array $headers, bool $debug = false)
    {
        $ch = curl_init();

        if (strtoupper($method) === 'GET') {
            if ($data) {
                $endPointURL .= '?'.http_build_query($data);
            }
        } else {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $curlHeaders = [];
        foreach ($headers as $k => $v) {
            $curlHeaders[] = "$k: $v";
        }

        curl_setopt($ch, CURLOPT_URL, $endPointURL);
        curl_setopt($ch, CURLOPT_VERBOSE, $debug);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);

        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);


        if ((int) $status >= 400) {
            $response = json_decode($body, false);
            $error = $response->error ?? $response->message ?? curl_error($ch);
            curl_close($ch);
            throw new UnexpectedValueException($error, $status);
        }

        curl_close($ch);
        error_log($body);
        return $body;
    }
}
