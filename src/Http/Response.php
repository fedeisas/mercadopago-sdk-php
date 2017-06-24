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
        $this->data = (array) json_decode($json, true);
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
            $messagePieces = array_merge($messagePieces, $this->parseCauses($this->get('cause')));
        }

        return join(' - ', $messagePieces);
    }

    /**
     * @param array $cause
     * @return array
     */
    private function parseCauses(array $cause)
    {
        $pieces = [];

        if (is_array($cause) && array_key_exists('code', $cause) && array_key_exists('description', $cause)) {
            $pieces[] = $cause['code'] . ': ' . $cause['description'];
        } elseif (is_array($cause)) {
            foreach ($cause as $causes) {
                if (is_array($causes)) {
                    foreach ($causes as $cause) {
                        $pieces[] = $cause['code'] . ': ' . $cause['description'];
                    }
                } else {
                    $pieces[] = $causes['code'] . ': ' . $causes['description'];
                }
            }
        }

        return $pieces;
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
