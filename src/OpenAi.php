<?php

namespace Orhanerday\OpenAi;

use Exception;

class OpenAi
{
    private string $engine = "davinci";
    private string $model = "text-davinci-002";
    private string $chatModel = "gpt-3.5-turbo";
    private string $responseModel = "gpt-4.1-mini";
    private string $assistantsBetaVersion = "v1";

    private array $headers;
    private array $contentTypes;
    private int $timeout = 0;
    private object $stream_method;
    private string $customUrl = "";
    private string $proxy = "";
    private array $curlInfo = [];

    public function __construct($OPENAI_API_KEY)
    {
        $this->contentTypes = [
            "application/json" => "Content-Type: application/json",
            "multipart/form-data" => "Content-Type: multipart/form-data",
        ];

        $this->headers = [
            $this->contentTypes["application/json"],
            "Authorization: Bearer $OPENAI_API_KEY",
        ];
    }

    /**
     * @return array
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
     * @param string $model
     * @return bool|string
     */
    public function retrieveModel($model)
    {
        $url = Url::fineTuneModel() . "/" . $model;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param array $opts
     * @return bool|string
     */
    public function complete($opts)
    {
        $engine = $opts['engine'] ?? $this->engine;
        $url = Url::completionURL($engine);

        unset($opts['engine']);

        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param array $opts
     * @param callable|null $stream
     * @return bool|string
     * @throws Exception
     */
    public function completion($opts, $stream = null)
    {
        if (array_key_exists('stream', $opts) && $opts['stream']) {
            if ($stream === null) {
                throw new Exception(
                    'Please provide a stream function. Check https://github.com/orhanerday/open-ai#stream-example for an example.'
                );
            }

            $this->stream_method = $stream;
        }

        $opts['model'] = $opts['model'] ?? $this->model;

        $url = Url::completionsURL();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * Create a response using the Responses API.
     *
     * For background processing, provide:
     *
     * [
     *     'background' => true
     * ]
     *
     * For streaming, provide:
     *
     * [
     *     'stream' => true
     * ]
     *
     * @param array $opts
     * @param callable|null $stream
     * @return bool|string
     * @throws Exception
     */
    public function responses($opts, $stream = null)
    {
        if (array_key_exists('stream', $opts) && $opts['stream']) {
            if ($stream === null) {
                throw new Exception(
                    'Please provide a stream function when stream is enabled.'
                );
            }

            $this->stream_method = $stream;
        }

        $opts['model'] = $opts['model'] ?? $this->responseModel;

        $url = Url::responsesUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * Retrieve an existing response.
     *
     * This can be used to poll a background response.
     *
     * @param string $responseId
     * @return bool|string
     */
    public function retrieveResponse(string $responseId)
    {
        $url = Url::responseUrl($responseId);
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * Cancel a background response.
     *
     * @param string $responseId
     * @return bool|string
     */
    public function cancelResponse(string $responseId)
    {
        $url = Url::cancelResponseUrl($responseId);
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST');
    }

    /**
     * @param array $opts
     * @return bool|string
     */
    public function createEdit($opts)
    {
        $url = Url::editsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param array $opts
     * @return bool|string
     */
    public function image($opts)
    {
        $url = Url::imageUrl() . "/generations";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param array $opts
     * @return bool|string
     */
    public function imageEdit($opts)
    {
        $url = Url::imageUrl() . "/edits";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param array $opts
     * @return bool|string
     */
    public function createImageVariation($opts)
    {
        $url = Url::imageUrl() . "/variations";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param array $opts
     * @return bool|string
     */
    public function search($opts)
    {
        $engine = $opts['engine'] ?? $this->engine;
        $url = Url::searchURL($engine);

        unset($opts['engine']);

        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param array $opts
     * @return bool|string
     */
    public function answer($opts)
    {
        $url = Url::answersUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param array $opts
     * @return bool|string
     */
    public function classification($opts)
    {
        $url = Url::classificationsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param array $opts
     * @return bool|string
     */
    public function moderation($opts)
    {
        $url = Url::moderationUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param array $opts
     * @param callable|null $stream
     * @return bool|string
     * @throws Exception
     */
    public function chat($opts, $stream = null)
    {
        if (array_key_exists('stream', $opts) && $opts['stream']) {
            if ($stream === null) {
                throw new Exception(
                    'Please provide a stream function. Check https://github.com/orhanerday/open-ai#stream-example for an example.'
                );
            }

            $this->stream_method = $stream;
        }

        $opts['model'] = $opts['model'] ?? $this->chatModel;

        $url = Url::chatUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param array $opts
     * @return bool|string
     */
    public function transcribe($opts)
    {
        $url = Url::transcriptionsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param array $opts
     * @return bool|string
     */
    public function translate($opts)
    {
        $url = Url::translationsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param array $opts
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
    public function listFiles()
    {
        $url = Url::filesUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $fileId
     * @return bool|string
     */
    public function retrieveFile($fileId)
    {
        $url = Url::filesUrl() . "/" . $fileId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $fileId
     * @return bool|string
     */
    public function retrieveFileContent($fileId)
    {
        $url = Url::filesUrl() . "/" . $fileId . "/content";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $fileId
     * @return bool|string
     */
    public function deleteFile($fileId)
    {
        $url = Url::filesUrl() . "/" . $fileId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    /**
     * @param array $opts
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
    public function listFineTunes()
    {
        $url = Url::fineTuneUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $fineTuneId
     * @return bool|string
     */
    public function retrieveFineTune($fineTuneId)
    {
        $url = Url::fineTuneUrl() . "/" . $fineTuneId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $fineTuneId
     * @return bool|string
     */
    public function cancelFineTune($fineTuneId)
    {
        $url = Url::fineTuneUrl() . "/" . $fineTuneId . "/cancel";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST');
    }

    /**
     * @param string $fineTuneId
     * @return bool|string
     */
    public function listFineTuneEvents($fineTuneId)
    {
        $url = Url::fineTuneUrl() . "/" . $fineTuneId . "/events";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $fineTuneId
     * @return bool|string
     */
    public function deleteFineTune($fineTuneId)
    {
        $url = Url::fineTuneModel() . "/" . $fineTuneId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    /**
     * @return bool|string
     */
    public function engines()
    {
        $url = Url::enginesUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $engine
     * @return bool|string
     */
    public function engine($engine)
    {
        $url = Url::engineUrl($engine);
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param array $opts
     * @return bool|string
     */
    public function embeddings($opts)
    {
        $url = Url::embeddings();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param array $data
     * @return bool|string
     */
    public function createAssistant($data)
    {
        $data['model'] = $data['model'] ?? $this->chatModel;

        $this->addAssistantsBetaHeader();

        $url = Url::assistantsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param string $assistantId
     * @return bool|string
     */
    public function retrieveAssistant($assistantId)
    {
        $this->addAssistantsBetaHeader();

        $url = Url::assistantsUrl() . '/' . $assistantId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $assistantId
     * @param array $data
     * @return bool|string
     */
    public function modifyAssistant($assistantId, $data)
    {
        $this->addAssistantsBetaHeader();

        $url = Url::assistantsUrl() . '/' . $assistantId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param string $assistantId
     * @return bool|string
     */
    public function deleteAssistant($assistantId)
    {
        $this->addAssistantsBetaHeader();

        $url = Url::assistantsUrl() . '/' . $assistantId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    /**
     * @param array $query
     * @return bool|string
     */
    public function listAssistants($query = [])
    {
        $this->addAssistantsBetaHeader();

        $url = Url::assistantsUrl();

        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }

        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $assistantId
     * @param string $fileId
     * @return bool|string
     */
    public function createAssistantFile($assistantId, $fileId)
    {
        $this->addAssistantsBetaHeader();

        $url = Url::assistantsUrl() . '/' . $assistantId . '/files';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', [
            'file_id' => $fileId,
        ]);
    }

    /**
     * @param string $assistantId
     * @param string $fileId
     * @return bool|string
     */
    public function retrieveAssistantFile($assistantId, $fileId)
    {
        $this->addAssistantsBetaHeader();

        $url = Url::assistantsUrl() . '/' . $assistantId . '/files/' . $fileId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $assistantId
     * @param array $query
     * @return bool|string
     */
    public function listAssistantFiles($assistantId, $query = [])
    {
        $this->addAssistantsBetaHeader();

        $url = Url::assistantsUrl() . '/' . $assistantId . '/files';

        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }

        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $assistantId
     * @param string $fileId
     * @return bool|string
     */
    public function deleteAssistantFile($assistantId, $fileId)
    {
        $this->addAssistantsBetaHeader();

        $url = Url::assistantsUrl() . '/' . $assistantId . '/files/' . $fileId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    /**
     * @param array $data
     * @return bool|string
     */
    public function createThread($data = [])
    {
        $this->addAssistantsBetaHeader();

        $url = Url::threadsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param string $threadId
     * @return bool|string
     */
    public function retrieveThread($threadId)
    {
        $this->addAssistantsBetaHeader();

        $url = Url::threadsUrl() . '/' . $threadId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $threadId
     * @param array $data
     * @return bool|string
     */
    public function modifyThread($threadId, $data)
    {
        $this->addAssistantsBetaHeader();

        $url = Url::threadsUrl() . '/' . $threadId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param string $threadId
     * @return bool|string
     */
    public function deleteThread($threadId)
    {
        $this->addAssistantsBetaHeader();

        $url = Url::threadsUrl() . '/' . $threadId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    /**
     * @param string $threadId
     * @param array $data
     * @return bool|string
     */
    public function createThreadMessage($threadId, $data)
    {
        $this->addAssistantsBetaHeader();

        $url = Url::threadsUrl() . '/' . $threadId . '/messages';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param string $threadId
     * @param string $messageId
     * @return bool|string
     */
    public function retrieveThreadMessage($threadId, $messageId)
    {
        $this->addAssistantsBetaHeader();

        $url = Url::threadsUrl() . '/' . $threadId . '/messages/' . $messageId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $threadId
     * @param string $messageId
     * @param array $data
     * @return bool|string
     */
    public function modifyThreadMessage($threadId, $messageId, $data)
    {
        $this->addAssistantsBetaHeader();

        $url = Url::threadsUrl() . '/' . $threadId . '/messages/' . $messageId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param string $threadId
     * @param array $query
     * @return bool|string
     */
    public function listThreadMessages($threadId, $query = [])
    {
        $this->addAssistantsBetaHeader();

        $url = Url::threadsUrl() . '/' . $threadId . '/messages';

        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }

        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $threadId
     * @param string $messageId
     * @param string $fileId
     * @return bool|string
     */
    public function retrieveMessageFile($threadId, $messageId, $fileId)
    {
        $this->addAssistantsBetaHeader();

        $url = Url::threadsUrl()
            . '/' . $threadId
            . '/messages/' . $messageId
            . '/files/' . $fileId;

        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $threadId
     * @param string $messageId
     * @param array $query
     * @return bool|string
     */
    public function listMessageFiles($threadId, $messageId, $query = [])
    {
        $this->addAssistantsBetaHeader();

        $url = Url::threadsUrl()
            . '/' . $threadId
            . '/messages/' . $messageId
            . '/files';

        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }

        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $threadId
     * @param array $data
     * @param callable|null $stream
     * @return bool|string
     * @throws Exception
     */
    public function createRun($threadId, $data, $stream = null)
    {
        if (array_key_exists('stream', $data) && $data['stream']) {
            if ($stream === null) {
                throw new Exception(
                    'Please provide a stream function. Check https://github.com/orhanerday/open-ai#stream-example for an example.'
                );
            }

            $this->stream_method = $stream;
        }

        $this->addAssistantsBetaHeader();

        $url = Url::threadsUrl() . '/' . $threadId . '/runs';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param string $threadId
     * @param string $runId
     * @return bool|string
     */
    public function retrieveRun($threadId, $runId)
    {
        $this->addAssistantsBetaHeader();

        $url = Url::threadsUrl() . '/' . $threadId . '/runs/' . $runId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $threadId
     * @param string $runId
     * @param array $data
     * @return bool|string
     */
    public function modifyRun($threadId, $runId, $data)
    {
        $this->addAssistantsBetaHeader();

        $url = Url::threadsUrl() . '/' . $threadId . '/runs/' . $runId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param string $threadId
     * @param array $query
     * @return bool|string
     */
    public function listRuns($threadId, $query = [])
    {
        $this->addAssistantsBetaHeader();

        $url = Url::threadsUrl() . '/' . $threadId . '/runs';

        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }

        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $threadId
     * @param string $runId
     * @param array $outputs
     * @param callable|null $stream
     * @return bool|string
     * @throws Exception
     */
    public function submitToolOutputs($threadId, $runId, $outputs, $stream = null)
    {
        if (array_key_exists('stream', $outputs) && $outputs['stream']) {
            if ($stream === null) {
                throw new Exception(
                    'Please provide a stream function. Check https://github.com/orhanerday/open-ai#stream-example for an example.'
                );
            }

            $this->stream_method = $stream;
        }

        $this->addAssistantsBetaHeader();

        $url = Url::threadsUrl()
            . '/' . $threadId
            . '/runs/' . $runId
            . '/submit_tool_outputs';

        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $outputs);
    }

    /**
     * @param string $threadId
     * @param string $runId
     * @return bool|string
     */
    public function cancelRun($threadId, $runId)
    {
        $this->addAssistantsBetaHeader();

        $url = Url::threadsUrl() . '/' . $threadId . '/runs/' . $runId . '/cancel';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST');
    }

    /**
     * @param array $data
     * @return bool|string
     */
    public function createThreadAndRun($data)
    {
        $this->addAssistantsBetaHeader();

        $url = Url::threadsUrl() . '/runs';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param string $threadId
     * @param string $runId
     * @param string $stepId
     * @return bool|string
     */
    public function retrieveRunStep($threadId, $runId, $stepId)
    {
        $this->addAssistantsBetaHeader();

        $url = Url::threadsUrl()
            . '/' . $threadId
            . '/runs/' . $runId
            . '/steps/' . $stepId;

        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $threadId
     * @param string $runId
     * @param array $query
     * @return bool|string
     */
    public function listRunSteps($threadId, $runId, $query = [])
    {
        $this->addAssistantsBetaHeader();

        $url = Url::threadsUrl()
            . '/' . $threadId
            . '/runs/' . $runId
            . '/steps';

        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }

        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param array $opts
     * @return bool|string
     */
    public function tts($opts)
    {
        $url = Url::ttsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param int $timeout
     * @return void
     */
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @param string $proxy
     * @return void
     */
    public function setProxy(string $proxy)
    {
        if ($proxy && strpos($proxy, '://') === false) {
            $proxy = 'https://' . $proxy;
        }

        $this->proxy = $proxy;
    }

    /**
     * @param string $customUrl
     * @return void
     */
    public function setCustomURL(string $customUrl)
    {
        if ($customUrl !== "") {
            $this->customUrl = $customUrl;
        }
    }

    /**
     * @param string $customUrl
     * @return void
     */
    public function setBaseURL(string $customUrl)
    {
        if ($customUrl !== '') {
            $this->customUrl = $customUrl;
        }
    }

    /**
     * @param array $header
     * @return void
     */
    public function setHeader(array $header)
    {
        if ($header) {
            foreach ($header as $key => $value) {
                $this->headers[$key] = $value;
            }
        }
    }

    /**
     * @param string $org
     * @return void
     */
    public function setORG(string $org)
    {
        if ($org !== "") {
            $this->headers[] = "OpenAI-Organization: $org";
        }
    }

    /**
     * @param string $version
     * @return void
     */
    public function setAssistantsBetaVersion(string $version)
    {
        if ($version !== "") {
            $this->assistantsBetaVersion = $version;
        }
    }

    /**
     * @return void
     */
    private function addAssistantsBetaHeader()
    {
        $this->headers[] = 'OpenAI-Beta: assistants=' . $this->assistantsBetaVersion;
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $opts
     * @return bool|string
     * @throws Exception
     */
    private function sendRequest(string $url, string $method, array $opts = [])
    {
        $postFields = json_encode($opts);

        if (array_key_exists('file', $opts) || array_key_exists('image', $opts)) {
            $this->headers[0] = $this->contentTypes["multipart/form-data"];
            $postFields = $opts;
        } else {
            $this->headers[0] = $this->contentTypes["application/json"];
        }

        $curlInfo = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => $this->headers,
        ];

        if ($opts === []) {
            unset($curlInfo[CURLOPT_POSTFIELDS]);
        }

        if (!empty($this->proxy)) {
            $curlInfo[CURLOPT_PROXY] = $this->proxy;
        }

        if (array_key_exists('stream', $opts) && $opts['stream']) {
            $curlInfo[CURLOPT_WRITEFUNCTION] = $this->stream_method;
        }

        $curl = curl_init();

        curl_setopt_array($curl, $curlInfo);

        $response = curl_exec($curl);
        $curlError = curl_error($curl);

        $this->curlInfo = curl_getinfo($curl);

        curl_close($curl);

        if ($response === false) {
            throw new Exception($curlError);
        }

        return $response;
    }

    /**
     * @param string $url
     * @return void
     */
    private function baseUrl(string &$url)
    {
        if ($this->customUrl !== "") {
            $url = str_replace(Url::ORIGIN, $this->customUrl, $url);
        }
    }
}