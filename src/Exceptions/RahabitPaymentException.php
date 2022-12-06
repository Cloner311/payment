<?php


namespace Rahabit\Payment\Exceptions;

use Exception;
use Throwable;

class RahabitPaymentException extends Exception
{
    public static function unknown(Throwable $previous = null)
    {
        return new self('متاسفانه خطای ناشناخته ای رخ داده است', 500, $previous);
    }
}
