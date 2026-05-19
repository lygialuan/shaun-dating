<?php


namespace Packages\ShaunSocial\Core\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class MessageHttpException extends HttpException
{
    public function __construct(string $message = '', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct(400, $message, $previous, $headers, $code);
    }
}
