<?php


namespace TendoPay\SDK\Traits;


use TendoPay\SDK\Constants;
use TendoPay\SDK\Exception\TendoPayConnectionException;
use TendoPay\SDK\Models\AccessToken;

trait TpHttpHelper
{
    /**
     * @param $method
     * @param $endPointURL
     * @param $data
     * @param $headers
     * @param  false  $debug
     * @return mixed
     */
    protected function requestCurl($method, $endPointURL, $data, $headers, $debug = false)
    {
        $ch = curl_init();

        if (strtoupper($method) == 'GET') {
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
            throw new \UnexpectedValueException($error, $status);
        }

        curl_close($ch);
        return $body;
    }

    /**
     * @param $method
     * @param $requestURI
     * @param $params
     * @param  array  $headers
     * @param  bool  $rawOutput
     * @return mixed
     * @throws TendoPayConnectionException
     */
    protected function request($method, $requestURI, $params, $headers = [], $rawOutput = false)
    {
        try {
            $data = $method === 'GET' ? $params : json_encode($params);
            $response = $this->requestCurl(
                $method,
                Constants::get_base_api_url().DIRECTORY_SEPARATOR.$requestURI,
                $data,
                array_merge([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'X-Using' => 'TendoPay_PHP_SDK_Client/1.0',
                ], $headers), $this->debug);

            return $rawOutput ? $response : json_decode($response, false);
        } catch (\Exception $e) {
            throw new TendoPayConnectionException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param  bool  $usePersonalAccessToken
     * @return array
     * @throws TendoPayConnectionException
     */
    protected function getAuthorizationHeader($usePersonalAccessToken = false): array
    {
        $accessToken = $usePersonalAccessToken ?
            $this->getPersonalAccessToken() :
            $this->getAccessToken();

        return [
            'Authorization' => 'Bearer '.$accessToken,
        ];
    }

    /**
     * @return string
     * @throws TendoPayConnectionException
     */
    protected function getAccessToken(): string
    {
        if ($this->accessToken instanceof AccessToken
            && !$this->accessToken->isExpired()) {
            return $this->accessToken->getToken();
        }

        $params = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
        ];
        $body = $this->request('POST',
            Constants::get_bearer_token_endpoint_uri(),
            $params, [], true);

        $token = json_decode($body, true);
        $this->accessToken = new AccessToken($token);

        return $this->accessToken->getToken();
    }

    /**
     * Return Personal Access Token
     * @return string
     */
    protected function getPersonalAccessToken(): string
    {
        $token = $this->config['MERCHANT_PERSONAL_ACCESS_TOKEN'] ?? (string) getenv('MERCHANT_PERSONAL_ACCESS_TOKEN',
                true);
        if (!$token) {
            throw new \InvalidArgumentException('MERCHANT_PERSONAL_ACCESS_TOKEN does not exists');
        }

        return $token;
    }
}
