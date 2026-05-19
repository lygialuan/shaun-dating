<?php

namespace Packages\ShaunSocial\AiChatProfiles\Exceptions;

use RuntimeException;
use Throwable;

class AiProviderException extends RuntimeException
{
    public function __construct(
        string $message = '',
        public readonly ?string $providerName = null,
        public readonly ?int $httpStatus = null,
        public readonly bool $retryable = false,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function transport(string $providerName, string $message, ?Throwable $previous = null): self
    {
        return new self($message, $providerName, null, true, $previous);
    }

    public static function http(string $providerName, int $status, string $message, ?Throwable $previous = null): self
    {
        return new self($message, $providerName, $status, $status >= 500 || $status === 429, $previous);
    }

    public static function invalidResponse(string $providerName, string $message, ?Throwable $previous = null): self
    {
        return new self($message, $providerName, null, false, $previous);
    }

    public static function notConfigured(string $providerName, string $message): self
    {
        return new self($message, $providerName, null, false);
    }
}
