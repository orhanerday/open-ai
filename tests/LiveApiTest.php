<?php

use Orhanerday\OpenAi\OpenAi;

beforeEach(function () {
    if (! getenv('OPENAI_API_KEY')) {
        $this->markTestSkipped('Set OPENAI_API_KEY in the environment or .env to run real API tests.');
    }

    $this->client = new OpenAi(getenv('OPENAI_API_KEY'));
    $this->client->setTimeout(120);
    // Retry transient server failures only around explicitly idempotent operations.
    $this->retry = function (callable $request) {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $response = $request();
            if (! in_array($this->client->getCURLInfo()['http_code'], [500, 502, 503, 504], true) || $attempt === 2) {
                return $response;
            }
            usleep(1000000 * (1 << $attempt));
        }
    };
    $this->decode = function ($response) {
        $status = $this->client->getCURLInfo()['http_code'];
        $body = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        if ($status < 200 || $status >= 300) {
            $message = $body['error']['message'] ?? 'Unexpected API status';
            $message = preg_replace('/sk-[A-Za-z0-9_-]+/', '[redacted]', $message);
            $this->fail('HTTP ' . $status . ': ' . $message);
        }
        $this->assertNull($body['error'] ?? null);

        return $body;
    };
});

it('moderates content', function () {
    $body = ($this->decode)($this->client->moderation(['model' => 'omni-moderation-latest', 'input' => 'Hello']));
    expect($body['results'])->toBeArray()->not->toBeEmpty();
})->group('live', 'working');

it('creates retrieves downloads and deletes its own file', function () {
    $path = tempnam(sys_get_temp_dir(), 'openai_file_');
    $content = "{\"messages\":[{\"role\":\"user\",\"content\":\"Hello\"},{\"role\":\"assistant\",\"content\":\"Hi\"}]}\n";
    file_put_contents($path, $content);
    $fileId = null;

    try {
        $uploaded = ($this->decode)($this->client->uploadFile([
            'purpose' => 'fine-tune',
            'file' => new CURLFile($path, 'application/jsonl', 'integration-test.jsonl'),
        ]));
        $fileId = $uploaded['id'];
        expect($uploaded['object'])->toBe('file');

        $retrieved = ($this->decode)($this->client->retrieveFile($fileId));
        expect($retrieved['id'])->toBe($fileId);
        expect($retrieved['filename'])->toBe('integration-test.jsonl');

        expect($this->client->retrieveFileContent($fileId))->toBe($content);
        expect($this->client->getCURLInfo()['http_code'])->toBe(200);

        $deleted = ($this->decode)($this->client->deleteFile($fileId));
        expect($deleted['deleted'])->toBeTrue();
        $fileId = null;
    } finally {
        unlink($path);
        if ($fileId !== null) {
            $this->client->deleteFile($fileId);
        }
    }
})->group('live', 'working');

it('lists files fine-tuning jobs vector stores and batches', function ($method) {
    $body = ($this->decode)($this->client->$method());
    expect($body['object'])->toBe('list');
    expect($body['data'])->toBeArray();
})->with(['listFiles', 'listFineTunes', 'listVectorStores', 'listBatches'])->group('live', 'working');

it('generates an image and streams an edit with real multipart image arrays', function () {
    $model = getenv('OPENAI_IMAGE_MODEL') ?: 'gpt-image-2.5-flare';
    $body = ($this->decode)($this->client->image([
        'model' => $model,
        'prompt' => 'A picture of a cat',
        'n' => 1,
        'size' => '1024x1024',
        'quality' => 'low',
    ]));

    expect($body['data'][0]['b64_json'])->toBeString()->not->toBeEmpty();
    $path = tempnam(sys_get_temp_dir(), 'openai_image_');
    file_put_contents($path, base64_decode($body['data'][0]['b64_json'], true));
    $chunks = '';

    try {
        $this->client->imageEdit([
            'model' => $model,
            'image' => [new CURLFile($path, 'image/png', 'cat-one.png'), new CURLFile($path, 'image/png', 'cat-two.png')],
            'prompt' => 'Use these pictures to create a cat with a blue background.',
            'size' => '1024x1024',
            'quality' => 'low',
            'stream' => true,
            'partial_images' => 0,
        ], function ($curl, $data) use (&$chunks) {
            $chunks .= $data;

            return strlen($data);
        });
        if ($this->client->getCURLInfo()['http_code'] !== 200) {
            ($this->decode)($chunks);
        }
        // Keep image bytes out of failure output.
        expect(strpos($chunks, 'image_edit.completed') !== false)->toBeTrue();
    } finally {
        unlink($path);
    }
})->group('live', 'working');

it('lists and retrieves a model', function () {
    $models = ($this->decode)($this->client->listModels());
    expect($models['object'])->toBe('list');

    $modelId = getenv('OPENAI_CHAT_MODEL') ?: 'gpt-4o-mini';
    $model = ($this->decode)($this->client->retrieveModel($modelId));
    expect($model['id'])->toBe($modelId);
    expect($model['object'])->toBe('model');
})->group('live', 'working');

it('creates embeddings using the fallback model', function () {
    $body = ($this->decode)($this->client->embeddings(['input' => 'Hello']));
    expect($body['data'][0]['embedding'])->toBeArray()->not->toBeEmpty();
})->group('live', 'working');

it('creates a chat completion', function () {
    $body = ($this->decode)($this->client->chat([
        'model' => getenv('OPENAI_CHAT_MODEL') ?: 'gpt-4o-mini',
        'messages' => [['role' => 'user', 'content' => 'Say hello']],
    ]));

    expect($body['object'])->toBe('chat.completion');
    expect($body['choices'][0]['message']['content'])->toBeString()->not->toBeEmpty();
})->group('live', 'working');

it('creates a response without storing it', function () {
    $body = ($this->decode)($this->client->createResponse([
        'model' => getenv('OPENAI_CHAT_MODEL') ?: 'gpt-4o-mini',
        'input' => 'Say hello',
        'store' => false,
    ]));

    expect($body['object'])->toBe('response');
    expect($body['status'])->toBe('completed');
    expect($body['output'])->toBeArray()->not->toBeEmpty();
})->group('live', 'working');

it('creates and deletes its own conversation', function () {
    $created = ($this->decode)($this->client->createConversation(['metadata' => ['test' => 'openai-php']]));
    $id = $created['id'];

    try {
        $retrieved = ($this->decode)($this->client->retrieveConversation($id));
        expect($retrieved['id'])->toBe($id);
    } finally {
        $deleted = ($this->decode)($this->client->deleteConversation($id));
        expect($deleted['deleted'])->toBeTrue();
    }
})->group('live', 'working');

it('stores retrieves paginates and deletes a response', function () {
    $response = ($this->decode)($this->client->createResponse([
        'model' => getenv('OPENAI_CHAT_MODEL') ?: 'gpt-4o-mini',
        'input' => 'Reply with hello.',
        'store' => true,
        'max_output_tokens' => 32,
    ]));
    $id = $response['id'];

    try {
        $retrieved = ($this->decode)($this->client->retrieveResponse($id, ['include' => ['message.output_text.logprobs']]));
        expect($retrieved['id'])->toBe($id);
        $items = ($this->decode)($this->client->listResponseInputItems($id, ['limit' => 1, 'order' => 'asc']));
        expect($items['data'])->toHaveCount(1);
        expect($items['data'][0]['role'])->toBe('user');
    } finally {
        $deleted = ($this->decode)($this->client->deleteResponse($id));
        expect($deleted['deleted'])->toBeTrue();
    }
})->group('live');

it('counts response input tokens', function () {
    $result = ($this->decode)($this->client->countResponseInputTokens([
        'model' => getenv('OPENAI_CHAT_MODEL') ?: 'gpt-4o-mini',
        'input' => 'Hello world.',
    ]));
    expect($result['object'])->toBe('response.input_tokens');
    expect($result['input_tokens'])->toBeInt()->toBeGreaterThan(0);
})->group('live');

it('streams and resumes a stored background response', function () {
    $chunks = '';
    $callback = function ($curl, $data) use (&$chunks) {
        $chunks .= $data;

        return strlen($data);
    };
    $this->client->createResponse([
        'model' => getenv('OPENAI_CHAT_MODEL') ?: 'gpt-4o-mini',
        'input' => 'Reply with hello.',
        'background' => true,
        'store' => true,
        'stream' => true,
        'max_output_tokens' => 32,
    ], $callback);
    expect($this->client->getCURLInfo()['http_code'])->toBe(200);

    $events = [];
    foreach (preg_split('/\r?\n/', $chunks) as $line) {
        if (strpos($line, 'data: ') === 0 && substr($line, 6) !== '[DONE]') {
            $events[] = json_decode(substr($line, 6), true, 512, JSON_THROW_ON_ERROR);
        }
    }
    $id = $events[0]['response']['id'] ?? null;
    expect($id)->toBeString();

    try {
        expect(array_column($events, 'type'))->toContain('response.completed');
        $chunks = '';
        $this->client->retrieveResponse($id, ['stream' => true, 'starting_after' => 0], $callback);
        expect($this->client->getCURLInfo()['http_code'])->toBe(200);
        expect($chunks)->toContain('response.completed');
        // A later JSON request on this same client must not reuse the streaming callback.
        $retrieved = ($this->decode)($this->client->retrieveResponse($id));
        expect($retrieved['status'])->toBe('completed');
    } finally {
        $this->client->deleteResponse($id);
    }
})->group('live');

it('updates a conversation and manages its own items', function () {
    $conversation = ($this->decode)($this->client->createConversation());
    $id = $conversation['id'];

    try {
        $updated = ($this->decode)($this->client->updateConversation($id, ['metadata' => ['test' => 'items']]));
        expect($updated['metadata']['test'])->toBe('items');
        $created = ($this->decode)($this->client->createConversationItem($id, [
            'items' => [['type' => 'message', 'role' => 'user', 'content' => [
                ['type' => 'input_text', 'text' => 'Hello from the PHP integration test.'],
            ]]],
        ]));
        $itemId = $created['data'][0]['id'];
        $listed = ($this->decode)($this->client->listConversationItems($id, ['limit' => 1, 'order' => 'asc']));
        expect($listed['data'][0]['id'])->toBe($itemId);
        $item = ($this->decode)($this->client->retrieveConversationItem($id, $itemId, ['include' => ['message.input_image.image_url']]));
        expect($item['id'])->toBe($itemId);
        ($this->decode)($this->client->deleteConversationItem($id, $itemId));
        $remaining = ($this->decode)($this->client->listConversationItems($id));
        expect($remaining['data'])->toBeEmpty();
    } finally {
        $this->client->deleteConversation($id);
    }
})->group('live');

it('stores and manages its own chat completion', function () {
    $tag = bin2hex(random_bytes(8));
    $chat = ($this->decode)($this->client->chat([
        'model' => getenv('OPENAI_CHAT_MODEL') ?: 'gpt-4o-mini',
        'messages' => [['role' => 'user', 'content' => 'Reply with hello.']],
        'store' => true,
        'metadata' => ['test' => $tag],
        'max_completion_tokens' => 32,
    ]));
    $id = $chat['id'];

    try {
        $deadline = time() + 30;
        do {
            $raw = ($this->retry)(fn () => $this->client->retrieveChatCompletion($id));
            if ($this->client->getCURLInfo()['http_code'] !== 404) {
                break;
            }
            usleep(500000);
        } while (time() < $deadline);
        $retrieved = ($this->decode)($raw);
        expect($retrieved['id'])->toBe($id);
        $updated = ($this->decode)(($this->retry)(fn () => $this->client->updateChatCompletion($id, ['metadata' => ['test' => $tag, 'updated' => 'yes']])));
        expect($updated['metadata']['updated'])->toBe('yes');
        $listed = ($this->decode)(($this->retry)(fn () => $this->client->listChatCompletions(['limit' => 1, 'order' => 'desc', 'metadata' => ['test' => $tag]])));
        expect($listed['data'][0]['id'])->toBe($id);
        $messages = ($this->decode)($this->client->listChatCompletionMessages($id, ['limit' => 1]));
        expect($messages['data'])->toHaveCount(1);
    } finally {
        $cleanup = $this->client->deleteChatCompletion($id);
        if ($this->client->getCURLInfo()['http_code'] !== 404) {
            $deleted = ($this->decode)($cleanup);
            expect($deleted['deleted'])->toBeTrue();
        }
    }
})->group('live');

it('uploads real multipart parts completes and cancels uploads', function () {
    $content = "{\"messages\":[{\"role\":\"user\",\"content\":\"Hello\"},{\"role\":\"assistant\",\"content\":\"Hi\"}]}\n";
    $path = tempnam(sys_get_temp_dir(), 'openai_part_');
    file_put_contents($path, $content);
    $uploadId = null;
    $fileId = null;
    $options = ['filename' => 'integration-upload.jsonl', 'purpose' => 'fine-tune', 'bytes' => strlen($content), 'mime_type' => 'application/jsonl'];

    try {
        $upload = ($this->decode)($this->client->createUpload($options));
        $uploadId = $upload['id'];
        $part = ($this->decode)($this->client->addUploadPart($uploadId, [
            'data' => new CURLFile($path, 'application/octet-stream', 'part.bin'),
        ]));
        expect($part['object'])->toBe('upload.part');
        $completed = ($this->decode)($this->client->completeUpload($uploadId, ['part_ids' => [$part['id']]]));
        $fileId = $completed['file']['id'];
        $uploadId = null;
        expect($completed['status'])->toBe('completed');
        expect($this->client->retrieveFileContent($fileId))->toBe($content);
        $cancelledUpload = ($this->decode)($this->client->createUpload($options));
        $uploadId = $cancelledUpload['id'];
        $cancelled = ($this->decode)($this->client->cancelUpload($uploadId));
        expect($cancelled['status'])->toBe('cancelled');
        $uploadId = null;
    } finally {
        unlink($path);
        if ($uploadId !== null) {
            $this->client->cancelUpload($uploadId);
        }
        if ($fileId !== null) {
            $this->client->deleteFile($fileId);
        }
    }
})->group('live');

it('indexes searches and removes its own vector store file', function () {
    $path = tempnam(sys_get_temp_dir(), 'openai_vector_');
    file_put_contents($path, "The support desk opens at nine every morning. Its mascot is a purple owl.\n");
    $storeId = null;
    $fileId = null;

    try {
        $file = ($this->decode)($this->client->uploadFile(['purpose' => 'assistants', 'file' => new CURLFile($path, 'text/plain', 'support.txt')]));
        $fileId = $file['id'];
        $store = ($this->decode)($this->client->createVectorStore());
        $storeId = $store['id'];
        $updated = ($this->decode)($this->client->updateVectorStore($storeId, ['name' => 'PHP integration test']));
        expect($updated['name'])->toBe('PHP integration test');
        $retrieved = ($this->decode)($this->client->retrieveVectorStore($storeId));
        expect($retrieved['id'])->toBe($storeId);
        ($this->decode)($this->client->createVectorStoreFile($storeId, ['file_id' => $fileId]));
        $deadline = time() + 30;
        do {
            $indexed = ($this->decode)($this->client->retrieveVectorStoreFile($storeId, $fileId));
            if ($indexed['status'] !== 'in_progress') {
                break;
            }
            usleep(250000);
        } while (time() < $deadline);
        expect($indexed['status'])->toBe('completed');
        $attributes = ($this->decode)($this->client->updateVectorStoreFile($storeId, $fileId, ['attributes' => ['category' => 'support']]));
        expect($attributes['attributes']['category'])->toBe('support');
        $listed = ($this->decode)($this->client->listVectorStoreFiles($storeId, ['limit' => 1, 'filter' => 'completed']));
        expect($listed['data'][0]['id'])->toBe($fileId);
        $content = ($this->decode)($this->client->retrieveVectorStoreFileContent($storeId, $fileId, ['limit' => 1]));
        expect($content['data'])->not->toBeEmpty();
        $search = ($this->decode)($this->client->searchVectorStore($storeId, ['query' => 'What is the support desk mascot?', 'max_num_results' => 1]));
        expect($search['data'][0]['file_id'])->toBe($fileId);
        $deleted = ($this->decode)($this->client->deleteVectorStoreFile($storeId, $fileId));
        expect($deleted['deleted'])->toBeTrue();
    } finally {
        unlink($path);
        if ($storeId !== null) {
            ($this->decode)($this->client->deleteVectorStore($storeId));
        }
        if ($fileId !== null) {
            ($this->decode)($this->client->deleteFile($fileId));
        }
    }
})->group('live');

it('compacts a response using a configured supported model', function () {
    $model = getenv('OPENAI_COMPACTION_MODEL') ?: 'gpt-6.1-sol';
    $result = ($this->decode)($this->client->compactResponse([
        'model' => $model,
        'input' => [['role' => 'user', 'content' => 'Remember that the support desk mascot is a purple owl.']],
    ]));
    expect($result['object'])->toBe('response.compaction');
    expect($result['output'])->not->toBeEmpty();
})->group('live');

it('evaluates a decision using a configured beta model', function () {
    $model = getenv('OPENAI_DECISION_MODEL') ?: 'gpt-6-luna';
    $result = ($this->decode)($this->client->createDecision([
        'model' => $model,
        'input' => 'The support desk mascot is a purple owl.',
        'questions' => [['type' => 'predicate', 'name' => 'owl', 'instructions' => 'The mascot is an owl.']],
    ]));
    expect($result['answers'][0]['type'])->toBe('predicate');
    expect($result['answers'][0]['name'])->toBe('owl');
    expect($result['answers'][0]['probability'])->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(1);
})->group('live');

it('creates a real short-lived Realtime client secret', function () {
    $result = ($this->decode)($this->client->createRealtimeClientSecret([
        'session' => ['type' => 'realtime', 'model' => getenv('OPENAI_REALTIME_MODEL') ?: 'gpt-realtime-2.1-mini'],
        'expires_after' => ['anchor' => 'created_at', 'seconds' => 10],
    ]));
    // Never print or snapshot the secret.
    expect(isset($result['value']) && is_string($result['value']) && $result['value'] !== '')->toBeTrue();
    expect($result['expires_at'])->toBeInt()->toBeGreaterThan(time());
})->group('live');

it('validates and runs a real fine-tuning grader', function () {
    $grader = ['type' => 'string_check', 'name' => 'exact match', 'operation' => 'eq', 'input' => '{{sample.output_text}}', 'reference' => 'hello'];
    $validated = ($this->decode)($this->client->validateFineTuningGrader(['grader' => $grader]));
    expect($validated['grader']['type'])->toBe('string_check');
    $result = ($this->decode)($this->client->runFineTuningGrader(['grader' => $grader, 'model_sample' => 'hello']));
    expect((float) $result['reward'])->toBe(1.0);
})->group('live');

it('lists real webhook endpoints', function () {
    $result = ($this->decode)($this->client->listWebhookEndpoints(['limit' => 1]));
    expect($result['data'])->toBeArray();
})->group('live');

it('lists real voice consent recordings', function () {
    if (! getenv('OPENAI_TEST_CUSTOM_VOICES')) {
        $this->markTestSkipped('Set OPENAI_TEST_CUSTOM_VOICES=1 when custom voices are enabled for your organization.');
    }
    $result = ($this->decode)($this->client->listVoiceConsents(['limit' => 1]));
    expect($result['data'])->toBeArray();
})->group('live');

it('cancels its own background response', function () {
    $created = ($this->decode)($this->client->createResponse([
        'model' => getenv('OPENAI_CHAT_MODEL') ?: 'gpt-4o-mini',
        'input' => 'Write a short paragraph about owls.',
        'background' => true,
        'store' => true,
        'max_output_tokens' => 64,
    ]));
    $id = $created['id'];

    try {
        $cancelled = ($this->decode)($this->client->cancelResponse($id));
        expect($cancelled['id'])->toBe($id);
        expect($cancelled['status'])->toBeIn(['cancelled', 'completed']);
    } finally {
        $this->client->deleteResponse($id);
    }
})->group('live');

it('creates retrieves and cancels its own batch', function () {
    $path = tempnam(sys_get_temp_dir(), 'openai_batch_');
    file_put_contents($path, json_encode([
        'custom_id' => 'php-integration-test',
        'method' => 'POST',
        'url' => '/v1/embeddings',
        'body' => ['model' => 'text-embedding-3-small', 'input' => 'Hello'],
    ], JSON_THROW_ON_ERROR) . "\n");
    $fileId = null;
    $batchId = null;

    try {
        $file = ($this->decode)($this->client->uploadFile(['purpose' => 'batch', 'file' => new CURLFile($path, 'application/jsonl', 'requests.jsonl')]));
        $fileId = $file['id'];
        $batch = ($this->decode)($this->client->createBatch(['input_file_id' => $fileId, 'endpoint' => '/v1/embeddings', 'completion_window' => '24h']));
        $batchId = $batch['id'];
        $retrieved = ($this->decode)($this->client->retrieveBatch($batchId));
        expect($retrieved['id'])->toBe($batchId);
        $cancelled = ($this->decode)($this->client->cancelBatch($batchId));
        expect($cancelled['status'])->toBeIn(['cancelling', 'cancelled']);
        $batchId = null;
    } finally {
        unlink($path);
        if ($batchId !== null) {
            $this->client->cancelBatch($batchId);
        }
        if ($fileId !== null) {
            $this->client->deleteFile($fileId);
        }
    }
})->group('live');

it('creates retrieves lists and cancels its own vector store file batch', function () {
    $path = tempnam(sys_get_temp_dir(), 'openai_vector_batch_');
    file_put_contents($path, "A purple owl is the mascot.\n");
    $fileId = null;
    $storeId = null;

    try {
        $file = ($this->decode)($this->client->uploadFile(['purpose' => 'assistants', 'file' => new CURLFile($path, 'text/plain', 'mascot.txt')]));
        $fileId = $file['id'];
        $store = ($this->decode)($this->client->createVectorStore());
        $storeId = $store['id'];
        $batch = ($this->decode)($this->client->createVectorStoreFileBatch($storeId, ['file_ids' => [$fileId]]));
        // Cancel promptly; waiting for indexing can race with batch completion.
        $cancelled = ($this->decode)($this->client->cancelVectorStoreFileBatch($storeId, $batch['id']));
        expect($cancelled['id'])->toBe($batch['id']);
        $retrieved = ($this->decode)($this->client->retrieveVectorStoreFileBatch($storeId, $batch['id']));
        expect($retrieved['id'])->toBe($batch['id']);
        $files = ($this->decode)($this->client->listVectorStoreFileBatchFiles($storeId, $batch['id'], ['limit' => 1]));
        expect($files['data'])->toBeArray();
    } finally {
        unlink($path);
        if ($storeId !== null) {
            $this->client->deleteVectorStore($storeId);
        }
        if ($fileId !== null) {
            $this->client->deleteFile($fileId);
        }
    }
})->group('live');

it('reaches endpoints through the generic request method', function () {
    $models = ($this->decode)($this->client->request('GET', '/models', [
        'limit' => 5,
        'include' => ['a', 'b'],
    ]));
    expect($models['data'])->toBeArray()->not->toBeEmpty();
    expect($this->client->getCURLInfo()['url'])->toContain('?limit=5&include%5B%5D=a&include%5B%5D=b');

    $moderation = ($this->decode)($this->client->request('POST', '/moderations', [
        'model' => 'omni-moderation-latest',
        'input' => 'Hello',
    ]));
    expect($moderation['results'])->toBeArray()->not->toBeEmpty();
})->group('live', 'working');

it('lists agent sessions and environments', function () {
    foreach (['listAgentSessions', 'listAgentEnvironments'] as $method) {
        $body = ($this->decode)($this->client->$method(['limit' => 1]));
        expect($body['object'])->toBe('list');
        expect($body['data'])->toBeArray();
    }
})->group('live', 'working');

it('creates retrieves updates and deletes its own idle agent session', function () {
    $sessionId = null;
    $tag = 'openai-php-agent-' . bin2hex(random_bytes(8));
    $artifacts = getenv('OPENAI_TEST_ARTIFACT_DIR') ?: dirname(__DIR__) . '/build/logs';
    if (! is_dir($artifacts) && ! mkdir($artifacts, 0700, true) && ! is_dir($artifacts)) {
        throw new RuntimeException('Cannot create the live test artifact directory.');
    }
    $journal = $artifacts . '/' . $tag . '.json';
    $record = ['test' => 'openai-php-agent-session', 'run' => $tag, 'php' => PHP_VERSION, 'session_id' => null, 'deleted' => false];
    $writeJournal = function () use (&$record, $journal) {
        if (file_put_contents($journal, json_encode($record, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), LOCK_EX) === false) {
            throw new RuntimeException('Cannot write the live test resource journal: ' . $journal);
        }
    };
    $writeJournal();

    $deleteOwnSession = function (string $id) {
        $deadline = microtime(true) + 60;
        $delay = 1;
        while (true) {
            $raw = $this->client->deleteAgentSession($id);
            $status = $this->client->getCURLInfo()['http_code'];
            $body = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            $message = $body['error']['message'] ?? '';
            // Hosted resources may still be provisioning after the session is created.
            $provisioning = $status === 409 && is_string($message)
                && strpos($message, 'provisioning or resource creation has not settled') !== false;
            if (! $provisioning || microtime(true) >= $deadline) {
                $deleted = ($this->decode)($raw);
                expect($deleted['id'])->toBe($id);
                expect($deleted['deleted'])->toBeTrue();

                return $deleted;
            }

            // The Agents error guide recommends retrieving current state before retrying a conflict.
            $current = ($this->decode)($this->client->retrieveAgentSession($id));
            expect($current['id'])->toBe($id);
            $wait = min($delay, max(0, $deadline - microtime(true)));
            if ($wait > 0) {
                usleep((int) ($wait * 1000000));
            }
            $delay = min($delay * 2, 5);
        }
    };
    $failure = null;
    $this->client->setHeader(['OpenAI-Beta' => 'responses_multi_agent=v1']);
    $this->client->setCURLOptions([CURLINFO_HEADER_OUT => true]);

    try {
        $raw = $this->client->createAgentSession([
            'agent' => ['model' => getenv('OPENAI_AGENT_MODEL') ?: 'gpt-6-astra'],
            'environment' => ['type' => 'openai_hosted'],
            'metadata' => ['test' => 'openai-php-agent-session', 'run' => $tag],
        ]);
        $created = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (isset($created['id']) && is_string($created['id'])) {
            $sessionId = $created['id'];
            $record['session_id'] = $sessionId;
            $writeJournal();
        }
        $session = ($this->decode)($raw);
        $sessionId = $session['id'];
        expect($session['object'])->toBe('agent.session');
        expect($session['metadata']['run'])->toBe($tag);

        preg_match('/^OpenAI-Beta:\s*([^\r\n]+)/mi', $this->client->getCURLInfo()['request_header'], $sentBeta);
        expect($sentBeta[1] ?? null)->toBe('agents=v1');

        $retrieved = ($this->decode)($this->client->retrieveAgentSession($sessionId));
        expect($retrieved['id'])->toBe($sessionId);

        $updated = ($this->decode)($this->client->updateAgentSession($sessionId, [
            'metadata' => ['test' => 'openai-php-agent-session', 'run' => $tag, 'updated' => 'yes'],
        ]));
        expect($updated['id'])->toBe($sessionId);
        expect($updated['metadata']['updated'])->toBe('yes');

        $items = ($this->decode)($this->client->listAgentSessionItems($sessionId, ['limit' => 1]));
        expect($items['data'])->toBeArray();
        $turns = ($this->decode)($this->client->listAgentSessionTurns($sessionId, ['limit' => 1]));
        expect($turns['data'])->toBeArray();

        $deleteOwnSession($sessionId);
        $sessionId = null;
        $record['deleted'] = true;
        $writeJournal();

        ($this->decode)($this->client->listModels());
        preg_match('/^OpenAI-Beta:\s*([^\r\n]+)/mi', $this->client->getCURLInfo()['request_header'], $restoredBeta);
        expect($restoredBeta[1] ?? null)->toBe('responses_multi_agent=v1');
    } catch (Throwable $error) {
        $failure = $error;

        throw $error;
    } finally {
        if ($sessionId !== null) {
            try {
                $deleteOwnSession($sessionId);
                $record['deleted'] = true;
                $writeJournal();
            } catch (Throwable $cleanupError) {
                $message = 'Cleanup failed for agent session ' . $sessionId . '; resource journal: ' . $journal
                    . '. Cleanup error: ' . $cleanupError->getMessage();
                if ($failure !== null) {
                    $message .= '. Original test error: ' . $failure->getMessage();
                }

                throw new RuntimeException($message, 0, $failure ?? $cleanupError);
            }
        }
    }
})->group('live', 'working');

it('provides response metadata and throws OpenAiException on error in real calls', function () {
    $this->client->listModels();
    $response = $this->client->getLastResponse();

    expect($response)->toBeInstanceOf(\Orhanerday\OpenAi\Response::class);
    expect($response->ok())->toBeTrue();
    expect($response->status())->toBe(200);
    expect($response->header('content-type'))->toContain('application/json');
    expect($response->json()['data'])->toBeArray();

    // Now test setThrowOnError(true) with an intentional 404
    $this->client->setThrowOnError(true);

    try {
        $this->client->retrieveModel('model-does-not-exist-' . bin2hex(random_bytes(4)));
        $this->fail('Expected OpenAiException');
    } catch (\Orhanerday\OpenAi\OpenAiException $e) {
        expect($e->getStatusCode())->toBe(404);
        expect($e->getErrorType())->toBe('invalid_request_error');
        expect($e->getApiError())->toBeArray();
        $failedResponse = $this->client->getLastResponse();
        expect($failedResponse->status())->toBe(404);
        expect($failedResponse->ok())->toBeFalse();
        expect($failedResponse->body())->toBe($e->getResponseBody());
        expect($failedResponse->json()['error'])->toBe($e->getApiError());
    } finally {
        $this->client->setThrowOnError(false);
    }
})->group('live', 'working');

it('collects response headers while forwarding them to a custom cURL header callback', function () {
    $receivedHeaders = [];
    $this->client->setCURLOptions([
        CURLOPT_HEADERFUNCTION => function ($curl, $line) use (&$receivedHeaders) {
            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $receivedHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
            }

            return strlen($line);
        },
    ]);

    ($this->decode)($this->client->listModels());
    $response = $this->client->getLastResponse();
    expect($receivedHeaders['content-type'] ?? '')->toContain('application/json');
    expect($response->header('content-type'))->toBe($receivedHeaders['content-type']);
    expect($response->header('x-request-id'))->toBeString()->not->toBeEmpty();
    expect($response->header('x-request-id'))->toBe($receivedHeaders['x-request-id']);
})->group('live', 'working');

it('preserves HTTP error bodies and callback bytes when a streaming request fails', function () {
    $received = '';
    $this->client->setThrowOnError(true);

    try {
        $this->client->chat([
            'model' => 'model-does-not-exist-' . bin2hex(random_bytes(4)),
            'messages' => [['role' => 'user', 'content' => 'Hello']],
            'stream' => true,
        ], function ($curl, $chunk) use (&$received) {
            $received .= $chunk;

            return strlen($chunk);
        });
        $this->fail('Expected OpenAiException');
    } catch (\Orhanerday\OpenAi\OpenAiException $e) {
        expect($e->getStatusCode())->toBe(404);
        expect($e->getErrorType())->toBe('invalid_request_error');
        expect($e->getApiError())->toBeArray();
        expect($e->getResponseBody())->not->toBeEmpty();
        expect($received)->toBe($e->getResponseBody());
        expect(json_decode($received, true, 512, JSON_THROW_ON_ERROR)['error'])->toBe($e->getApiError());
        $response = $this->client->getLastResponse();
        expect($response->status())->toBe(404);
        expect($response->ok())->toBeFalse();
        expect($response->body())->toBe($e->getResponseBody());
    } finally {
        $this->client->setThrowOnError(false);
    }
})->group('live', 'working');

it('honors a streaming callback that stops the transfer with a short byte count', function () {
    $called = false;

    try {
        $this->client->chat([
            'model' => 'model-does-not-exist-' . bin2hex(random_bytes(4)),
            'messages' => [['role' => 'user', 'content' => 'Hello']],
            'stream' => true,
        ], function ($curl, $chunk) use (&$called) {
            $called = true;

            return 0;
        });
        $this->fail('Expected the callback to stop the cURL transfer.');
    } catch (Exception $e) {
        expect($e->getCode())->toBe(CURLE_WRITE_ERROR);
        expect($called)->toBeTrue();
    }
})->group('live', 'working');
