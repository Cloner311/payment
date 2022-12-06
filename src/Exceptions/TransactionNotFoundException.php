<?php

namespace Rahabit\Payment\Exceptions;

use Rahabit\Payment\Exceptions\RahabitPaymentException;

use Throwable;

class TransactionNotFoundException extends RahabitPaymentException
{
    public function __construct(string $message = 'تراکنش مورد نظر یافت نشد.', int $code = 404, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
