<?php

namespace Arzcode\TickTick\Exceptions;

use Exception;
use Throwable;

class TickTickException extends Exception
{
    protected ?int $statusCode;
    protected ?string $responseBody;

    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null, ?int $statusCode = null, ?string $responseBody = null)
    {
        parent::__construct($message, $code, $previous);

        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
    }

    /**
     * The HTTP status code returned by TickTick, when the failure produced a response.
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * The raw response body returned by TickTick, when available.
     */
    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }
}
