<?php


use Orhanerday\OpenAi\OpenAi;

$open_ai = new OpenAi(getenv('OPENAI_API_KEY'));

it('should handle simple completion using the new endpoint', function () use ($open_ai) {
    $result = $open_ai->completion([
        'prompt' => "Hello",
        'temperature' => 0.9,
        "max_tokens" => 150,
        "frequency_penalty" => 0,
        "presence_penalty" => 0.6,
    ]);

    $this->assertStringContainsString('text', $result);
})->group('working');

it('should throw error when set the stream to true and does not set the stream method', function () use ($open_ai) {
    expect(fn () => $open_ai->completion([
        'prompt' => "Hello",
        'temperature' => 0.9,
        "max_tokens" => 150,
        "frequency_penalty" => 0,
        "presence_penalty" => 0.6,
        "stream" => true
    ]))->toThrow(Exception::class, 'Please provide a stream function. Check https://github.com/orhanerday/open-ai#stream-example for an example.');
})->group('working');

it('should handle simple completion using the deprecated endpoint', function () use ($open_ai) {
    $result = $open_ai->complete([
        'engine' => 'davinci',
        'prompt' => "Hello",
        'temperature' => 0.9,
        "max_tokens" => 150,
        "frequency_penalty" => 0,
        "presence_penalty" => 0.6,
    ]);

    $this->assertStringContainsString('text', $result);
})->group('working');


it('should handle search', function () use ($open_ai) {
    $result = $open_ai->search([
        'engine' => 'ada',
        'documents' => ["White House", "hospital", "school"],
        'query' => "the president",
    ]);
    $this->assertStringContainsString('document', $result);
})->group('deprecated');

it('should handle content filtering', function () use ($open_ai) {
    $result = $open_ai->moderation([
        'input' => "I want to kill them.",
    ]);
    $this->assertStringContainsString('results', $result);
})->group('working');


it('should handle file upload', function () use ($open_ai) {
    $c_file = curl_file_create(__DIR__ . '/../files/sample_file_1.jsonl');

    $result = $open_ai->uploadFile([
        "purpose" => "answers",
        "file" => $c_file,
    ]);

    $this->assertStringContainsString('filename', $result);
})->group('working');

it('should handle list files', function () use ($open_ai) {
    $result = $open_ai->listFiles();

    $this->assertStringContainsString('data', $result);
})->group('working');

it('should handle retrieve the file', function () use ($open_ai) {
    $result = $open_ai->retrieveFile('file-hrlLUmPhddnplQUVa2CTzg5k');

    $this->assertStringContainsString('filename', $result);
})->group('requires-missing-file');

it('should handle retrieve the file content', function () use ($open_ai) {
    $result = $open_ai->retrieveFileContent('file-hrlLUmPhddnplQUVa2CTzg5k');

    $this->assertStringContainsString('', $result);
})->group('requires-missing-file');

it('should handle delete the file', function () use ($open_ai) {
    $result = $open_ai->deleteFile('file-hrlLUmPhddnplQUVa2CTzg5k');

    $this->assertStringContainsString('deleted', $result);
})->group('requires-missing-file');

it('should handle creating fine-tune', function () use ($open_ai) {
    $result = $open_ai->createFineTune([
        "training_file" => "file-U3KoAAtGsjUKSPXwEUDdtw86",
    ]);

    $this->assertStringContainsString('training_files', $result);
})->group('requires-missing-file');

it('should handle listing fine-tunes', function () use ($open_ai) {
    $result = $open_ai->listFineTunes();

    $this->assertStringContainsString('data', $result);
})->group('working');

it('should handle retrieve the fine-tune', function () use ($open_ai) {
    $result = $open_ai->retrieveFineTune('file-XGinujblHPwGLSztz8cPS8XY');

    $this->assertStringContainsString('training_files', $result);
})->group('requires-missing-file');

it('should handle cancel the fine-tune', function () use ($open_ai) {
    $result = $open_ai->cancelFineTune('file-XGinujblHPwGLSztz8cPS8XY');

    $this->assertStringContainsString('training_files', $result);
})->group('requires-missing-file');;

it('should handle list fine-tune event', function () use ($open_ai) {
    $result = $open_ai->listFineTuneEvents('file-XGinujblHPwGLSztz8cPS8XY');

    $this->assertStringContainsString('data', $result);
})->group('requires-missing-file');;

it('should handle delete fine-tune model', function () use ($open_ai) {
    $result = $open_ai->deleteFineTune('curie:ft-acmeco-2021-03-03-21-44-20');

    $this->assertStringContainsString('deleted', $result);
})->group('requires-missing-file');;

it('should handle answers', function () use ($open_ai) {
    $result = $open_ai->answer([
        "documents" => ["Puppy A is happy.", "Puppy B is sad."],
        "question" => "which puppy is happy?",
        "search_model" => "ada",
        "model" => "curie",
        "examples_context" => "In 2017, U.S. life expectancy was 78.6 years.",
        "examples" => [["What is human life expectancy in the United States?", "78 years."]],
        "max_tokens" => 5,
        "stop" => ["\n", "<|endoftext|>"],
    ]);

    $this->assertStringContainsString('selected_documents', $result);
})->group('deprecated');

it('should handle classification', function () use ($open_ai) {
    $result = $open_ai->classification([
        'examples' => [
            ["A happy moment", "Positive"],
            ["I am sad.", "Negative"],
            ["I am feeling awesome", "Positive"],
        ],
        'labels' => ["Positive", "Negative", "Neutral"],
        'query' => "It is a raining day =>(",
        'search_model' => "ada",
        'model' => "curie",
    ]);

    $this->assertStringContainsString('selected_examples', $result);
})->group('deprecated');

it('should handle engines', function () use ($open_ai) {
    $result = $open_ai->engines();
    $this->assertStringContainsString('data', $result);
})->group('deprecated');


it('should handle engine', function () use ($open_ai) {
    $engine_id = 'davinci';
    $result = $open_ai->engine($engine_id);
    $this->assertStringContainsString($engine_id, $result);
})->group('deprecated');

it('should handle image', function () use ($open_ai) {
    $result = $open_ai->image([
      'prompt' => "a picture of a cat",
      'n' => 1,
      'size' => "256x256",
    ]);
    $this->assertStringContainsString('data', $result);
})->group('working');

it('should handle List models', function () use ($open_ai) {
    $result = $open_ai->listModels();
    $this->assertStringContainsString('owned_by', $result);
})->group('working');

it('should handle Retrieve given model', function () use ($open_ai) {
    $result = $open_ai->retrieveModel("text-ada-001");
    $this->assertStringContainsString('owned_by', $result);
})->group('working');

it('should handle create edit', function () use ($open_ai) {
    $result = $open_ai->createEdit([
        "model" => "text-davinci-edit-001",
        "input" => "What day of the wek is it?",
        "instruction" => "Fix the spelling mistakes",
    ]);
    $this->assertStringContainsString('created', $result);
})->group('working');

it('should handle image edit', function () use ($open_ai) {
    $otter = curl_file_create(__DIR__ . './files/otter.png');
    $mask = curl_file_create(__DIR__ . './files/mask.jpg');

    $result = $open_ai->imageEdit([
        "image" => $otter,
        "mask" => $mask,
        "prompt" => "A cute baby sea otter wearing a beret",
        "n" => 2,
        "size" => "1024x1024",
    ]);
    $this->assertStringContainsString('created', $result);
})->group('requires-missing-file');

it('should handle image variation', function () use ($open_ai) {
    $otter = curl_file_create(__DIR__ . './files/otter.png');
    $result = $open_ai->createImageVariation([
        "image" => $otter,
        "n" => 2,
        "size" => "256x256",
    ]);
    $this->assertStringContainsString('created', $result);
})->group('requires-missing-file');

it('should handle Create embeddings', function () use ($open_ai) {
    $result = $open_ai->embeddings([
        "model" => "text-similarity-babbage-001",
        "input" => "The food was delicious and the waiter...",
    ]);

    $this->assertStringContainsString('data', $result);
})->group('working');


it('should handle simple chat completion using the new endpoint', function () use ($open_ai) {
    $result = $open_ai->chat([
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            [
                "role" => "user",
                "content" => "Hello!"
            ]
        ],
        'temperature' => 0.9,
        "max_tokens" => 150,
        "frequency_penalty" => 0,
        "presence_penalty" => 0,
    ]);

    $this->assertStringContainsString('text', $result);
})->group('working');

it('should handle create a assistant', function () use ($open_ai) {
    $assistant = $open_ai->createAssistant([
        'model' => 'gpt-3.5-turbo',
        'name' => 'my assistant',
        'description' => 'my assistant description',
        'instructions' => 'you should cordially help me',
        'tools' => [],
        'file_ids' => [],
    ]);

    $this->assertStringContainsString('id', $assistant);
    $this->assertStringContainsString('"object": "assistant"', $assistant);
    $this->assertStringContainsString('"name": "my assistant"', $assistant);
})->group('working');

it('should handle retrieve a assistant', function () use ($open_ai) {
    $assistant = $open_ai->retrieveAssistant('asst_rzJqKRfpQI2JMj1gYGKQOZZo');

    $this->assertStringContainsString('id', $assistant);
    $this->assertStringContainsString('"object": "assistant"', $assistant);
    $this->assertStringContainsString('"name": "my assistant"', $assistant);
})->group('working');

it('should handle modify a assistant', function () use ($open_ai) {
    $assistant = $open_ai->modifyAssistant('asst_rzJqKRfpQI2JMj1gYGKQOZZo', [
        'name' => 'my modified assistant',
        'instructions' => 'you should cordially help me again',
    ]);

    $this->assertStringContainsString('id', $assistant);
    $this->assertStringContainsString('"object": "assistant"', $assistant);
    $this->assertStringContainsString('"name": "my modified assistant"', $assistant);
})->group('working');

it('should handle delete a assistant', function () use ($open_ai) {
    $assistant = $open_ai->deleteAssistant('asst_rzJqKRfpQI2JMj1gYGKQOZZo');

    $this->assertStringContainsString('id', $assistant);
    $this->assertStringContainsString('"deleted": true', $assistant);
})->group('working');

it('should handle list assistants', function () use ($open_ai) {
    $query = ['limit' => 10];
    $assistants = $open_ai->listAssistants($query);

    $this->assertStringContainsString('"object": "list"', $assistants);
    $this->assertStringContainsString('data', $assistants);
    $this->assertStringContainsString('id', $assistants);
})->group('working');

it('should handle create a assistant file', function () use ($open_ai) {
    $upload = curl_file_create(__DIR__ . '/../files/assistant-file.txt');
    $file = json_decode($open_ai->uploadFile([
        'purpose' => 'assistants',
        'file' => $upload,
    ]), true);
    $assistantId = 'asst_zT1LLZ8dWnuFCrMFzqxFOhzz';

    $assistantFile = $open_ai->createAssistantFile($assistantId, $file['id']);

    $this->assertStringContainsString('id', $assistantFile);
    $this->assertStringContainsString('"object": "assistant.file"', $assistantFile);
})->group('working');

it('should handle retrieve a assistant file', function () use ($open_ai) {
    $assistantId = 'asst_zT1LLZ8dWnuFCrMFzqxFOhzz';
    $fileId = 'file-CRLcY63DiHphWuBrmDWZVCgA';

    $assistantFile = $open_ai->retrieveAssistantFile($assistantId, $fileId);

    $this->assertStringContainsString('id', $assistantFile);
    $this->assertStringContainsString('"object": "assistant.file"', $assistantFile);
})->group('working');

it('should handle list assistant files', function () use ($open_ai) {
    $assistantId = 'asst_zT1LLZ8dWnuFCrMFzqxFOhzz';
    $query = ['limit' => 10];

    $files = $open_ai->listAssistantFiles($assistantId, $query);

    $this->assertStringContainsString('"object": "list"', $files);
    $this->assertStringContainsString('data', $files);
    $this->assertStringContainsString('id', $files);
})->group('working');

it('should handle delete a assistant file', function () use ($open_ai) {
    $assistantId = 'asst_zT1LLZ8dWnuFCrMFzqxFOhzz';
    $fileId = 'file-CRLcY63DiHphWuBrmDWZVCgA';

    $file = $open_ai->deleteAssistantFile($assistantId, $fileId);

    $this->assertStringContainsString('id', $file);
    $this->assertStringContainsString('"deleted": true', $file);
})->group('working');

it('should handle create a thread', function () use ($open_ai) {
    $thread = $open_ai->createThread([
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Hello, what is AI?',
                'file_ids' => [],
            ],
        ],
    ]);

    $this->assertStringContainsString('id', $thread);
    $this->assertStringContainsString('"object": "thread"', $thread);
})->group('working');

it('should handle retrieve a thread', function () use ($open_ai) {
    $thread = $open_ai->retrieveThread('thread_GHGU1zEOVD5z2EXs3e8zU55S');

    $this->assertStringContainsString('id', $thread);
    $this->assertStringContainsString('"object": "thread"', $thread);
})->group('working');

it('should handle modify a thread', function () use ($open_ai) {
    $thread = $open_ai->modifyThread('thread_GHGU1zEOVD5z2EXs3e8zU55S', [
        'metadata' => [
            'test' => '1234abcd',
        ],
    ]);

    $this->assertStringContainsString('id', $thread);
    $this->assertStringContainsString('"object": "thread"', $thread);
})->group('working');

it('should handle delete a thread', function () use ($open_ai) {
    $thread = $open_ai->deleteThread('thread_GHGU1zEOVD5z2EXs3e8zU55S');

    $this->assertStringContainsString('id', $thread);
    $this->assertStringContainsString('"deleted": true', $thread);
})->group('working');

it('should handle create a message within thread', function () use ($open_ai) {
    $thread = json_decode($open_ai->createThread(), true);
    $message = $open_ai->createThreadMessage($thread['id'], [
        'role' => 'user',
        'content' => 'How does AI work? Explain it in simple terms.',
    ]);

    $this->assertStringContainsString('id', $message);
    $this->assertStringContainsString('"object": "thread.message"', $message);
})->group('working');

it('should handle retrieve a message within thread', function () use ($open_ai) {
    $threadId = 'thread_d86alfR2rfF7rASyV4V7hicz';
    $messageId = 'msg_d37P5XgREsm6BItOcppnBO1b';
    $message = $open_ai->retrieveThreadMessage($threadId, $messageId);

    $this->assertStringContainsString('id', $message);
    $this->assertStringContainsString('"object": "thread.message"', $message);
})->group('working');

it('should handle modify a message within thread', function () use ($open_ai) {
    $threadId = 'thread_d86alfR2rfF7rASyV4V7hicz';
    $messageId = 'msg_d37P5XgREsm6BItOcppnBO1b';
    $message = $open_ai->modifyThreadMessage($threadId, $messageId, [
        'metadata' => [
            'test' => '1234abcd',
        ],
    ]);

    $this->assertStringContainsString('id', $message);
    $this->assertStringContainsString('"object": "thread.message"', $message);
})->group('working');

it('should handle list messages within thread', function () use ($open_ai) {
    $threadId = 'thread_d86alfR2rfF7rASyV4V7hicz';
    $query = ['limit' => 10];
    $messages = $open_ai->listThreadMessages($threadId, $query);

    $this->assertStringContainsString('id', $messages);
    $this->assertStringContainsString('"object": "list"', $messages);
    $this->assertStringContainsString('data', $messages);
})->group('working');

it('should handle retrieve a file within message', function () use ($open_ai) {
    $threadId = 'thread_d86alfR2rfF7rASyV4V7hicz';
    $fileId = 'file-CRLcY63DiHphWuBrmDWZVCgA';
    $message = json_decode($open_ai->createThreadMessage($threadId, [
        'role' => 'user',
        'content' => 'How does AI work? Explain it in simple terms.',
        'file_ids' => [$fileId],
    ]), true);

    $file = $open_ai->retrieveMessageFile($threadId, $message['id'], $fileId);

    $this->assertStringContainsString('id', $file);
    $this->assertStringContainsString('"object": "thread.message.file"', $file);
})->group('working');

it('should handle list files within message', function () use ($open_ai) {
    $threadId = 'thread_d86alfR2rfF7rASyV4V7hicz';
    $messageId = 'msg_CZ47kAGZugAfeHMX6bmJIukP';
    $query = ['limit' => 10];

    $files = $open_ai->listMessageFiles($threadId, $messageId, $query);

    $this->assertStringContainsString('id', $files);
    $this->assertStringContainsString('"object": "thread.message.file"', $files);
})->group('working');

it('should handle create a run of a thread', function () use ($open_ai) {
    $assistantId = 'asst_zT1LLZ8dWnuFCrMFzqxFOhzz';
    $thread = json_decode($open_ai->createThread([
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Hello, what is AI?',
                'file_ids' => [],
            ],
        ]]), true);

    $run = $open_ai->createRun($thread['id'], ['assistant_id' => $assistantId]);

    $this->assertStringContainsString('id', $run);
    $this->assertStringContainsString('"object": "thread.run"', $run);
})->group('working');

it('should handle retrieve a run of a thread', function () use ($open_ai) {
    $threadId = 'thread_JZbzCYpYgpNb79FNeneO3cGI';
    $runId = 'run_xBKYFcD2Jg3gnfrje6fhiyXj';

    $run = $open_ai->retrieveRun($threadId, $runId);

    $this->assertStringContainsString('id', $run);
    $this->assertStringContainsString('"object": "thread.run"', $run);
})->group('working');

it('should handle modify a run of a thread', function () use ($open_ai) {
    $threadId = 'thread_JZbzCYpYgpNb79FNeneO3cGI';
    $runId = 'run_xBKYFcD2Jg3gnfrje6fhiyXj';

    $run = $open_ai->modifyRun($threadId, $runId, [
        'metadata' => ['test' => 'abcd1234']
    ]);

    $this->assertStringContainsString('id', $run);
    $this->assertStringContainsString('"object": "thread.run"', $run);
})->group('working');

it('should handle list runs of a thread', function () use ($open_ai) {
    $threadId = 'thread_JZbzCYpYgpNb79FNeneO3cGI';
    $query = ['limit' => 10];

    $runs = $open_ai->listRuns($threadId, $query);

    $this->assertStringContainsString('id', $runs);
    $this->assertStringContainsString('"object": "list"', $runs);
    $this->assertStringContainsString('data', $runs);
})->group('working');

it('should handle submit tool outputs within run', function () use ($open_ai) {
    $threadId = 'thread_JZbzCYpYgpNb79FNeneO3cGI';
    $runId = 'run_xBKYFcD2Jg3gnfrje6fhiyXj';
    $outputs = ['tool_outputs' => [
        ['tool_call_id' => 'call_abc123', 'output' => '28C'],
    ]];

    $run = $open_ai->submitToolOutputs($threadId, $runId, $outputs);

    $this->assertStringContainsString('id', $run);
    $this->assertStringContainsString('"object": "thread.run"', $run);
    $this->assertStringContainsString('tools', $run);
})->group('working');

it('should handle cancel a run of a thread', function () use ($open_ai) {
    $threadId = 'thread_JZbzCYpYgpNb79FNeneO3cGI';
    $runId = 'run_xBKYFcD2Jg3gnfrje6fhiyXj';

    $run = $open_ai->cancelRun($threadId, $runId);

    $this->assertStringContainsString('id', $run);
    $this->assertStringContainsString('"object": "thread.run"', $run);
    $this->assertStringContainsString('"status": "cancelling"', $run);
})->group('working');

it('should handle create thread and run', function () use ($open_ai) {
    $thread = [
        'assistant_id' => 'asst_zT1LLZ8dWnuFCrMFzqxFOhzz',
        'thread' => [
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Hello, what is AI?',
                    'file_ids' => [],
                ],
            ]
        ]
    ];

    $run = $open_ai->createThreadAndRun($thread);

    $this->assertStringContainsString('id', $run);
    $this->assertStringContainsString('"object": "thread.run"', $run);
})->group('working');
