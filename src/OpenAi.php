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
     * @param $opts
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
     * @param $opts
     * @return bool|string
     */
    public function moderation($opts)
    {
        $url = Url::moderationUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param        $opts
     * @param  callable|null  $stream
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
     * @param $opts
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
            if (! is_string($line) || strpos($line, ':') === false || preg_match('/[\r\n]/', $line)) {
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

        $curl = curl_init();

        curl_setopt_array($curl, $curl_info);
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

        return $response;
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
    }

    // Responses API
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
