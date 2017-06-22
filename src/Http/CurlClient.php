<?php

namespace MercadoPago\Http;

use InvalidArgumentException;
use MercadoPago\MercadoPago;
use RuntimeException;
use function GuzzleHttp\default_ca_bundle;

class CurlClient extends Client
{
    /**
     * @param string $uri
     * @param string $method
     * @param array $data
     * @param array $params
     * @return Response
     * @throws RuntimeException If curl is not installed.
     * @throws InvalidArgumentException If JSON payload is not valid.
     */
    public function request($uri = '/', $method = 'GET', array $data = [], array $params = [])
    {
        if (!extension_loaded('curl')) {
            throw new RuntimeException(
                'cURL extension not found. You need to enable cURL in your php.ini or another configuration you have.'
            );
        }

        $options = [];
        $headers = [
            'Accept: application/json',
            'User-Agent: MercadoPago PHP SDK v' . MercadoPago::VERSION,
        ];

        if ($method !== self::METHOD_GET && !empty($data)) {
            if (!empty($params['form'])) {
                $options['form_data'] = $data;
                array_push($headers, 'Content-Type: application/x-www-form-urlencoded');
            } else {
                $options['json'] = $data;
                array_push($headers, 'Content-Type: application/json');
            }
        } else {
            array_push($headers, 'Content-Type: application/json');
        }

        if ($method === self::METHOD_GET && !empty($data)) {
            $options['query'] = $data;
        }

        if (!empty($params['access_token'])) {
            $options['query']['access_token'] = $params['access_token'];
        }

        $connect = curl_init();

        curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connect, CURLOPT_SSL_VERIFYPEER, true);

        curl_setopt($connect, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($connect, CURLOPT_HTTPHEADER, $headers);

        if (PHP_VERSION_ID < 50600) {
            curl_setopt($connect, CURLOPT_CAINFO, default_ca_bundle());
        }

        // Set parameters and url
        if (isset($options['query']) && is_array($options['query']) && count($options['query']) > 0) {
            $uri .= (strpos($uri, '?') === false) ? '?' : '&';
            $uri .= http_build_query($options['query'], '', '&');
        }

        curl_setopt($connect, CURLOPT_URL, self::API_BASE_URL . $uri);

        // Set data
        if (isset($options['json'])) {
            $options['json'] = json_encode($options['json']);
            curl_setopt($connect, CURLOPT_POSTFIELDS, $options['json']);

            if (function_exists('json_last_error')) {
                $json_error = json_last_error();
                if ($json_error != JSON_ERROR_NONE) {
                    throw new InvalidArgumentException("JSON Error [{$json_error}] - Data: " . $options['json']);
                }
            }
        } elseif (isset($options['form_data'])) {
            curl_setopt($connect, CURLOPT_POSTFIELDS, http_build_query($options['form_data'], '', '&'));
        }

        $httpContent = curl_exec($connect);
        $httpStatusCode = curl_getinfo($connect, CURLINFO_HTTP_CODE);

        if ($httpContent === false) {
            return new Response(500, '{"message": "Can\'t connect. ' . curl_error($connect) . '"}');
        }

        $response = new Response($httpStatusCode, $httpContent);

        curl_close($connect);

        return $response;
    }

    /**
     * @inheritdoc
     */
    public function get($uri = '/', array $data = [], array $params = [])
    {
        return $this->request($uri, self::METHOD_GET, $data, $params);
    }

    /**
     * @inheritdoc
     */
    public function post($uri = '/', array $data = [], array $params = [])
    {
        return $this->request($uri, self::METHOD_POST, $data, $params);
    }

    /**
     * @inheritdoc
     */
    public function put($uri = '/', array $data = [], array $params = [])
    {
        return $this->request($uri, self::METHOD_PUT, $data, $params);
    }

    /**
     * @inheritdoc
     */
    public function delete($uri = '/', array $data = [], array $params = [])
    {
        return $this->request($uri, self::METHOD_DELETE, $data, $params);
    }
}
