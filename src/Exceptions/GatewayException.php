<?php


namespace Rahabit\Payment\Exceptions;

use Rahabit\Payment\Exceptions\RahabitPaymentException;

use Exception;
use Throwable;

class GatewayException extends RahabitPaymentException
{
    public function __construct(string $message = 'خطای درگاه پرداخت', int $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param string|null $response
     * @return static
     */
    public static function unknownResponse(string $response = null): self
    {
        return new self(
            'پاسخ ناشناخته!',
            500,
            new Exception($response)
        );
    }

    public static function inconsistentResponse(): self
    {
        return new self('اطلاعات دریافتی با پایگاه‌داده همخوانی ندارند!');
    }

    public static function connectionProblem(Throwable $previous = null): self
    {
        return new self('در اتصال به درگاه پرداخت اشکالی پیش آمده است!', 503, $previous);
    }

    public static function notSupportedMethod(): self
    {
        return new self('درگاه پرداخت از این متد پشتیبانی نمی‌کند!');
    }
}
