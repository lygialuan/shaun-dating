<?php


namespace Packages\ShaunSocial\Core\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class PermissionHttpException extends HttpException
{
    public function __construct(string $message = '', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct(config('shaun_core.core.permission_code'), $message, $previous, $headers, $code);
    }
}
