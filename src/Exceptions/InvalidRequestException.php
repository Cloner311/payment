<?php

namespace Rahabit\Payment\Exceptions;

use Rahabit\Payment\Exceptions\RahabitPaymentException;

class InvalidRequestException extends RahabitPaymentException
{
    public static function notFound()
    {
        return new self('درخواست مورد نظر یافت نشد');
    }

    public static function unProcessableVerify()
    {
        return new self('امکان انجام عملیات تایید بر روی این تراکنش وجود ندارد');
    }
}
