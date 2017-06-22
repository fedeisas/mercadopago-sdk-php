<?php

namespace MercadoPago\Http;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\ClientException;
use MercadoPago\MercadoPago;

class GuzzleClient extends Client
{
    /**
     * @var GuzzleHttpClient
     */
    protected $client;

    /**
     * GuzzleClient constructor.
     */
    public function __construct()
    {
        $this->client = new GuzzleHttpClient([
            'base_uri' => self::API_BASE_URL,
            'headers' => [
                'User-Agent' => 'MercadoPago PHP SDK v' . MercadoPago::VERSION,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function request($uri = '/', $method = 'GET', array $data = [], array $params = [])
    {
        $options = [];

        if ($method !== self::METHOD_GET && !empty($data)) {
            if (empty($params['form'])) {
                $options['json'] = $data;
            } else {
                $options['form_params'] = $data;
            }
        }

        if ($method === self::METHOD_GET && !empty($data)) {
            $options['query'] = $data;
        }

        if (!empty($params['access_token'])) {
            $options['query']['access_token'] = $params['access_token'];
        }

        try {
            $response = $this->client->request($method, $uri, $options);
        } catch (ClientException $exception) {
            $response = $exception->getResponse();
        }

        return new Response($response->getStatusCode(), $response->getBody());
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
