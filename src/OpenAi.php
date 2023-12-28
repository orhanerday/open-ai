<?php

namespace Orhanerday\OpenAi;

use Exception;
use Illuminate\Support\Facades\Log;

class OpenAi
{
    private const AZURE_OPEN_AI = 'azure_open_ai';
    private const FUNCTION_IMAGE = 'image';
    private const FUNCTION_CREATE_IMAGE_VERSION = 'createImageVariation';
    private const FUNCTION_IMAGE_EDIT = 'imageEdit';
    private string $origin = '';
    private string $apiVersion = Url::API_VERSION;
    private string $engine = "davinci";
    private string $defaultModel = "text-davinci-002";
    private string $model = "";
    private array $headers;
    private array $contentTypes;
    private string $apiKey = '';
    private int $timeout = 60;
    private object $stream_method;
    private string $customUrl = "";
    private string $proxy = "";
    private array $curlInfo = [];
    private array $imageAiFunctions = [
        self::FUNCTION_CREATE_IMAGE_VERSION,
        self::FUNCTION_IMAGE_EDIT,
        self::FUNCTION_IMAGE,
    ];
    private array $config = [];
    private array $configs = [];


    public function __construct($OPENAI_API_KEY = '')
    {
        $this->contentTypes = [
            "application/json" => "Content-Type: application/json",
            "multipart/form-data" => "Content-Type: multipart/form-data",
        ];
        $this->apiKey = $OPENAI_API_KEY;
    }

    //获取配置
    protected function getConfig()
    {
        $config = config('openai');
        $default = $config['default'] ?? '';
        $driver_config = [];
        foreach ($config as $key => $value) {
            if (isset($value['driver']) && $value['driver'] == $default) {
                $driver_config[] = $value;
            }
        }
        if (empty($driver_config)) {
            throw new Exception('No default driver');
        }
        return $this->polling($driver_config);
    }

    /**
     * @description polling ","
     * @return void
     */
    protected function polling($config)
    {
        $current_config = [];
        //拆分token轮询;
        if (count($config) > 1) {
            $path = storage_path('app/openai_token/' . md5(json_encode($config)));

            try {
                $token_number = file_get_contents($path);
            } catch (\Exception $e) {
                $token_number = 0;
            }
            //根据$token_number切换数组
            file_put_contents($path, ($token_number + 1));
            $current = $token_number % count($config);
            $current_config = $config[$current] ?? $config[0];
        }
        return $current_config;
    }

    /**
     * @description Get headers
     * @param string $OPENAI_API_KEY
     * @return string[]
     */
    protected function getHeaders(string $OPENAI_API_KEY = '', $type = ''): array
    {
        $config = $this->config ?? $this->getConfig();
        $driver = $config['driver'] ?? '';
        $aip_key = $config['api_key'] ?? $OPENAI_API_KEY;
        //change model
        if (isset($config['model'])) {
            $this->defaultModel = $config['model'];
        }

        if ($driver == self::AZURE_OPEN_AI) {
            if ($type == 'dalle') {
                $aip_key = $config['dalle_api_key'] ?? $OPENAI_API_KEY;
            }

            return [
                $this->contentTypes["application/json"],
                "api-key: " . $aip_key,
            ];
        }

        return [
            $this->contentTypes["application/json"],
            "Authorization: Bearer " . $config['api_key'] ?? $OPENAI_API_KEY,
        ];
    }

    /**
<<<<<<< HEAD
=======
     * @description polling ","
     * @return void
     */
    protected function polling($tokens)
    {
        //拆分token轮询
        $explode = explode(',', $tokens);
        $token = $explode[0] ?? '';
        if (count($explode) > 1) {
            $path = storage_path('app/openai_token/' . md5($tokens));

            try {
                $token_number = file_get_contents($path);
            } catch (\Exception $e) {
                $token_number = 0;
            }
            //根据$token_number切换数组
            file_put_contents($path, ($token_number + 1));
            $token = $token_number % count($explode) == 0 ? $explode[0] : $explode[1];
        }

        return $token;
    }

    /**
>>>>>>> a1ce7856a5ac511d5dbe31185cdd403f0c501c2e
     * @return array
     * Remove this method from your code before deploying
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
        $url = Url::fineTuneModel() . $model;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $opts
     * @return bool|string
     * @deprecated
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
     * @param        $opts
     * @param null $stream
     * @return bool|string
     * @throws Exception
     */
    public function completion($opts, $stream = null)
    {
        if (array_key_exists('stream', $opts) && $opts['stream']) {
            if ($stream == null) {
                throw new Exception(
                    'Please provide a stream function. Check https://github.com/orhanerday/open-ai#stream-example for an example.'
                );
            }

            $this->stream_method = $stream;
        }
        $this->model = $opts['model'] ?? '';
        $opts['model'] = $opts['model'] ?? $this->defaultModel;
        $url = Url::completionsURL();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function createEdit($opts)
    {
        $url = Url::editsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function image($opts)
    {
        $this->model = $opts['model'] ?? '';
        $url = Url::imageUrl() . "/generations";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function imageEdit($opts)
    {
        $url = Url::imageUrl() . "/edits";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function createImageVariation($opts)
    {
        $this->model = $opts['model'] ?? '';
        $url = Url::imageUrl() . "/variations";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     * @deprecated
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
     * @param $opts
     * @return bool|string
     * @deprecated
     */
    public function answer($opts)
    {
        $url = Url::answersUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     * @deprecated
     */
    public function classification($opts)
    {
        $url = Url::classificationsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
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
     * @param null $stream
     * @return bool|string
     * @throws Exception
     */
    public function chat($opts, $stream = null)
    {
        if ($stream != null && array_key_exists('stream', $opts)) {
            if (! $opts['stream']) {
                throw new Exception(
                    'Please provide a stream function. Check https://github.com/orhanerday/open-ai#stream-example for an example.'
                );
            }

            $this->stream_method = $stream;
        }
        $this->model = $opts['model'] ?? '';
        $opts['model'] = $opts['model'] ?? $this->defaultModel;
        $url = Url::chatUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function transcribe($opts)
    {
        $url = Url::transcriptionsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
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
    public function listFiles()
    {
        $url = Url::filesUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $file_id
     * @return bool|string
     */
    public function retrieveFile($file_id)
    {
        $file_id = "/$file_id";
        $url = Url::filesUrl() . $file_id;
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
        $url = Url::filesUrl() . $file_id;
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
        $url = Url::filesUrl() . $file_id;
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
    public function listFineTunes()
    {
        $url = Url::fineTuneUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $fine_tune_id
     * @return bool|string
     */
    public function retrieveFineTune($fine_tune_id)
    {
        $fine_tune_id = "/$fine_tune_id";
        $url = Url::fineTuneUrl() . $fine_tune_id;
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
        $url = Url::fineTuneUrl() . $fine_tune_id;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST');
    }

    /**
     * @param $fine_tune_id
     * @return bool|string
     */
    public function listFineTuneEvents($fine_tune_id)
    {
        $fine_tune_id = "/$fine_tune_id/events";
        $url = Url::fineTuneUrl() . $fine_tune_id;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $fine_tune_id
     * @return bool|string
     */
    public function deleteFineTune($fine_tune_id)
    {
        $fine_tune_id = "/$fine_tune_id";
        $url = Url::fineTuneModel() . $fine_tune_id;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    /**
     * @param
     * @return bool|string
     * @deprecated
     */
    public function engines()
    {
        $url = Url::enginesUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $engine
     * @return bool|string
     * @deprecated
     */
    public function engine($engine)
    {
        $url = Url::engineUrl($engine);
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $opts
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
        $this->model = $data['model'] ?? '';
        $data['model'] = $data['model'] ?? $this->defaultModel;
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
        $url = Url::assistantsUrl() . '/' . $assistantId . '/files';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', ['file_id' => $fileId]);
    }

    /**
     * @param string $assistantId
     * @param string $fileId
     * @return bool|string
     */
    public function retrieveAssistantFile($assistantId, $fileId)
    {
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
        $url = Url::threadsUrl() . '/' . $threadId . '/messages/' . $messageId . '/files/' . $fileId;
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
        $url = Url::threadsUrl() . '/' . $threadId . '/messages/' . $messageId . '/files';
        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $threadId
     * @param array $data
     * @return bool|string
     */
    public function createRun($threadId, $data)
    {
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
     * @return bool|string
     */
    public function submitToolOutputs($threadId, $runId, $outputs)
    {
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
        $url = Url::threadsUrl() . '/' . $threadId . '/runs/' . $runId . '/submit_tool_outputs';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
        $url = Url::threadsUrl() . '/' . $threadId . '/runs/' . $runId . '/steps/' . $stepId;
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
        $this->headers[] = 'OpenAI-Beta: assistants=v1';
        $url = Url::threadsUrl() . '/' . $threadId . '/runs/' . $runId . '/steps';
        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @param string $proxy
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
     * @deprecated
     */

    /**
     * @param string $customUrl
     * @return void
     */
    public function setCustomURL(string $customUrl)
    {
        if ($customUrl != "") {
            $this->customUrl = $customUrl;
        }
    }

    /**
     * @param string $customUrl
     * @return void
     */
    public function setBaseURL(string $customUrl)
    {
        if ($customUrl != '') {
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
     */
    public function setORG(string $org)
    {
        if ($org != "") {
            $this->headers[] = "OpenAI-Organization: $org";
        }
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $opts
     * @return bool|string
     */
    private function sendRequest(string $url, string $method, array $opts = [])
    {
        $post_fields = json_encode($opts);
        if (array_key_exists('file', $opts) || array_key_exists('image', $opts)) {
            $this->headers[0] = $this->contentTypes["multipart/form-data"];
            $post_fields = $opts;
        } else {
            $this->headers[0] = $this->contentTypes["application/json"];
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
            CURLOPT_SSL_VERIFYPEER => false,
        ];
        //service check verifypeer
        if (config('app.env') == 'production') {
            $curl_info[CURLOPT_SSL_VERIFYPEER] = true;
        }
        if ($opts == []) {
            unset($curl_info[CURLOPT_POSTFIELDS]);
        }

        if (! empty($this->proxy)) {
            $curl_info[CURLOPT_PROXY] = $this->proxy;
        }

        if (array_key_exists('stream', $opts) && $opts['stream']) {
            $curl_info[CURLOPT_WRITEFUNCTION] = $this->stream_method;
        }
        $curl = curl_init();
        curl_setopt_array($curl, $curl_info);

        Log::channel('openai_request')->info(json_encode($curl_info));

        $response = curl_exec($curl);

        $info = curl_getinfo($curl);
        $this->curlInfo = $info;

        curl_close($curl);

        Log::channel('openai_request')->debug(json_encode($info));


        if (! $response) {
            throw new Exception(curl_error($curl));
        }
        Log::channel('openai_request')->info($response);

        return $response;
    }

    /**
     * @param string $url
     */
    private function baseUrl(string &$url)
    {
        $parentFunction = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? '';
        $configUrl = $this->makeConfigUrl($parentFunction);
        if ($configUrl) {
            $url = str_replace(Url::OPEN_AI_URL, $configUrl, $url);
        } elseif ($this->customUrl) {
            $url = str_replace(Url::ORIGIN, $this->customUrl, $url);
        }
        //azure open ai append api version
        if ($this->origin == self::AZURE_OPEN_AI) {
            $url .= '?api-version=' . $this->apiVersion;
        }
    }

    /**
     * @return mixed|string
     */
    protected function makeConfigUrl($function = ''): mixed
    {
        $config = $this->getConfig();
        $this->config = $config;
        $this->headers = $this->getHeaders($this->apiKey);
        $base_url = $config['base_url'] ?? '';
        $this->origin = $config['driver'] ?? '';
        $this->apiVersion = $config['api_version'] ?? $this->apiVersion;
        $models = $config['models'] ?? [];
        $driver = $config['driver'] ?? '';

        //微软
        if ($driver == self::AZURE_OPEN_AI) {
            $model = $models[$config['model']] ?? $this->defaultModel;
            if ($this->model) {
                $model = $models[$this->model] ?? $this->model;
            }
            //拼接链接
            $base_url = $base_url . 'openai/deployments/' . $model;
            if (in_array($function, $this->imageAiFunctions)) {
                //切换头部授权
                $this->headers = $this->getHeaders($this->apiKey, 'dalle');
                $base_url = $config['dalle_url'] ?? '';
                if ($function == self::FUNCTION_IMAGE) {
                    if ($model == 'dall-e-2') {
                        $base_url = $base_url . 'openai/images/generations:submit';
                    } else {
                        $base_url = $base_url . 'openai/deployments/' . $model;
                    }
                } else {
                    $base_url = 'openai/operations/images/' . $model;
                }
            }

            $this->setCustomURL($base_url);
        }

        return $base_url;
    }
}
