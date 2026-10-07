<?php

namespace Orhanerday\OpenAi;

class Url
{
    public const ORIGIN = 'https://api.openai.com';
    public const API_VERSION = 'v1';
    public const OPEN_AI_URL = self::ORIGIN . "/" . self::API_VERSION;

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

    public static function fineTuningCheckpointsUrl(): string
    {
        return self::OPEN_AI_URL . "/fine_tuning/checkpoints";
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
     * @return string
     */
    public static function ttsUrl(): string
    {
        return self::OPEN_AI_URL . "/audio/speech";
    }

    /**
     * @return string
     */
    public static function responsesUrl(): string
    {
        return self::OPEN_AI_URL . "/responses";
    }

    /**
     * @return string
     */
    public static function conversationsUrl(): string
    {
        return self::OPEN_AI_URL . "/conversations";
    }

    /**
     * @return string
     */
    public static function vectorStoresUrl(): string
    {
        return self::OPEN_AI_URL . "/vector_stores";
    }

    /**
     * @return string
     */
    public static function batchesUrl(): string
    {
        return self::OPEN_AI_URL . "/batches";
    }

    /**
     * @return string
     */
    public static function uploadsUrl(): string
    {
        return self::OPEN_AI_URL . "/uploads";
    }

    /**
     * @return string
     */
    public static function realtimeClientSecretsUrl(): string
    {
        return self::OPEN_AI_URL . "/realtime/client_secrets";
    }

    public static function decisionsUrl(): string
    {
        return self::OPEN_AI_URL . '/decisions';
    }

    public static function webhookEndpointsUrl(): string
    {
        return self::OPEN_AI_URL . '/webhook_endpoints';
    }

    public static function voicesUrl(): string
    {
        return self::OPEN_AI_URL . '/audio/voices';
    }

    public static function voiceConsentsUrl(): string
    {
        return self::OPEN_AI_URL . '/audio/voice_consents';
    }

    public static function fineTuningGradersUrl(): string
    {
        return self::OPEN_AI_URL . '/fine_tuning/alpha/graders';
    }

    public static function realtimeCallsUrl(): string
    {
        return self::OPEN_AI_URL . '/realtime/calls';
    }

    public static function realtimeTranslationClientSecretsUrl(): string
    {
        return self::OPEN_AI_URL . '/realtime/translations/client_secrets';
    }
}
