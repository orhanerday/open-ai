<?php

namespace Orhanerday\OpenAi;

use Exception;

class OpenAi
{
    private string $chatModel = "gpt-4o-mini";
    private array $headers;
    private array $contentTypes;
    private int $timeout = 0;
    private string $customUrl = "";
    private string $proxy = "";
    private array $curlInfo = [];
    private array $customCurlOptions = [];
    private string $customVersion = "";
    private bool $throwOnError = false;
    private int $maxRetries = 0;
    private int $retryDelayMs = 500;
    private ?Response $lastResponse = null;

    public function __construct($OPENAI_API_KEY = '')
    {
        $this->contentTypes = [
            "application/json" => "Content-Type: application/json",
            "multipart/form-data" => "Content-Type: multipart/form-data",
        ];

        $this->headers = [
            $this->contentTypes["application/json"],
        ];

        if ((string) $OPENAI_API_KEY !== '') {
            $this->setApiKey((string) $OPENAI_API_KEY);
        }
    }

    public function setApiKey(string $apiKey): void
    {
        if (trim($apiKey) === '') {
            throw new \InvalidArgumentException('API key must not be empty.');
        }
        $this->setHeader(['Authorization' => 'Bearer ' . $apiKey]);
    }

    /**
     * @return array
     * Metadata from the most recent completed cURL request.
     */
    public function getCURLInfo()
    {
        return $this->curlInfo;
    }

    /**
     * @param array $options
     * Set custom cURL options to be applied to all requests.
     */
    public function setCURLOptions(array $options)
    {
        $this->customCurlOptions = $options;
    }

    /**
     * @return bool|string
     */
    public function listModels()
    {
        $url = Url::fineTuneModel();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $model
     * @return bool|string
     */
    public function retrieveModel($model)
    {
        $model = "/$model";
        $url = Url::fineTuneModel().$model;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param  array<string, mixed>  $opts  Common keys: model, prompt, n, size, quality, stream
     * @param  callable|null  $stream
     * @return bool|string
     */
    public function image($opts, ?callable $stream = null)
    {
        $url = Url::imageUrl()."/generations";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts, $stream);
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function imageEdit($opts, ?callable $stream = null)
    {
        $url = Url::imageUrl()."/edits";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts, $stream);
    }

    /**
     * @param  array<string, mixed>  $opts  Common keys: model, input
     * @return bool|string
     */
    public function moderation($opts)
    {
        $url = Url::moderationUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param  array<string, mixed>  $opts  Common keys: model, messages (list of role/content arrays),
     *                                      temperature, max_completion_tokens, stream, tools, response_format
     * @param  callable|null  $stream  Required when `stream` is true; receives raw SSE chunks
     * @return bool|string
     * @throws Exception
     */
    public function chat($opts, ?callable $stream = null)
    {
        $opts['model'] = $opts['model'] ?? $this->chatModel;
        $url = Url::chatUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts, $stream);
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function transcribe($opts, ?callable $stream = null)
    {
        $url = Url::transcriptionsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts, $stream);
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function translate($opts)
    {
        $url = Url::translationsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function uploadFile($opts)
    {
        $url = Url::filesUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @return bool|string
     */
    public function listFiles($opts = [])
    {
        $url = Url::filesUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    /**
     * @param $file_id
     * @return bool|string
     */
    public function retrieveFile($file_id)
    {
        $file_id = "/$file_id";
        $url = Url::filesUrl().$file_id;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $file_id
     * @return bool|string
     */
    public function retrieveFileContent($file_id)
    {
        $file_id = "/$file_id/content";
        $url = Url::filesUrl().$file_id;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $file_id
     * @return bool|string
     */
    public function deleteFile($file_id)
    {
        $file_id = "/$file_id";
        $url = Url::filesUrl().$file_id;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function createFineTune($opts)
    {
        $url = Url::fineTuneUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @return bool|string
     */
    public function listFineTunes($opts = [])
    {
        $url = Url::fineTuneUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    /**
     * @param $fine_tune_id
     * @return bool|string
     */
    public function retrieveFineTune($fine_tune_id)
    {
        $fine_tune_id = "/$fine_tune_id";
        $url = Url::fineTuneUrl().$fine_tune_id;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $fine_tune_id
     * @return bool|string
     */
    public function cancelFineTune($fine_tune_id)
    {
        $fine_tune_id = "/$fine_tune_id/cancel";
        $url = Url::fineTuneUrl().$fine_tune_id;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST');
    }

    /**
     * @param $fine_tune_id
     * @return bool|string
     */
    public function listFineTuneEvents($fine_tune_id, $opts = [])
    {
        $fine_tune_id = "/$fine_tune_id/events";
        $url = Url::fineTuneUrl().$fine_tune_id;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    /**
     * @param $fine_tune_id
     * @return bool|string
     */
    public function deleteFineTune($fine_tune_id)
    {
        $fine_tune_id = "/$fine_tune_id";
        $url = Url::fineTuneModel().$fine_tune_id;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    /**
     * @param  array<string, mixed>  $opts  Common keys: model, input (string or list), dimensions, encoding_format
     * @return bool|string
     */
    public function embeddings($opts)
    {
        $opts['model'] = $opts['model'] ?? 'text-embedding-3-small';
        $url = Url::embeddings();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function tts($opts, ?callable $stream = null)
    {
        $url = Url::ttsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts, $stream);
    }

    /**
     * @param  int  $timeout
     */
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @param  string  $proxy
     */
    public function setProxy(string $proxy)
    {
        if ($proxy && strpos($proxy, '://') === false) {
            $proxy = 'https://'.$proxy;
        }
        $this->proxy = $proxy;
    }

    /**
     * @param  string  $customUrl
     * @return void
     * @deprecated Use setBaseURL() instead.
     */
    public function setCustomURL(string $customUrl)
    {
        if ($customUrl != "") {
            $this->customUrl = $customUrl;
        }
    }

    /**
     * @param  string  $customUrl
     * @return void
     */
    public function setBaseURL(string $customUrl)
    {
        if ($customUrl != '') {
            $this->customUrl = $customUrl;
        }
    }

    /**
     * @param string $version
     */
    public function setApiVersion(string $version)
    {
        if ($version != '') {
            $this->customVersion = $version;
        }
    }

    /**
     * @param  array  $header
     * @return void
     */
    public function setHeader(array $header)
    {
        foreach ($header as $key => $value) {
            if (! is_string($value)) {
                throw new \InvalidArgumentException('Header values must be strings.');
            }
            $line = is_string($key) ? $key . ': ' . $value : $value;
            if (strpos($line, ':') === false || preg_match('/[\r\n]/', $line)) {
                throw new \InvalidArgumentException('Headers must be name: value strings or an associative array.');
            }
            $name = strtolower(trim(explode(':', $line, 2)[0]));
            if (! preg_match('/^[!#$%&\'*+.^_`|~0-9a-z-]+$/', $name)) {
                throw new \InvalidArgumentException('Invalid header name.');
            }
            $replaced = false;
            foreach ($this->headers as $index => $existing) {
                if (strtolower(trim(explode(':', $existing, 2)[0])) === $name) {
                    $this->headers[$index] = $line;
                    $replaced = true;

                    break;
                }
            }
            if (! $replaced) {
                $this->headers[] = $line;
            }
        }
    }

    /**
     * Throw an OpenAiException for HTTP error responses (status 400 and above).
     * Disabled by default, so existing code keeps receiving the raw error body.
     *
     * @param  bool  $throw
     * @return void
     */
    public function setThrowOnError(bool $throw = true)
    {
        $this->throwOnError = $throw;
    }

    /**
     * Retry rate-limited (429) and temporary server errors (500, 502, 503, 504) with exponential backoff.
     * Honors Retry-After delays up to 60 seconds; longer waits return the original response.
     * Quota and billing errors are not retried. Server errors are retried only for GET, PUT, and
     * DELETE. Requests with streaming, body callbacks, or custom output destinations are not retried.
     *
     * @param  int  $maxRetries  Retries after the first attempt; 0 disables retrying
     * @param  int  $baseDelayMs  Delay before the first retry; doubled for each further retry
     * @return void
     */
    public function setRetries(int $maxRetries, int $baseDelayMs = 500)
    {
        if ($maxRetries < 0 || $baseDelayMs < 0) {
            throw new \InvalidArgumentException('Retries and delay must not be negative.');
        }
        $this->maxRetries = $maxRetries;
        $this->retryDelayMs = $baseDelayMs;
    }

    /**
     * The most recent HTTP response, with status, headers, and a JSON helper. Null before any response.
     * A transport failure leaves the previous response available. Successful streams have no stored body.
     *
     * @return Response|null
     */
    public function getLastResponse(): ?Response
    {
        return $this->lastResponse;
    }

    /**
     * @param  string  $org
     */
    public function setORG(string $org)
    {
        if ($org != "") {
            $this->setHeader(['OpenAI-Organization' => $org]);
        }
    }

    /**
     * @param  string  $url
     * @param  string  $method
     * @param  array|null  $opts
     * @return bool|string
     */
    private function sendRequest(string $url, string $method, ?array $opts = null, ?callable $stream = null, bool $multipart = false)
    {
        $authorization = '';
        foreach ($this->headers as $header) {
            [$name, $value] = explode(':', $header, 2);
            if (strcasecmp(trim($name), 'Authorization') === 0) {
                $authorization = trim($value);

                break;
            }
        }
        if ($authorization === '' || strcasecmp($authorization, 'Bearer') === 0) {
            throw new Exception('Please provide an API key using the constructor or setApiKey().');
        }

        $hasBody = $opts !== null;
        $opts = $opts ?? [];
        $isStreaming = ! empty($opts['stream']) || ($opts['stream_format'] ?? null) === 'sse';
        if ($isStreaming && $stream === null) {
            throw new Exception(
                'Please provide a stream function. Check https://github.com/orhanerday/open-ai#stream-example for an example.'
            );
        }

        if ($method === 'GET' || ! $hasBody) {
            $query = $this->buildQuery($opts);
            if ($query !== '') {
                $url .= (strpos($url, '?') === false ? '?' : '&') . $query;
            }
            $this->headers[0] = $this->contentTypes['application/json'];
            $post_fields = null;
        } elseif ($multipart || array_key_exists('file', $opts) || array_key_exists('image', $opts)) {
            $this->headers[0] = $this->contentTypes["multipart/form-data"];
            $post_fields = $this->buildMultipart($opts);
        } else {
            $this->headers[0] = $this->contentTypes["application/json"];
            $post_fields = json_encode($opts === [] ? (object) [] : $opts, JSON_THROW_ON_ERROR);
        }
        $curl_info = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $post_fields,
            CURLOPT_HTTPHEADER => $this->headers,
        ];

        if ($post_fields === null || ($method === 'DELETE' && $opts === [])) {
            unset($curl_info[CURLOPT_POSTFIELDS]);
        }

        if (! empty($this->proxy)) {
            $curl_info[CURLOPT_PROXY] = $this->proxy;
        }

        if ($isStreaming) {
            $curl_info[CURLOPT_WRITEFUNCTION] = $stream;
        }

        $curlOptions = array_replace($curl_info, $this->customCurlOptions);
        $retryMethod = $this->retryMethod($curlOptions);
        $headerCallback = $curlOptions[CURLOPT_HEADERFUNCTION] ?? null;
        if ($headerCallback !== null && ! is_callable($headerCallback)) {
            throw new \InvalidArgumentException('The cURL header callback must be callable.');
        }
        $writeCallback = $curlOptions[CURLOPT_WRITEFUNCTION] ?? null;

        $attempt = 0;
        while (true) {
            $responseHeaders = [];
            $responseStatus = 0;
            $errorBody = '';
            $options = $curlOptions;
            $curl = curl_init();

            $options[CURLOPT_HEADERFUNCTION] = function ($handle, $line) use (&$responseHeaders, &$responseStatus, &$errorBody, $headerCallback) {
                if ($this->captureResponseHeader($responseHeaders, $responseStatus, $line)) {
                    $errorBody = '';
                }

                return $headerCallback === null ? strlen($line) : $headerCallback($handle, $line);
            };
            if (is_callable($writeCallback)) {
                $options[CURLOPT_WRITEFUNCTION] = function ($handle, $chunk) use ($writeCallback, &$responseStatus, &$errorBody) {
                    if ($responseStatus >= 400) {
                        $errorBody .= $chunk;
                    }

                    return $writeCallback($handle, $chunk);
                };
            }
            curl_setopt_array($curl, $options);
            $response = curl_exec($curl);

            $info = curl_getinfo($curl);
            $this->curlInfo = $info;
            $error = curl_error($curl);
            $errno = curl_errno($curl);

            if (PHP_VERSION_ID < 80000) {
                curl_close($curl);
            }

            if ($response === false) {
                throw new Exception($error, $errno);
            }

            $status = (int) $info['http_code'];
            $body = is_string($response) ? $response : $errorBody;
            if (! $isStreaming && $retryMethod !== null && $attempt < $this->maxRetries && $this->shouldRetry($status, $retryMethod, $body)) {
                $delay = $this->retryDelayMicroseconds($attempt, $responseHeaders);
                if ($delay !== null) {
                    usleep($delay);
                    $attempt++;

                    continue;
                }
            }

            break;
        }

        $this->lastResponse = new Response($status, $responseHeaders, $body);
        if ($this->throwOnError && $status >= 400) {
            throw new OpenAiException($status, $body);
        }

        return $response;
    }

    private function retryMethod(array $options): ?string
    {
        foreach ([CURLOPT_WRITEFUNCTION, CURLOPT_READFUNCTION, CURLOPT_FILE, CURLOPT_INFILE] as $option) {
            if (array_key_exists($option, $options)) {
                return null;
            }
        }
        if (empty($options[CURLOPT_RETURNTRANSFER]) || ! empty($options[CURLOPT_FAILONERROR]) || ! empty($options[CURLOPT_HEADER]) || ! empty($options[CURLOPT_UPLOAD])) {
            return null;
        }

        $method = $options[CURLOPT_CUSTOMREQUEST] ?? null;

        return is_string($method) && in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'], true) ? $method : null;
    }

    private function captureResponseHeader(array &$headers, int &$status, string $line): bool
    {
        if (preg_match('/^HTTP\/\S+\s+(\d{3})(?:\s|$)/i', $line, $matches)) {
            $headers = [];
            $status = (int) $matches[1];

            return true;
        }
        $parts = explode(':', $line, 2);
        if (count($parts) === 2) {
            $headers[strtolower(trim($parts[0]))] = trim($parts[1]);
        }

        return false;
    }

    private function shouldRetry(int $status, string $method, string $body = ''): bool
    {
        if ($status === 429) {
            $decoded = json_decode($body, true);
            $error = is_array($decoded) ? ($decoded['error'] ?? null) : null;
            if (is_array($error)) {
                foreach (['code', 'type'] as $field) {
                    if (isset($error[$field]) && is_string($error[$field]) && preg_match('/quota|billing|spend_limit|usage_limit/i', $error[$field])) {
                        return false;
                    }
                }
            }

            return true;
        }

        return in_array($status, [500, 502, 503, 504], true) && in_array($method, ['GET', 'PUT', 'DELETE'], true);
    }

    private function retryDelayMicroseconds(int $attempt, array $responseHeaders): ?int
    {
        $retryAfter = trim($responseHeaders['retry-after'] ?? '');
        $seconds = null;
        if ($retryAfter !== '') {
            if (preg_match('/^\d+(?:\.\d+)?$/D', $retryAfter)) {
                $seconds = (float) $retryAfter;
            } else {
                $date = strtotime($retryAfter);
                if ($date !== false) {
                    $seconds = max(0, $date - time());
                }
            }
        }
        if ($seconds !== null) {
            if ($seconds > 60) {
                return null;
            }
            $delay = (int) ceil($seconds * 1000000);
        } else {
            $delay = (int) min($this->retryDelayMs * (2 ** min($attempt, 30)), 60000) * 1000;
        }
        $jitter = min((int) ($delay / 10), 1000000, 60000000 - $delay);

        return $delay + ($jitter > 0 ? random_int(0, $jitter) : 0);
    }

    private function buildMultipart(array $opts): array
    {
        $fields = [];
        $append = function ($key, $value) use (&$append, &$fields) {
            if ($value === null) {
                return;
            }
            if (is_array($value)) {
                foreach ($value as $index => $item) {
                    $append($key . '[' . $index . ']', $item);
                }

                return;
            }
            $fields[$key] = is_bool($value) ? ($value ? 'true' : 'false') : $value;
        };
        foreach ($opts as $key => $value) {
            $append($key, $value);
        }

        return $fields;
    }

    private function buildQuery(array $opts): string
    {
        $parts = [];
        $append = function ($key, $value) use (&$append, &$parts) {
            if ($value === null) {
                return;
            }
            if (is_array($value)) {
                foreach ($value as $index => $item) {
                    $append($key . '[' . (is_int($index) ? '' : $index) . ']', $item);
                }

                return;
            }
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $parts[] = rawurlencode($key) . '=' . rawurlencode((string) $value);
        };
        foreach ($opts as $key => $value) {
            $append($key, $value);
        }

        return implode('&', $parts);
    }

    /**
     * @param  string  $url
     */
    private function baseUrl(string &$url)
    {
        if ($this->customUrl != "") {
            $url = str_replace(Url::ORIGIN, $this->customUrl, $url);
        }
        if ($this->customVersion != "") {
            $url = str_replace('/' . Url::API_VERSION . '/', '/' . $this->customVersion . '/', $url);
        }
    }

    /**
     * Send a request to any API endpoint, including ones without a dedicated method.
     *
     * The path is relative to the configured base URL and API version, for example
     * `/containers`. Authentication, organization headers, setBaseURL(), setApiVersion()
     * and setCURLOptions() all apply. On GET (or when $opts is null) $opts becomes the
     * query string; otherwise it is sent as a JSON body, or as multipart when $multipart
     * is true or it contains a `file` or `image` key.
     *
     * @param  string  $method  GET, POST, PUT, PATCH or DELETE
     * @param  string  $path  Relative path starting with `/`, for example `/evals/{id}`
     * @param  array|null  $opts  Query parameters or request body
     * @param  callable|null  $stream  Required when $opts enables streaming
     * @param  bool  $multipart  Force a multipart/form-data body
     * @return bool|string
     *
     * @throws Exception When the method or path is invalid, or the request fails
     */
    public function request(string $method, string $path, ?array $opts = null, ?callable $stream = null, bool $multipart = false)
    {
        $method = strtoupper($method);
        if (! in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            throw new Exception('Unsupported HTTP method. Use GET, POST, PUT, PATCH or DELETE.');
        }
        if ($path === '' || $path[0] !== '/' || strpos($path, '//') === 0 || strpos($path, '://') !== false) {
            throw new Exception('The path must be relative and start with a single "/", for example "/containers".');
        }

        $url = Url::OPEN_AI_URL . $path;
        $this->baseUrl($url);

        return $this->sendRequest($url, $method, $opts, $stream, $multipart);
    }

    /**
     * Agents API (beta). Agents methods send the `OpenAI-Beta: agents=v1` header for that
     * request only; headers configured with setHeader() are restored afterwards.
     */
    private function agentsRequest(string $url, string $method, ?array $opts = null, ?callable $stream = null, bool $eventStream = false)
    {
        $original = $this->headers;

        try {
            $this->setHeader(['OpenAI-Beta' => 'agents=v1']);
            if ($eventStream) {
                $this->setHeader(['Accept' => 'text/event-stream']);
            }

            return $this->sendRequest($url, $method, $opts, $stream);
        } finally {
            $this->headers = $original;
        }
    }

    public function createAgentSession($opts, ?callable $stream = null)
    {
        $url = Url::agentsUrl() . '/sessions';
        $this->baseUrl($url);

        return $this->agentsRequest($url, 'POST', $opts, $stream);
    }

    public function listAgentSessions($opts = [])
    {
        $url = Url::agentsUrl() . '/sessions';
        $this->baseUrl($url);

        return $this->agentsRequest($url, 'GET', $opts);
    }

    public function retrieveAgentSession($sessionId)
    {
        $url = Url::agentsUrl() . '/sessions/' . rawurlencode($sessionId);
        $this->baseUrl($url);

        return $this->agentsRequest($url, 'GET');
    }

    public function updateAgentSession($sessionId, $opts)
    {
        $url = Url::agentsUrl() . '/sessions/' . rawurlencode($sessionId);
        $this->baseUrl($url);

        return $this->agentsRequest($url, 'POST', $opts);
    }

    public function deleteAgentSession($sessionId)
    {
        $url = Url::agentsUrl() . '/sessions/' . rawurlencode($sessionId);
        $this->baseUrl($url);

        return $this->agentsRequest($url, 'DELETE');
    }

    /**
     * Open the session event stream. The callback receives raw server-sent event chunks and
     * must return the number of bytes it handled.
     */
    public function streamAgentSessionEvents($sessionId, callable $stream, $opts = [])
    {
        $url = Url::agentsUrl() . '/sessions/' . rawurlencode($sessionId) . '/events';
        $this->baseUrl($url);
        $opts['stream'] = true;

        return $this->agentsRequest($url, 'GET', $opts, $stream, true);
    }

    /**
     * Send input events to a session, for example a user message or an approval result.
     *
     * @param  array  $events  List of event objects, sent as {"events": [...]}
     */
    public function sendAgentSessionEvents($sessionId, array $events)
    {
        $url = Url::agentsUrl() . '/sessions/' . rawurlencode($sessionId) . '/events';
        $this->baseUrl($url);

        return $this->agentsRequest($url, 'POST', ['events' => $events]);
    }

    public function listAgentSessionItems($sessionId, $opts = [])
    {
        $url = Url::agentsUrl() . '/sessions/' . rawurlencode($sessionId) . '/items';
        $this->baseUrl($url);

        return $this->agentsRequest($url, 'GET', $opts);
    }

    public function listAgentSessionTurns($sessionId, $opts = [])
    {
        $url = Url::agentsUrl() . '/sessions/' . rawurlencode($sessionId) . '/turns';
        $this->baseUrl($url);

        return $this->agentsRequest($url, 'GET', $opts);
    }

    public function retrieveAgentSessionTurn($sessionId, $turnId)
    {
        $url = Url::agentsUrl() . '/sessions/' . rawurlencode($sessionId) . '/turns/' . rawurlencode($turnId);
        $this->baseUrl($url);

        return $this->agentsRequest($url, 'GET');
    }

    /**
     * Export session traces as OpenTelemetry (OTLP) JSON. Requires trace export for the organization.
     */
    public function exportAgentSessionTraces($sessionId, $opts = [])
    {
        $url = Url::agentsUrl() . '/sessions/' . rawurlencode($sessionId) . '/traces';
        $this->baseUrl($url);

        return $this->agentsRequest($url, 'GET', $opts);
    }

    public function createAgentEnvironment($opts)
    {
        $url = Url::agentsUrl() . '/environments';
        $this->baseUrl($url);

        return $this->agentsRequest($url, 'POST', $opts);
    }

    public function listAgentEnvironments($opts = [])
    {
        $url = Url::agentsUrl() . '/environments';
        $this->baseUrl($url);

        return $this->agentsRequest($url, 'GET', $opts);
    }

    public function retrieveAgentEnvironment($environmentId)
    {
        $url = Url::agentsUrl() . '/environments/' . rawurlencode($environmentId);
        $this->baseUrl($url);

        return $this->agentsRequest($url, 'GET');
    }

    // Responses API

    /**
     * @param  array<string, mixed>  $opts  Common keys: model, input, instructions, tools, previous_response_id,
     *                                      conversation, store, background, stream
     * @param  callable|null  $stream  Required when `stream` is true; receives raw SSE chunks
     * @return bool|string
     *
     * @throws Exception
     */
    public function createResponse($opts, ?callable $stream = null)
    {
        $url = Url::responsesUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts, $stream);
    }

    public function retrieveResponse($responseId, $opts = [], ?callable $stream = null)
    {
        $url = Url::responsesUrl() . '/' . $responseId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts, $stream);
    }

    public function deleteResponse($responseId)
    {
        $url = Url::responsesUrl() . '/' . $responseId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    public function cancelResponse($responseId)
    {
        $url = Url::responsesUrl() . '/' . $responseId . '/cancel';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST');
    }

    public function compactResponse($opts = [])
    {
        $url = Url::responsesUrl() . '/compact';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function countResponseInputTokens($opts)
    {
        $url = Url::responsesUrl() . '/input_tokens';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function listResponseInputItems($responseId, $opts = [])
    {
        $url = Url::responsesUrl() . '/' . $responseId . '/input_items';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    // Conversations API
    public function createConversation($opts = [])
    {
        $url = Url::conversationsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function retrieveConversation($conversationId)
    {
        $url = Url::conversationsUrl() . '/' . $conversationId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    public function updateConversation($conversationId, $opts)
    {
        $url = Url::conversationsUrl() . '/' . $conversationId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function deleteConversation($conversationId)
    {
        $url = Url::conversationsUrl() . '/' . $conversationId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    public function createConversationItem($conversationId, $opts)
    {
        $url = Url::conversationsUrl() . '/' . $conversationId . '/items';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function retrieveConversationItem($conversationId, $itemId, $opts = [])
    {
        $url = Url::conversationsUrl() . '/' . $conversationId . '/items/' . $itemId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function listConversationItems($conversationId, $opts = [])
    {
        $url = Url::conversationsUrl() . '/' . $conversationId . '/items';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function deleteConversationItem($conversationId, $itemId)
    {
        $url = Url::conversationsUrl() . '/' . $conversationId . '/items/' . $itemId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    // Vector Stores API
    public function createVectorStore($opts = [])
    {
        $url = Url::vectorStoresUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function listVectorStores($opts = [])
    {
        $url = Url::vectorStoresUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function retrieveVectorStore($vectorStoreId)
    {
        $url = Url::vectorStoresUrl() . '/' . $vectorStoreId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    public function updateVectorStore($vectorStoreId, $opts = [])
    {
        $url = Url::vectorStoresUrl() . '/' . $vectorStoreId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function deleteVectorStore($vectorStoreId)
    {
        $url = Url::vectorStoresUrl() . '/' . $vectorStoreId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    public function searchVectorStore($vectorStoreId, $opts = [])
    {
        $url = Url::vectorStoresUrl() . '/' . $vectorStoreId . '/search';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function createVectorStoreFile($vectorStoreId, $opts)
    {
        $url = Url::vectorStoresUrl() . '/' . $vectorStoreId . '/files';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function listVectorStoreFiles($vectorStoreId, $opts = [])
    {
        $url = Url::vectorStoresUrl() . '/' . $vectorStoreId . '/files';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function retrieveVectorStoreFile($vectorStoreId, $fileId)
    {
        $url = Url::vectorStoresUrl() . '/' . $vectorStoreId . '/files/' . $fileId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    public function updateVectorStoreFile($vectorStoreId, $fileId, $opts)
    {
        $url = Url::vectorStoresUrl() . '/' . $vectorStoreId . '/files/' . $fileId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function deleteVectorStoreFile($vectorStoreId, $fileId)
    {
        $url = Url::vectorStoresUrl() . '/' . $vectorStoreId . '/files/' . $fileId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    public function retrieveVectorStoreFileContent($vectorStoreId, $fileId, $opts = [])
    {
        $url = Url::vectorStoresUrl() . '/' . $vectorStoreId . '/files/' . $fileId . '/content';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function createVectorStoreFileBatch($vectorStoreId, $opts)
    {
        $url = Url::vectorStoresUrl() . '/' . $vectorStoreId . '/file_batches';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function retrieveVectorStoreFileBatch($vectorStoreId, $batchId)
    {
        $url = Url::vectorStoresUrl() . '/' . $vectorStoreId . '/file_batches/' . $batchId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    public function cancelVectorStoreFileBatch($vectorStoreId, $batchId)
    {
        $url = Url::vectorStoresUrl() . '/' . $vectorStoreId . '/file_batches/' . $batchId . '/cancel';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST');
    }

    public function listVectorStoreFileBatchFiles($vectorStoreId, $batchId, $opts = [])
    {
        $url = Url::vectorStoresUrl() . '/' . $vectorStoreId . '/file_batches/' . $batchId . '/files';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    // Batches API
    public function createBatch($opts)
    {
        $url = Url::batchesUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function retrieveBatch($batchId)
    {
        $url = Url::batchesUrl() . '/' . $batchId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    public function cancelBatch($batchId)
    {
        $url = Url::batchesUrl() . '/' . $batchId . '/cancel';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST');
    }

    public function listBatches($opts = [])
    {
        $url = Url::batchesUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    // Uploads API
    public function createUpload($opts)
    {
        $url = Url::uploadsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function addUploadPart($uploadId, $opts)
    {
        $url = Url::uploadsUrl() . '/' . $uploadId . '/parts';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts, null, true);
    }

    public function completeUpload($uploadId, $opts)
    {
        $url = Url::uploadsUrl() . '/' . $uploadId . '/complete';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function cancelUpload($uploadId)
    {
        $url = Url::uploadsUrl() . '/' . $uploadId . '/cancel';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST');
    }

    // Realtime API
    public function createRealtimeClientSecret($opts)
    {
        $url = Url::realtimeClientSecretsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    // Fine-tuning checkpoints and permissions
    public function createFineTuningCheckpointPermission($checkpointId, $opts)
    {
        $url = Url::fineTuningCheckpointsUrl() . '/' . $checkpointId . '/permissions';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function deleteFineTuningCheckpointPermission($checkpointId, $permissionId)
    {
        $url = Url::fineTuningCheckpointsUrl() . '/' . $checkpointId . '/permissions/' . $permissionId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    public function listFineTuningCheckpoints($jobId, $opts = [])
    {
        $url = Url::fineTuneUrl() . '/' . $jobId . '/checkpoints';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function pauseFineTune($jobId)
    {
        $url = Url::fineTuneUrl() . '/' . rawurlencode($jobId) . '/pause';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST');
    }

    public function resumeFineTune($jobId)
    {
        $url = Url::fineTuneUrl() . '/' . rawurlencode($jobId) . '/resume';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST');
    }

    public function listFineTuningCheckpointPermissions($checkpointId, $opts = [])
    {
        $url = Url::fineTuningCheckpointsUrl() . '/' . rawurlencode($checkpointId) . '/permissions';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function retrieveChatCompletion($completionId, $opts = [])
    {
        $url = Url::chatUrl() . '/' . rawurlencode($completionId);
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function listChatCompletions($opts = [])
    {
        $url = Url::chatUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function updateChatCompletion($completionId, $opts)
    {
        $url = Url::chatUrl() . '/' . rawurlencode($completionId);
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function deleteChatCompletion($completionId)
    {
        $url = Url::chatUrl() . '/' . rawurlencode($completionId);
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    public function listChatCompletionMessages($completionId, $opts = [])
    {
        $url = Url::chatUrl() . '/' . rawurlencode($completionId) . '/messages';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function createDecision($opts)
    {
        $url = Url::decisionsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function createWebhookEndpoint($opts)
    {
        $url = Url::webhookEndpointsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function listWebhookEndpoints($opts = [])
    {
        $url = Url::webhookEndpointsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function retrieveWebhookEndpoint($endpointId)
    {
        $url = Url::webhookEndpointsUrl() . '/' . rawurlencode($endpointId);
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    public function updateWebhookEndpoint($endpointId, $opts)
    {
        $url = Url::webhookEndpointsUrl() . '/' . rawurlencode($endpointId);
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function deleteWebhookEndpoint($endpointId)
    {
        $url = Url::webhookEndpointsUrl() . '/' . rawurlencode($endpointId);
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    public function rotateWebhookEndpointSecret($endpointId, $opts = [])
    {
        $url = Url::webhookEndpointsUrl() . '/' . rawurlencode($endpointId) . '/rotate_secret';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function testWebhookEndpoint($endpointId, $opts)
    {
        $url = Url::webhookEndpointsUrl() . '/' . rawurlencode($endpointId) . '/test';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    // Audio voices, fine-tuning graders, and Realtime REST operations

    public function createVoice($opts)
    {
        $url = Url::voicesUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts, null, array_key_exists('audio_sample', $opts));
    }

    public function createVoiceConsent($opts)
    {
        $url = Url::voiceConsentsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts, null, true);
    }

    public function listVoiceConsents($opts = [])
    {
        $url = Url::voiceConsentsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function retrieveVoiceConsent($consentId)
    {
        $url = Url::voiceConsentsUrl() . '/' . rawurlencode($consentId);
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    public function updateVoiceConsent($consentId, $opts)
    {
        $url = Url::voiceConsentsUrl() . '/' . rawurlencode($consentId);
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function deleteVoiceConsent($consentId)
    {
        $url = Url::voiceConsentsUrl() . '/' . rawurlencode($consentId);
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    public function runFineTuningGrader($opts)
    {
        $url = Url::fineTuningGradersUrl() . '/run';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function validateFineTuningGrader($opts)
    {
        $url = Url::fineTuningGradersUrl() . '/validate';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function createRealtimeTranslationClientSecret($opts)
    {
        $url = Url::realtimeTranslationClientSecretsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function acceptRealtimeCall($callId, $opts)
    {
        $url = Url::realtimeCallsUrl() . '/' . rawurlencode($callId) . '/accept';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function hangupRealtimeCall($callId)
    {
        $url = Url::realtimeCallsUrl() . '/' . rawurlencode($callId) . '/hangup';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST');
    }

    public function referRealtimeCall($callId, $opts)
    {
        $url = Url::realtimeCallsUrl() . '/' . rawurlencode($callId) . '/refer';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function rejectRealtimeCall($callId, $opts = [])
    {
        $url = Url::realtimeCallsUrl() . '/' . rawurlencode($callId) . '/reject';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    public function createRealtimeCall($opts)
    {
        if (isset($opts['session']) && is_array($opts['session'])) {
            $opts['session'] = json_encode($opts['session'], JSON_THROW_ON_ERROR);
        }
        $url = Url::realtimeCallsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts, null, true);
    }

    // Organization Usage and Costs APIs (admin API key required).
    public function getCompletionsUsage($opts)
    {
        $url = Url::organizationUsageUrl() . '/completions';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function getDecisionsUsage($opts)
    {
        $url = Url::organizationUsageUrl() . '/decisions';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function getEmbeddingsUsage($opts)
    {
        $url = Url::organizationUsageUrl() . '/embeddings';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function getModerationsUsage($opts)
    {
        $url = Url::organizationUsageUrl() . '/moderations';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function getImagesUsage($opts)
    {
        $url = Url::organizationUsageUrl() . '/images';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function getAudioSpeechesUsage($opts)
    {
        $url = Url::organizationUsageUrl() . '/audio_speeches';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function getAudioTranscriptionsUsage($opts)
    {
        $url = Url::organizationUsageUrl() . '/audio_transcriptions';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function getVectorStoresUsage($opts)
    {
        $url = Url::organizationUsageUrl() . '/vector_stores';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function getCodeInterpreterSessionsUsage($opts)
    {
        $url = Url::organizationUsageUrl() . '/code_interpreter_sessions';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function getFileSearchCallsUsage($opts)
    {
        $url = Url::organizationUsageUrl() . '/file_search_calls';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function getWebSearchCallsUsage($opts)
    {
        $url = Url::organizationUsageUrl() . '/web_search_calls';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }

    public function getCosts($opts)
    {
        $url = Url::organizationCostsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET', $opts);
    }
}
