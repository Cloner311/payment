<?php

namespace Rahabit\Payment\Exceptions;

use Rahabit\Payment\Exceptions\RahabitPaymentException;

use Throwable;

class SucceedRetryException extends RahabitPaymentException
{
    public function __construct($message = "پرداخت موفقیت آمیز بوده و قبلا عملیات تایید تراکنش انجام شده است.", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
