<?php

namespace Orhanerday\OpenAi;

class Url
{
    public const ORIGIN = 'https://api.openai.com';
    public const API_VERSION = 'v1';
    public const OPEN_AI_URL = self::ORIGIN ."/". self::API_VERSION;

    /**
     * @param string $engine
     * @return string
     */
    public static function completionURL(string $engine): string
    {
        return self::OPEN_AI_URL."/engines/$engine/completions";
    }

    /**
     * @param string $engine
     * @return string
     */
    public static function searchURL(string $engine): string
    {
        return self::OPEN_AI_URL."/engines/$engine/search";
    }

    public static function enginesUrl(): string
    {
        return self::OPEN_AI_URL."/engines";
    }

    /**
     * @param string $engine
     * @return string
     */
    public static function engineUrl(string $engine): string
    {
        return self::OPEN_AI_URL."/engines/$engine";
    }

    public static function classificationsUrl(): string
    {
        return self::OPEN_AI_URL."/classifications";
    }

    public static function filesUrl(): string
    {
        return self::OPEN_AI_URL."/files";
    }

    public static function answersUrl(): string
    {
        return self::OPEN_AI_URL."/answers";
    }
}
