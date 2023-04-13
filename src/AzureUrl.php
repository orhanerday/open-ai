<?php

namespace Ze\OpenAi;

class AzureUrl
{
    private $baseUrl;
    private $apiVersion;

    const PROTOCOL = 'https';
    const DOMAIN   = 'openai.azure.com/openai';

    public function __construct(string $sourceName, ?string $apiVersion)
    {
        $this->baseUrl = self::PROTOCOL . '://' . $sourceName . '.' . self::DOMAIN;

        $this->apiVersion = $apiVersion ?: date('Y-m-d');
    }

    // build complete API url
    protected function buildUrl(string $path)
    {
        return $this->baseUrl . $path . '?api-version=' . $this->apiVersion;
    }

    public function embeddingsUrl(string $model)
    {
        return $this->buildUrl("/deployments/{$model}/embeddings");
    }

    public function chatUrl(string $model)
    {
        return $this->buildUrl("/deployments/{$model}/chat/completions");
    }

    public function completionUrl(string $model)
    {
        return $this->buildUrl("/deployments/{$model}/completions");
    }

    public function fileUrl(string $fileId)
    {
        return $this->buildUrl("/files/{$fileId}");
    }

    public function fileContentUrl(string $fileId)
    {
        return $this->buildUrl("/files/{$fileId}/content");
    }

    public function importContentUrl(string $fileId)
    {
        return $this->buildUrl("/files/{$fileId}/import");
    }

    public function filesUrl()
    {
        return $this->buildUrl('/files');
    }

    public function fineTunesUrl()
    {
        return $this->buildUrl('/fine-tunes');
    }

    public function fineTuneCancelUrl(string $fineTuneId)
    {
        return $this->buildUrl("/fine-tunes/{$fineTuneId}/cancel");
    }

    public function fineTuneEventsUrl(string $fineTuneId)
    {
        return $this->buildUrl('/fine-tunes/{$fineTuneId}/events');
    }

    public function fineTuneUrl(string $fineTuneId)
    {
        return $this->buildUrl("/fine-tunes/{$fineTuneId}");
    }

    public function deploymentsUrl()
    {
        return $this->buildUrl('/deployments');
    }

    public function deploymentUrl(string $deploymentId)
    {
        return $this->buildUrl("/deployments/{$deploymentId}");
    }

    public function modelUrl(string $modelId)
    {
        return $this->buildUrl("/models/{$modelId}");
    }

    public function modelsUrl()
    {
        return $this->buildUrl("/models");
    }
}
