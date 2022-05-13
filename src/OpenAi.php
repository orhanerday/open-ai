<?php

namespace Orhanerday\OpenAi;

class OpenAi
{
    private $engine = "ada";
    private $headers;

    public function __construct($OPENAI_API_KEY)
    {
        $this->headers = [
            "Content-Type: application/json",
            "Authorization: Bearer $OPENAI_API_KEY",
        ];
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function complete($opts)
    {
        $engine = $opts['engine'] ?? $this->engine;
        $url = Url::completionURL($engine);
        unset($opts['engine']);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function search($opts)
    {
        $engine = $opts['engine'] ?? $this->engine;
        $url = Url::searchURL($engine);
        unset($opts['engine']);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function answer($opts)
    {
        $url = Url::answersUrl();

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function classification($opts)
    {
        $url = Url::classificationsUrl();

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $file
     * @return bool|string
     */
    public function uploadFile($file)
    {
        $url = Url::filesUrl();

        $key = $this->headers[1];
        $this->headers = [
            "Content-Type: multipart/form-data",
            $key,
        ];
        
        $opts = array(
            "purpose" => "answers",
            "file" => curl_file_create($file),
        );

        return $this->sendRequest($url, 'FILE_POST', $opts);
    }

    /**
     * @return bool|string
     */
    public function getFiles()
    {
        $url = Url::filesUrl();

        return $this->sendRequest($url, 'FILE_GET');
    }

    /**
     * @param
     * @return bool|string
     */
    public function engines()
    {
        $url = Url::enginesUrl();

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $engine
     * @return bool|string
     */
    public function engine($engine)
    {
        $url = Url::engineUrl($engine);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $opts
     * @return bool|string
     */
    private function sendRequest(string $url, string $method, array $opts = null)
    {
        $curl_info = [];
        if($method == 'POST' || $method == 'GET') //custom post or get
        {
            $curl_info = [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_POSTFIELDS => json_encode($opts),
                CURLOPT_HTTPHEADER => $this->headers,
            ];
        }
        elseif($method == 'FILE_POST') //case for the file[default post command]
        {
            $curl_info = [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $opts,
                CURLOPT_HTTPHEADER => $this->headers,
            ];
        }
        elseif($method == 'FILE_GET') //case for the getting files[default post command]
        {
            $curl_info = [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => $this->headers,
            ];
        }

        if ($opts == null) {
            unset($curl_info["CURLOPT_POSTFIELDS"]);
        }

        $curl = curl_init();
        curl_setopt_array($curl, $curl_info);
        //curl_setopt($curl, CURLOPT_VERBOSE, 1); //TODO: remove when done debugging

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
