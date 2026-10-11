<?php

namespace Orhanerday\OpenAi;

/**
 * The last HTTP response, available from OpenAi::getLastResponse(). It casts to the raw body.
 */
class Response
{
    /** @var int */
    private $status;

    /** @var array<string, string> */
    private $headers;

    /** @var string */
    private $body;

    /**
     * @param  int  $status
     * @param  array<string, string>  $headers  Lower-cased header names
     * @param  string  $body
     */
    public function __construct(int $status, array $headers, string $body)
    {
        $this->status = $status;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function status(): int
    {
        return $this->status;
    }

    /**
     * @return bool True for a 2xx status
     */
    public function ok(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }

    public function body(): string
    {
        return $this->body;
    }

    /**
     * @return array<string, string> Response headers keyed by lower-cased name
     */
    public function headers(): array
    {
        return $this->headers;
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    /**
     * Decode the body as JSON.
     *
     * @param  bool  $assoc  Return an array (true) or stdClass (false)
     * @return mixed
     *
     * @throws \JsonException When the body is not valid JSON
     */
    public function json(bool $assoc = true)
    {
        return json_decode($this->body, $assoc, 512, JSON_THROW_ON_ERROR);
    }

    public function __toString(): string
    {
        return $this->body;
    }
}
