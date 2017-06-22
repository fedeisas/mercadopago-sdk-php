<?php

namespace MercadoPago\Exceptions;

use Exception;

class MercadoPagoException extends Exception
{
    /**
     * MercadoPagoException constructor.
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message, $code = 500, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
