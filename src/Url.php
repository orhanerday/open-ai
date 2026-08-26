<?php

namespace Orhanerday\OpenAi;

class Url
{
    public const ORIGIN = 'https://api.openai.com';
    public const API_VERSION = 'v1';
    public const OPEN_AI_URL = self::ORIGIN . "/" . self::API_VERSION;

    /**
     * @deprecated
     *
     * @param string $engine
     * @return string
     */
    public static function completionURL(string $engine): string
    {
        return self::OPEN_AI_URL . "/engines/$engine/completions";
    }

    /**
     * @return string
     */
    public static function completionsURL(): string
    {
        return self::OPEN_AI_URL . "/completions";
    }

    /**
     * @return string
     */
    public static function editsUrl(): string
    {
        return self::OPEN_AI_URL . "/edits";
    }

    /**
     * @param string $engine
     * @return string
     */
    public static function searchURL(string $engine): string
    {
        return self::OPEN_AI_URL . "/engines/$engine/search";
    }

    /**
     * @return string
     */
    public static function enginesUrl(): string
    {
        return self::OPEN_AI_URL . "/engines";
    }

    /**
     * @param string $engine
     * @return string
     */
    public static function engineUrl(string $engine): string
    {
        return self::OPEN_AI_URL . "/engines/$engine";
    }

    /**
     * @return string
     */
    public static function classificationsUrl(): string
    {
        return self::OPEN_AI_URL . "/classifications";
    }

    /**
     * @return string
     */
    public static function moderationUrl(): string
    {
        return self::OPEN_AI_URL . "/moderations";
    }

    /**
     * @return string
     */
    public static function transcriptionsUrl(): string
    {
        return self::OPEN_AI_URL . "/audio/transcriptions";
    }

    /**
     * @return string
     */
    public static function translationsUrl(): string
    {
        return self::OPEN_AI_URL . "/audio/translations";
    }

    /**
     * @return string
     */
    public static function filesUrl(): string
    {
        return self::OPEN_AI_URL . "/files";
    }

    /**
     * @return string
     */
    public static function fineTuneUrl(): string
    {
        return self::OPEN_AI_URL . "/fine_tuning/jobs";
    }

    /**
     * @return string
     */
    public static function fineTuneModel(): string
    {
        return self::OPEN_AI_URL . "/models";
    }

    /**
     * @return string
     */
    public static function answersUrl(): string
    {
        return self::OPEN_AI_URL . "/answers";
    }

    /**
     * @return string
     */
    public static function imageUrl(): string
    {
        return self::OPEN_AI_URL . "/images";
    }

    /**
     * @return string
     */
    public static function embeddings(): string
    {
        return self::OPEN_AI_URL . "/embeddings";
    }

    /**
     * @return string
     */
    public static function chatUrl(): string
    {
        return self::OPEN_AI_URL . "/chat/completions";
    }

    /**
     * Responses API endpoint.
     *
     * @return string
     */
    public static function responsesUrl(): string
    {
        return self::OPEN_AI_URL . "/responses";
    }

    /**
     * Retrieve a specific response.
     *
     * @param string $responseId
     * @return string
     */
    public static function responseUrl(string $responseId): string
    {
        return self::OPEN_AI_URL . "/responses/" . rawurlencode($responseId);
    }

    /**
     * Cancel a background response.
     *
     * @param string $responseId
     * @return string
     */
    public static function cancelResponseUrl(string $responseId): string
    {
        return self::OPEN_AI_URL
            . "/responses/"
            . rawurlencode($responseId)
            . "/cancel";
    }

    /**
     * @return string
     */
    public static function assistantsUrl(): string
    {
        return self::OPEN_AI_URL . "/assistants";
    }

    /**
     * @return string
     */
    public static function threadsUrl(): string
    {
        return self::OPEN_AI_URL . "/threads";
    }

    /**
     * @return string
     */
    public static function ttsUrl(): string
    {
        return self::OPEN_AI_URL . "/audio/speech";
    }
}