<?php

namespace Rahabit\Payment\Exceptions;

use Rahabit\Payment\Exceptions\RahabitPaymentException;

use Throwable;

class GatewayNotFoundException extends RahabitPaymentException
{
    public function __construct($message = "درگاه پرداخت انتخابی معتبر نمی‌باشد", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function productionUnavailableGateway(): self
    {
        return new self('امکان استفاده از این درگاه در محیط پروداکشن وجود ندارد');
    }

    public static function defaultGatewayDoesNotSet(): self
    {
        return new self('درگاه پرداختی به صورت پیش فرض انتخاب نشده است');
    }

    public static function detectionProblem(): self
    {
        return new self('امکان تشخیص درگاه پرداخت وجود ندارد');
    }
}
