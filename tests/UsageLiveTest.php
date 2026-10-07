<?php

use Orhanerday\OpenAi\OpenAi;

beforeEach(function () {
    $key = getenv('OPENAI_ADMIN_KEY');
    if (! $key) {
        $this->markTestSkipped('Set OPENAI_ADMIN_KEY to run real organization Usage and Costs tests.');
    }

    $this->reporting = new OpenAi($key);
    $this->reporting->setTimeout(30);
    $this->usageOptions = [
        'start_time' => (int) floor(time() / 86400) * 86400 - 7 * 86400,
        'end_time' => time(),
        'bucket_width' => '1d',
        'limit' => 1,
    ];
    $this->decodeUsagePage = function ($response) {
        $status = $this->reporting->getCURLInfo()['http_code'];
        $body = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        if ($status !== 200) {
            $message = preg_replace('/sk-[A-Za-z0-9_-]+/', '[redacted]', $body['error']['message'] ?? 'Unexpected API status');
            $this->fail('HTTP ' . $status . ': ' . $message);
        }
        expect($body['object'])->toBe('page');
        expect($body['data'])->toBeArray();
        expect($body['has_more'])->toBeBool();
        expect(array_key_exists('next_page', $body))->toBeTrue();
        if ($body['has_more']) {
            expect($body['next_page'])->toBeString()->not->toBeEmpty();
        }
        foreach ($body['data'] as $bucket) {
            expect($bucket['object'])->toBe('bucket');
            expect($bucket['start_time'])->toBeInt();
            expect($bucket['end_time'])->toBeInt()->toBeGreaterThan($bucket['start_time']);
            expect($bucket['results'])->toBeArray();
        }

        return $body;
    };
});

it('retrieves real organization usage and costs pages', function ($method, $resultObject) {
    $options = $this->usageOptions;
    $options['group_by'] = ['project_id'];
    $page = ($this->decodeUsagePage)($this->reporting->$method($options));
    expect(count($page['data']))->toBeLessThanOrEqual(1);
    foreach ($page['data'] as $bucket) {
        foreach ($bucket['results'] as $result) {
            expect($result['object'])->toBe($resultObject);
        }
    }
})->with([
    'completions' => ['getCompletionsUsage', 'organization.usage.completions.result'],
    'embeddings' => ['getEmbeddingsUsage', 'organization.usage.embeddings.result'],
    'moderations' => ['getModerationsUsage', 'organization.usage.moderations.result'],
    'images' => ['getImagesUsage', 'organization.usage.images.result'],
    'audio speeches' => ['getAudioSpeechesUsage', 'organization.usage.audio_speeches.result'],
    'audio transcriptions' => ['getAudioTranscriptionsUsage', 'organization.usage.audio_transcriptions.result'],
    'vector stores' => ['getVectorStoresUsage', 'organization.usage.vector_stores.result'],
    'code interpreter' => ['getCodeInterpreterSessionsUsage', 'organization.usage.code_interpreter_sessions.result'],
    'file search' => ['getFileSearchCallsUsage', 'organization.usage.file_searches.result'],
    'web search' => ['getWebSearchCallsUsage', 'organization.usage.web_searches.result'],
    'costs' => ['getCosts', 'organization.costs.result'],
])->group('live', 'usage');

it('groups completions usage by multiple fields and filters non-batch requests', function () {
    $options = $this->usageOptions;
    $options['start_time'] = (int) floor(time() / 86400) * 86400;
    $options['group_by'] = ['model', 'batch'];
    $options['batch'] = false;
    $page = ($this->decodeUsagePage)($this->reporting->getCompletionsUsage($options));
    foreach ($page['data'] as $bucket) {
        foreach ($bucket['results'] as $result) {
            expect(array_key_exists('model', $result))->toBeTrue();
            expect($result['batch'])->toBeFalse();
        }
    }
})->group('live', 'usage');

it('groups real costs by project and invoice line item', function () {
    $options = $this->usageOptions;
    $options['start_time'] = (int) floor(time() / 86400) * 86400;
    $options['group_by'] = ['project_id', 'line_item'];
    $page = ($this->decodeUsagePage)($this->reporting->getCosts($options));
    foreach ($page['data'] as $bucket) {
        foreach ($bucket['results'] as $result) {
            expect(array_key_exists('project_id', $result))->toBeTrue();
            expect(array_key_exists('line_item', $result))->toBeTrue();
            expect($result['amount'])->toBeArray();
        }
    }
})->group('live', 'usage');

it('follows a real reporting cursor while preserving query options', function ($method) {
    $options = $this->usageOptions;
    $options['group_by'] = ['project_id'];
    $first = ($this->decodeUsagePage)($this->reporting->$method($options));
    if (! $first['has_more']) {
        $this->markTestSkipped('The selected organization time range did not return a second reporting page.');
    }
    $options['page'] = $first['next_page'];
    $second = ($this->decodeUsagePage)($this->reporting->$method($options));
    expect($second['data'])->not->toBeEmpty();
    expect($second['data'][0]['start_time'])->toBeGreaterThan($first['data'][0]['start_time']);
})->with(['getCompletionsUsage', 'getCosts'])->group('live', 'usage');

it('returns the real API validation error when start_time is missing', function () {
    $body = json_decode($this->reporting->getCompletionsUsage([]), true, 512, JSON_THROW_ON_ERROR);
    expect($this->reporting->getCURLInfo()['http_code'])->toBe(400);
    expect($body['error']['message'])->toBeString()->not->toBeEmpty();
})->group('live', 'usage');
