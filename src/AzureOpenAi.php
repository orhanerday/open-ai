<?php

namespace Ze\OpenAi;

use Ze\OpenAi\Exceptions\OpenAiException;

class AzureOpenAi
{
    // auth types
    const AUTH_API_KEY = 1,
        AUTH_API_TOKEN = 2;

    private $contentTypes = [
        'application/json' => 'Content-Type: application/json',
        'multipart/form-data' => 'Content-Type: multipart/form-data',
    ];
    private $curlConfig = [];
    private $stream_method;

    private $urlBuilder;

    public function __construct(
        string $resourceName,
        string $authKey,
        string $apiVersion = null,
        int $authType = self::AUTH_API_KEY)
    {
        $this->headers = [
            $this->contentTypes['application/json']
        ];

        if ($authType == self::AUTH_API_KEY) {
            $this->headers[] = "api-key: {$authKey}";
        }

        if ($authType == self::AUTH_API_TOKEN) {
            $this->headers[] = "Authorization: Bearer {$authKey}";
        }

        $this->urlBuilder = new AzureUrl($resourceName, $apiVersion);
    }

    public function setCurlConfig(array $conf)
    {
        $this->curlConfig = $conf;
    }

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
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_POSTFIELDS => $post_fields,
                CURLOPT_HTTPHEADER => $this->headers,
            ] + $this->curlConfig;

        if ($opts == []) {
            unset($curl_info[CURLOPT_POSTFIELDS]);
        }

        if (array_key_exists('stream', $opts) && $opts['stream']) {
            $curl_info[CURLOPT_WRITEFUNCTION] = $this->stream_method;
        }

        $curl = curl_init();
        curl_setopt_array($curl, $curl_info);
        $response = curl_exec($curl);

        if ($response === false || curl_errno($curl)) {
            throw new OpenAiException('Curl error: ' . curl_error($curl));
        }

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($httpCode != 200) {
            $errJson = json_decode($response, true);
            $httpErrMsg = "An error occurred [{$httpCode}]";
            if (isset($errJson['error'])) {
                $httpErrMsg = $errJson['error']['code'] . '|' . $errJson['error']['message'];
            }
            throw new OpenAiException('OpenAIError: ' . $httpErrMsg, $httpCode);
        }

        curl_close($curl);

        return $response;
    }

    // create embedding
    public function embeddings(array $opts)
    {
        $url = $this->urlBuilder->embeddingsUrl($opts['model']);
        unset($opts['model']);

        return $this->sendRequest($url, 'POST', $opts);
    }

    // chat (need gpt-3.5 or 4)
    public function chat(array $opts, $stream = null)
    {
        if ($stream != null && array_key_exists('stream', $opts)) {
            if (! $opts['stream']) {
                throw new OpenAiException(
                    'Please provide a stream function. '
                );
            }

            $this->stream_method = $stream;
        }

        $url = $this->urlBuilder->chatUrl($opts['model']);
        unset($opts['model']);

        return $this->sendRequest($url, 'POST', $opts);
    }

    // completion
    public function completion(array $opts, $stream = null)
    {
        if ($stream != null && array_key_exists('stream', $opts)) {
            if (! $opts['stream']) {
                throw new OpenAiException(
                    'Please provide a stream function.'
                );
            }

            $this->stream_method = $stream;
        }

        $url = $this->urlBuilder->completionUrl($opts['model']);
        unset($opts['model']);

        return $this->sendRequest($url, 'POST', $opts);
    }

    // delete uploaded file
    public function deleteFile(string $fileId)
    {
        $url = $this->urlBuilder->fileUrl($fileId);

        return $this->sendRequest($url, 'DELETE');
    }

    // get file info
    public function retrieveFile(string $fileId)
    {
        $url = $this->urlBuilder->fileUrl($fileId);

        return $this->sendRequest($url, 'GET');
    }

    // get file content detail
    public function fileContent(string $fileId)
    {
        $url = $this->urlBuilder->fileContentUrl($fileId);

        return $this->sendRequest($url, 'GET');
    }

    public function importFile(string $fileId, array $opts)
    {
        $url = $this->urlBuilder->importContentUrl($fileId);

        return $this->sendRequest($url, 'POST', $opts);
    }

    // file upload
    public function uploadFile(array $opts)
    {
        $url = $this->urlBuilder->filesUrl();

        return $this->sendRequest($url, 'POST', $opts);
    }

    // get all files info
    public function listFiles()
    {
        $url = $this->urlBuilder->filesUrl();

        return $this->sendRequest($url, 'GET');
    }

    // create fine-tune
    public function createFineTune(array $opts)
    {
        $url = $this->urlBuilder->fineTunesUrl();

        return $this->sendRequest($url, 'POST', $opts);
    }

    // cancel fine-tune
    public function cancelFineTune(string $fineTuneId)
    {
        $url = $this->urlBuilder->fineTuneCancelUrl($fineTuneId);

        return $this->sendRequest($url, 'POST');
    }

    // delete fine-tune
    public function deleteFineTune(string $fineTuneId)
    {
        $url = $this->urlBuilder->fineTuneUrl($fineTuneId);

        return $this->sendRequest($url, 'DELETE');
    }

    // get fine-tune info
    public function retrieveFineTune(string $fineTuneId)
    {
        $url = $this->urlBuilder->fineTuneUrl($fineTuneId);

        return $this->sendRequest($url, 'GET');
    }

    // list fine-tunes events
    public function retrieveFineTuneEvents(string $fineTuneId)
    {
        $url = $this->urlBuilder->fineTuneEventsUrl($fineTuneId);

        return $this->sendRequest($url, 'GET');
    }

    // get all fine-tunes
    public function listFineTunes()
    {
        $url = $this->urlBuilder->fineTunesUrl();

        return $this->sendRequest($url, 'GET');
    }

    // create deployment
    public function createDeployment(array $opts)
    {
        $url = $this->urlBuilder->deploymentsUrl();

        return $this->sendRequest($url, 'POST', $opts);
    }

    // delete deployment
    public function deleteDeployment(string $deploymentId)
    {
        $url = $this->urlBuilder->deploymentUrl($deploymentId);

        return $this->sendRequest($url, 'DELETE');
    }

    // get deployment info
    public function retrieveDeployment(string $deploymentId)
    {
        $url = $this->urlBuilder->deploymentUrl($deploymentId);

        return $this->sendRequest($url, 'GET');
    }

    // get all deployments
    public function listDeployments()
    {
        $url = $this->urlBuilder->deploymentsUrl();

        return $this->sendRequest($url, 'GET');
    }

    // update deployment
    public function updateDeployment(string $deploymentId, array $opts)
    {
        $url = $this->urlBuilder->deploymentUrl($deploymentId);

        return $this->sendRequest($url, 'PATCH', $opts);
    }

    // get model info
    public function retrieveModel(string $modelId)
    {
        $url = $this->urlBuilder->modelUrl($modelId);

        return $this->sendRequest($url, 'GET');
    }

    // get all models info
    public function listModels()
    {
        $url = $this->urlBuilder->modelsUrl();

        return $this->sendRequest($url, 'GET');
    }
}
