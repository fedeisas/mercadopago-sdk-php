<?php

namespace MercadoPago\Http;

abstract class Client
{
    const API_BASE_URL = 'https://api.mercadopago.com';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';

    /**
     * @param string $uri
     * @param string $method
     * @param array $data
     * @param array $params
     * @return Response
     */
    abstract public function request($uri = '/', $method = 'GET', array $data = [], array $params = []);

    /**
     * @param string $uri
     * @param array $data
     * @param array $params
     * @return Response
     */
    abstract public function get($uri, array $data = [], array $params = []);

    /**
     * @param string $uri
     * @param array $data
     * @param array $params
     * @return Response
     */
    abstract public function post($uri, array $data = [], array $params = []);

    /**
     * @param string $uri
     * @param array $data
     * @param array $params
     * @return Response
     */
    abstract public function put($uri, array $data = [], array $params = []);

    /**
     * @param string $uri
     * @param array $data
     * @param array $params
     * @return Response
     */
    abstract public function delete($uri, array $data = [], array $params = []);
}
