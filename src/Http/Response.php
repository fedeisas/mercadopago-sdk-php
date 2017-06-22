<?php

namespace MercadoPago\Http;

use ArrayAccess;

class Response
{
    /**
     * @var int
     */
    private $statusCode = 0;

    /**
     * @var array
     */
    private $data = [];

    /**
     * Response constructor.
     * @param int $statusCode
     * @param string $json
     */
    public function __construct($statusCode, $json = '{}')
    {
        $this->data = (array) gijson_decode($json, true);
        $this->statusCode = (int) $statusCode;
    }

    /**
     * @return null|string
     */
    public function getError()
    {
        if ($this->statusCode < 400) {
            return null;
        }

        $messagePieces = [$this->get('message')];
        if ($this->get('cause')) {
            if ($this->get('cause.code') && $this->get('cause.description')) {
                $messagePieces[] = $this->get('cause.code') . ': ' . $this->get('cause.description');
            } elseif (is_array($this->get('cause'))) {
                $messagePieces = array_merge($messagePieces, array_map(function ($cause) {
                    return $cause['code'] . ': '. $cause['description'];
                }, $this->get('cause')));
            }
        }

        return join(' - ', $messagePieces);
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->statusCode >= 400;
    }

    /**
     * @param $key
     * @param null $default
     * @return array|mixed|null
     */
    public function get($key, $default = null)
    {
        $keys = explode('.', (string)$key);
        $array = &$this->data;
        foreach ($keys as $key) {
            if (!$this->exists($array, $key)) {
                return $default;
            }
            $array = &$array[$key];
        }

        return $array;
    }

    /**
     * @param $array
     * @param $key
     * @return bool
     */
    public function exists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return isset($array[$key]);
        } elseif (!is_array($array)) {
            return false;
        }

        return array_key_exists($key, $array);
    }
}
