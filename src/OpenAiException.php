<?php

namespace Orhanerday\OpenAi;

use Exception;

/**
 * Thrown for HTTP error responses (status 400 and above) when setThrowOnError(true) is enabled.
 */
class OpenAiException extends Exception
{
    /** @var int */
    private $statusCode;

    /** @var string */
    private $responseBody;

    /** @var array|null */
    private $apiError;

    public function __construct(int $statusCode, string $responseBody)
    {
        $decoded = json_decode($responseBody, true);
        $error = is_array($decoded) && isset($decoded['error']) ? $decoded['error'] : null;
        if (is_string($error)) {
            $error = ['message' => $error];
        }

        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
        $this->apiError = is_array($error) ? $error : null;

        $message = is_array($error) && isset($error['message']) && is_string($error['message'])
            ? $error['message']
            : 'The API returned an error response.';

        parent::__construct('HTTP ' . $statusCode . ': ' . $message, $statusCode);
    }

    /**
     * @return int HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return string Raw response body
     */
    public function getResponseBody(): string
    {
        return $this->responseBody;
    }

    /**
     * @return array|null The decoded `error` object (message, type, code, param), or null
     */
    public function getApiError(): ?array
    {
        return $this->apiError;
    }

    /**
     * @return string|null The API error type, for example `invalid_request_error`
     */
    public function getErrorType(): ?string
    {
        return isset($this->apiError['type']) && is_string($this->apiError['type']) ? $this->apiError['type'] : null;
    }

    /**
     * @return string|null The API error code, for example `rate_limit_exceeded`
     */
    public function getErrorCode(): ?string
    {
        return isset($this->apiError['code']) && is_string($this->apiError['code']) ? $this->apiError['code'] : null;
    }
}
