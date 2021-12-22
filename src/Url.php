<?php

namespace Orhanerday\OpenAi;

class Url
{

    const ORIGIN = 'https://api.openai.com';
    const API_VERSION = 'v1';
    const OPEN_AI_URL = self::ORIGIN ."/". self::API_VERSION;

    /**
     * @param string $engine
     * @return string
     */
    public static function completionURL(string $engine) {
        return self::OPEN_AI_URL."/engines/$engine/completions";
    }

    /**
     * @param string $engine
     * @return string
     */
    public static function searchURL(string $engine) {
        return self::OPEN_AI_URL."/engines/$engine/search";
    }

    public static function enginesUrl()
    {
        return self::OPEN_AI_URL."/engines";
    }

    /**
     * @param string $engine
     * @return string
     */
    public static function engineUrl(string $engine) {
        return self::OPEN_AI_URL."/engines/$engine";
    }

    public static function classificationsUrl()
    {
        return self::OPEN_AI_URL."/classifications";
    }

    public static function filesUrl()
    {
        return self::OPEN_AI_URL."/files";
    }

    public static function answersUrl()
    {
        return self::OPEN_AI_URL."/answers";
    }


}
