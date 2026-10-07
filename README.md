# OpenAI PHP Client

[![Latest Version on Packagist](https://img.shields.io/packagist/v/orhanerday/open-ai.svg?style=flat-square)](https://packagist.org/packages/orhanerday/open-ai)

A community-maintained PHP client for the OpenAI REST API, with streaming support.
Requires PHP 7.4+ and the cURL and JSON extensions.

See [API_COVERAGE.md](API_COVERAGE.md) for endpoint coverage and
[MIGRATING.md](MIGRATING.md) for the upcoming 6.0.0 breaking changes.

## Installation

```bash
composer require orhanerday/open-ai
```

## Quick start

Set `OPENAI_API_KEY` in your environment.

Linux or macOS:

```bash
export OPENAI_API_KEY='your-api-key'
```

PowerShell:

```powershell
$Env:OPENAI_API_KEY = "your-api-key"
```

Windows Command Prompt:

```cmd
set "OPENAI_API_KEY=your-api-key"
```

Save this as `example.php`:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Orhanerday\OpenAi\OpenAi;

$open_ai = new OpenAi(getenv('OPENAI_API_KEY'));
$result = $open_ai->chat([
    'model' => 'gpt-4o-mini',
    'messages' => [['role' => 'user', 'content' => 'Hello']],
]);

$response = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
echo $response['choices'][0]['message']['content'];
```

Run it with `php example.php`. See [Handling results](#handling-results) for HTTP errors.

## Supported APIs

- Chat
    - [x] [Chat completions](#chat-completions)
    - [x] Stored completions: retrieve, update, delete, list, and list messages
- Models
    - [x] [List models](https://developers.openai.com/api/reference/resources/models/methods/list)
    - [x] [Retrieve model](https://developers.openai.com/api/reference/resources/models/methods/retrieve)
- Images
    - [x] [Create image](https://developers.openai.com/api/reference/resources/images/methods/generate)
    - [x] [Create image edit](https://developers.openai.com/api/reference/resources/images/methods/edit)
- Embeddings
    - [x] [Create embeddings](https://developers.openai.com/api/reference/resources/embeddings/methods/create)
- Audio
    - [x] [Text to Speech (TTS)](#text-to-speech-tts)
    - [x] [Create transcription](#create-transcription)
    - [x] [Create translation](#create-translation)
    - [x] Custom voice creation and voice consent CRUD
- Files
    - [x] [List files](#list-files)
    - [x] [Upload file](#upload-file)
    - [x] [Delete file](#delete-file)
    - [x] [Retrieve file](#retrieve-file)
    - [x] [Retrieve file content](#retrieve-file-content)
- Fine-tunes
    - [x] [Create fine-tune](#create-fine-tune)
    - [x] [List fine-tunes](#list-fine-tune)
    - [x] [Retrieve fine-tune](#retrieve-fine-tune)
    - [x] [Cancel fine-tune](#cancel-fine-tune)
    - [x] [List fine-tune events](#list-fine-tune-events)
    - [x] [Delete fine-tune model](#delete-fine-tune-model)
    - [x] Pause/resume, checkpoints and permissions, grader validation and execution
- Moderation
    - [x] [Create moderation](#content-moderations)
- Responses API
    - [x] [Responses](#responses-api)
- Conversations API
    - [x] [Conversations](#conversations-api)
- Vector Stores API
    - [x] [Vector Stores](#vector-stores-api)
- Batches API
    - [x] [Batches](#batches-api)
- Uploads API
    - [x] [Uploads](#uploads-api)
- Realtime API
    - [x] [Realtime REST operations](#realtime-api)
- Decisions API
    - [x] [Decisions](#decisions-api)
- Webhooks
    - [x] [Endpoint management and signature verification](#webhooks)
- Organization reporting
    - [x] [Usage and Costs](#usage-and-costs)

See [API_COVERAGE.md](API_COVERAGE.md) for exact routes, guide support, and testing limits.
WebSocket mode and mid-turn steering require a separate WebSocket client.

## Configuration

The examples below use an `OpenAi` instance:

```php
use Orhanerday\OpenAi\OpenAi;

$open_ai = new OpenAi(getenv('OPENAI_API_KEY'));
```

You can also configure or replace the key after construction:

```php
$open_ai = new OpenAi();
$open_ai->setApiKey(getenv('OPENAI_API_KEY'));
```

`setApiKey()` replaces the Authorization header and rejects blank keys. Requests fail locally
if no key is configured.

### Organization

Specify the organization for API requests:

```php
$open_ai->setORG('org-your-organization');
```

### Base URL

Set the origin for an OpenAI-compatible API:

```php
$open_ai->setBaseURL('https://ai.example.com');
```

### Custom API Version

By default, the client appends `/v1` to the Base URL. You can override this to support alternative API versions (e.g., ByteDance Ark uses `/api/v3`):

```php
$open_ai->setBaseURL('https://ark.cn-beijing.volces.com');
$open_ai->setApiVersion('api/v3');
```

### Proxy

Route requests through a proxy:

```php
$open_ai->setProxy("http://127.0.0.1:1086");
```

### Custom cURL options

Set cURL options for each request:

```php
$open_ai->setCURLOptions([
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 300,
]);
```

### Headers

```php
$open_ai->setHeader(["Connection: keep-alive"]);
```

### Request information

Inspect transport metadata after a request:

```php
$open_ai->listModels();
$info = $open_ai->getCURLInfo();
echo $info['http_code'];
```

## Chat completions

```php
$result = $open_ai->chat([
    'model' => 'gpt-4o-mini',
    'messages' => [['role' => 'user', 'content' => 'Hello']],
]);
```

### Read response text

```php
$response = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
echo $response['choices'][0]['message']['content'];
```

### Stream example

Both `chat()` and `createResponse()` accept a callback when `stream` is `true`.
The callback receives raw server-sent event chunks and must return the number of bytes consumed.
Forward chunks unchanged so event boundaries survive browser parsing.

```php
$open_ai = new OpenAi(getenv('OPENAI_API_KEY'));

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

$open_ai->createResponse([
    'model' => 'gpt-4o-mini',
    'input' => 'Hello',
    'stream' => true,
    'store' => false,
], function ($curl, $data) {
    echo $data;
    if (ob_get_level() > 0) {
        ob_flush();
    }
    flush();

    return strlen($data);
});
```

For a browser client, listen for the named Responses events:

```html
<div id="output"></div>
<script>
const events = new EventSource('/stream.php');
const output = document.getElementById('output');

events.addEventListener('response.output_text.delta', event => {
    output.textContent += JSON.parse(event.data).delta;
});
events.addEventListener('response.completed', () => events.close());
events.addEventListener('response.failed', () => events.close());
events.addEventListener('response.incomplete', () => events.close());
events.onerror = () => events.close();
</script>
```

See the [official streaming guide](https://developers.openai.com/api/docs/guides/streaming-responses) for event handling.

## Images

Use a supported GPT Image model explicitly. GPT Image responses contain base64 image data.
See the [official image guide](https://developers.openai.com/api/docs/guides/image-generation) for supported models and parameters.

### Create image

```php
$result = $open_ai->image([
    'model' => 'gpt-image-2.5-flare',
    'prompt' => 'A cat drinking milk',
    'n' => 1,
    'size' => '1024x1024',
]);

$image = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
file_put_contents('cat.png', base64_decode($image['data'][0]['b64_json']));
```

### Create image edit

Supply your own input image. A single local file is sent as multipart form data.
For multiple inputs, use the JSON `images` field with uploaded file IDs or image URLs.

```php
$result = $open_ai->imageEdit([
    'model' => 'gpt-image-2.5-flare',
    'image' => new CURLFile('/path/to/input.png', 'image/png', 'input.png'),
    'prompt' => 'Add a beret to the animal in this image',
    'n' => 1,
    'size' => '1024x1024',
]);
```

To create a variation, use `imageEdit()` with a prompt describing the desired changes.

## Embeddings

Create a vector representation of text.

Related guide: [Embeddings](https://platform.openai.com/docs/guides/embeddings)

### Create embeddings

```php
$result = $open_ai->embeddings([
    "model" => "text-embedding-3-small",
    "input" => "The food was delicious and the waiter..."
]);
```

## Content Moderations

Classify content using the moderation endpoint.

```php
$flags = $open_ai->moderation([
    'model' => 'omni-moderation-latest',
    'input' => 'I hate ducks!'
]);
```

API reference: [OpenAI Moderations](https://developers.openai.com/api/reference/resources/moderations)

## Audio

### Text to speech (TTS)

`tts($opts)` wraps `/v1/audio/speech` for existing integrations. Its currently documented speech models are deprecated;
see [OpenAI's deprecations](https://developers.openai.com/api/docs/deprecations) and the
[Realtime guide](https://developers.openai.com/api/docs/guides/realtime) when planning a new voice integration.
Pass a model supported by the speech endpoint explicitly; Realtime models use a different API.

### Create transcription

Transcribe a recording using a file you supply:

```php
$result = $open_ai->transcribe([
    'model' => 'gpt-transcribe',
    'file' => new CURLFile('/path/to/recording.mp3', 'audio/mpeg', 'recording.mp3'),
]);
```

See the [official file transcription guide](https://developers.openai.com/api/docs/guides/speech-to-text).

### Create translation

`translate($opts)` wraps `/v1/audio/translations` for existing integrations. For new workflows, transcribe with
`gpt-transcribe`, then send the transcript to `createResponse()` with translation instructions.
Check the [translation endpoint's supported models](https://developers.openai.com/api/reference/resources/audio/subresources/translations/methods/create)
before using the compatibility wrapper.

## Files

Upload files for model input, vector store file search, batch processing, or fine-tuning. Choose a purpose accepted by the [Files API](https://developers.openai.com/api/reference/resources/files/methods/create).

### List files

Returns a list of files that belong to the user's organization.

```php
$files = $open_ai->listFiles();
```

### Upload file

Upload a file you supply, and retain the returned ID for later operations. For larger files, use the Uploads API.
See the [Files reference](https://developers.openai.com/api/reference/resources/files/methods/create) for current size limits.

```php
$result = $open_ai->uploadFile([
    'purpose' => 'user_data',
    'file' => new CURLFile('/path/to/document.txt', 'text/plain', 'document.txt'),
]);

$uploaded = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
$fileId = $uploaded['id'];
```

### Upload file with an HTML form

```php
<form action="index.php" method="post" enctype="multipart/form-data">
    Select file to upload:
    <input type="file" name="fileToUpload" id="fileToUpload">
    <input type="submit" value="Upload File" name="submit">
</form>
<?php
require __DIR__ . '/vendor/autoload.php';

use Orhanerday\OpenAi\OpenAi;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    ob_clean();
    $open_ai = new OpenAi(getenv('OPENAI_API_KEY'));
    $tmp_file = $_FILES['fileToUpload']['tmp_name'];
    $file_name = basename($_FILES['fileToUpload']['name']);
    $c_file = curl_file_create($tmp_file, $_FILES['fileToUpload']['type'], $file_name);

    echo "[";
    echo $open_ai->uploadFile(
        [
            "purpose" => "user_data",
            "file" => $c_file,
        ]
    );
    echo ",";
    echo $open_ai->listFiles();
    echo "]";

}

```

### Delete file

```php
$result = $open_ai->deleteFile($fileId);
```

### Retrieve file

```php
$file = $open_ai->retrieveFile($fileId);
```

### Retrieve file content

Use an ID for a downloadable file purpose, such as a fine-tuning or batch file. OpenAI rejects
content downloads for `user_data` files, even though retrieving their metadata succeeds.

```php
$file = $open_ai->retrieveFileContent($downloadableFileId);
```

## Fine-tunes

These methods manage `/v1/fine_tuning/jobs`. Fine-tuning access is restricted by OpenAI's
[availability policy](https://developers.openai.com/api/docs/deprecations).
Use a model supported for fine-tuning and a training file containing valid chat-format examples.

### Create fine-tune

```php
$upload = $open_ai->uploadFile([
    'purpose' => 'fine-tune',
    'file' => new CURLFile('/path/to/training.jsonl', 'application/jsonl', 'training.jsonl'),
]);
$trainingFileId = json_decode($upload, true, 512, JSON_THROW_ON_ERROR)['id'];

$result = $open_ai->createFineTune([
    'model' => 'gpt-4o-mini',
    'training_file' => $trainingFileId,
]);
$jobId = json_decode($result, true, 512, JSON_THROW_ON_ERROR)['id'];
```

### List fine-tune

```php
$jobs = $open_ai->listFineTunes();
```

### Retrieve fine-tune

```php
$job = $open_ai->retrieveFineTune($jobId);
```

### Cancel fine-tune

```php
$result = $open_ai->cancelFineTune($jobId);
```

### List fine-tune events

```php
$events = $open_ai->listFineTuneEvents($jobId);
```

### Delete fine-tune model

Delete the model ID returned by a completed job, using `DELETE /v1/models/{model}`:

```php
$completedJob = json_decode($open_ai->retrieveFineTune($jobId), true, 512, JSON_THROW_ON_ERROR);
if (!empty($completedJob['fine_tuned_model'])) {
    $result = $open_ai->deleteFineTune($completedJob['fine_tuned_model']);
}
```

### Checkpoint permissions

Construct a separate `OpenAi` client with an **admin API key** for checkpoint permissions.
`listFineTuningCheckpointPermissions()`, `createFineTuningCheckpointPermission()`, and
`deleteFineTuningCheckpointPermission()` manage access across projects;
`listFineTuningCheckpoints()` lists a job's checkpoints.

## Models

List or retrieve model metadata.

### List models

```php
$result = $open_ai->listModels();
```

### Retrieve model

```php
$result = $open_ai->retrieveModel("gpt-4o-mini");
```

## Handling results

Nonstreaming methods return raw response bodies; streaming methods deliver raw chunks to the callback.
Inspect `getCURLInfo()['http_code']` before decoding success fields;
HTTP API errors are returned as response bodies, while connection failures throw `Exception`.
Malformed JSON request data throws `JsonException`.

## Responses API

```php
$result = $open_ai->createResponse([
    "model" => "gpt-4o-mini",
    "input" => "Hello",
    "max_output_tokens" => 100,
]);
```

Token counting and explicit compaction use `countResponseInputTokens($opts)` and `compactResponse($opts)`.
File inputs, tools, structured output, conversation state, automatic compaction, and background mode are
passed through as request options; see [API_COVERAGE.md](API_COVERAGE.md).
To resume a response created with `background => true` and `stream => true`:

```php
$open_ai->retrieveResponse($responseId, [
    'stream' => true,
    'starting_after' => $lastSequenceNumber,
], function ($curl, $data) {
    echo $data;
    return strlen($data);
});
```

For the Responses multi-agent HTTP beta, add its header with
`$open_ai->setHeader(['OpenAI-Beta' => 'responses_multi_agent=v1'])` and pass the documented
`multi_agent` options to `createResponse()`. The application handles tool execution and SSE events.

## Conversations API

```php
$result = $open_ai->createConversation();
```

## Vector Stores API

```php
$result = $open_ai->createVectorStore([
    "name" => "Support FAQs"
]);
```

## Batches API

```php
$upload = $open_ai->uploadFile([
    'purpose' => 'batch',
    'file' => new CURLFile('/path/to/requests.jsonl', 'application/jsonl', 'requests.jsonl'),
]);
$batchFileId = json_decode($upload, true, 512, JSON_THROW_ON_ERROR)['id'];

$result = $open_ai->createBatch([
    "completion_window" => "24h",
    "endpoint" => "/v1/responses",
    "input_file_id" => $batchFileId,
]);
```

## Uploads API

Create an upload, add its bytes as multipart parts, then complete it with the ordered part IDs.
Each part must be at most 64 MB. This example uploads one part:

```php
$path = '/path/to/document.txt';
$upload = json_decode($open_ai->createUpload([
    'filename' => 'document.txt',
    'purpose' => 'assistants', // Accepted for vector store files.
    'bytes' => filesize($path),
    'mime_type' => 'text/plain',
]), true, 512, JSON_THROW_ON_ERROR);

$part = json_decode($open_ai->addUploadPart($upload['id'], [
    'data' => new CURLFile($path, 'application/octet-stream', 'part.bin'),
]), true, 512, JSON_THROW_ON_ERROR);

$result = $open_ai->completeUpload($upload['id'], ['part_ids' => [$part['id']]]);
```

## Realtime API

Create a short-lived client secret with a nested session configuration:

```php
$result = $open_ai->createRealtimeClientSecret([
    'session' => [
        'type' => 'realtime',
        'model' => 'gpt-realtime-2.1-mini',
        'instructions' => 'You are a helpful assistant.',
    ],
    'expires_after' => ['anchor' => 'created_at', 'seconds' => 600],
]);
```

`createRealtimeTranslationClientSecret()` issues translation session credentials.
`createRealtimeCall()` accepts an SDP offer and a session array, returning the raw SDP answer.
`acceptRealtimeCall()`, `hangupRealtimeCall()`, `referRealtimeCall()`, and `rejectRealtimeCall()`
control existing calls. These REST wrappers do not implement a WebRTC media or WebSocket client.

See the [client-secret API reference](https://developers.openai.com/api/reference/resources/realtime/subresources/client_secrets/methods/create).

## Decisions API

```php
$result = $open_ai->createDecision([
    'model' => 'gpt-6-luna',
    'input' => 'The support desk mascot is a purple owl.',
    'questions' => [[
        'type' => 'predicate',
        'name' => 'owl',
        'instructions' => 'The mascot is an owl.',
    ]],
]);
```

See the [Decisions guide](https://developers.openai.com/api/docs/guides/decisions) for beta availability and question types.

## Webhooks

Use `createWebhookEndpoint()`, `listWebhookEndpoints()`, `retrieveWebhookEndpoint()`,
`updateWebhookEndpoint()`, `deleteWebhookEndpoint()`, `rotateWebhookEndpointSecret()`, and
`testWebhookEndpoint()` to manage subscriptions. Testing an endpoint sends an event to its configured URL.
These methods use `/v1/webhook_endpoints`.

Verify incoming events using the original request body and the webhook signing secret:

```php
use Orhanerday\OpenAi\Webhook;

$event = Webhook::unwrap(
    file_get_contents('php://input'),
    getallheaders(),
    getenv('OPENAI_WEBHOOK_SECRET')
);
```

Verification throws on invalid signatures, missing headers, or timestamps outside the default five-minute tolerance.
Your application should handle retries idempotently using the webhook ID. See the
[webhooks guide](https://developers.openai.com/api/docs/guides/webhooks).

## Usage and Costs

Use an organization admin API key for these read-only reporting endpoints:

```php
$reporting = new OpenAi(getenv('OPENAI_ADMIN_KEY'));
$options = [
    'start_time' => time() - 7 * 86400,
    'end_time' => time(),
    'bucket_width' => '1d',
    'group_by' => ['model', 'project_id'],
    'batch' => false,
    'limit' => 7,
];

$page = json_decode($reporting->getCompletionsUsage($options), true, 512, JSON_THROW_ON_ERROR);
if ($reporting->getCURLInfo()['http_code'] !== 200) {
    throw new RuntimeException('Usage request failed; check the decoded API error.');
}

// Each entry in data is a time bucket containing aggregated results.
foreach ($page['data'] as $bucket) {
    foreach ($bucket['results'] as $result) {
        echo $result['input_tokens'] . PHP_EOL;
    }
}

// To fetch the next page, preserve the filters and pass the returned cursor.
if ($page['has_more']) {
    $options['page'] = $page['next_page'];
    $nextPage = $reporting->getCompletionsUsage($options);
}
```

All methods require an options array with `start_time` (Unix seconds). Filters, array-valued
`group_by`, time buckets, limits, and the `page` cursor pass through as GET query parameters.
Each method returns the raw response body; pagination is controlled by the caller.

| Report | Method |
| --- | --- |
| Completions | `getCompletionsUsage($options)` |
| Embeddings | `getEmbeddingsUsage($options)` |
| Moderations | `getModerationsUsage($options)` |
| Images | `getImagesUsage($options)` |
| Audio speeches | `getAudioSpeechesUsage($options)` |
| Audio transcriptions | `getAudioTranscriptionsUsage($options)` |
| Vector stores | `getVectorStoresUsage($options)` |
| Code interpreter sessions | `getCodeInterpreterSessionsUsage($options)` |
| File search calls | `getFileSearchCallsUsage($options)` |
| Web search calls | `getWebSearchCallsUsage($options)` |
| Costs | `getCosts($options)` |

For costs, use the same time-range and pagination fields, with cost-specific grouping:

```php
$costs = $reporting->getCosts([
    'start_time' => time() - 7 * 86400,
    'bucket_width' => '1d',
    'group_by' => ['project_id', 'line_item'],
]);
```

Costs supports daily buckets and should be used for spend reporting. Usage supports minute,
hour, and day buckets; usage aggregates may differ from billing totals. Filters and grouping
fields vary by endpoint. See the [official Usage and Costs reference](https://developers.openai.com/api/reference/resources/admin/subresources/organization/subresources/usage).
The `/organization/usage/completions` reporting route remains supported independently of
the removed legacy text-generation integration.

## Testing

Composer selects a compatible Pest version for your PHP runtime. The library continues to support PHP 7.4+.

Run the actual API tests with a key from the environment or your local `.env`:

```bash
export OPENAI_API_KEY='your-api-key'
composer test
```

`composer test-live` runs only live API tests. Without a key, API tests are explicitly skipped.
Usage and Costs tests require `OPENAI_ADMIN_KEY`; run them separately with
`vendor/bin/pest tests/UsageLiveTest.php`. Without that admin key, those cases are explicitly skipped.
To run native cURL transport and webhook signature checks without API calls:

```bash
vendor/bin/pest --exclude-group=live
```

There are no cURL mocks. Live tests may incur API charges. They create and delete their own files,
Responses, conversations, stored chats, uploads, and vector stores. They never launch a training job.
Stored chat tests poll briefly because persistence can be asynchronous.

Models can be selected with `OPENAI_CHAT_MODEL`, `OPENAI_IMAGE_MODEL`, `OPENAI_COMPACTION_MODEL`,
`OPENAI_DECISION_MODEL`, and `OPENAI_REALTIME_MODEL`. Defaults follow the examples and current guides.
Additional account-dependent tests and required fixtures are documented in [API_COVERAGE.md](API_COVERAGE.md).
Push/PR CI runs native transport and signature checks on PHP 7.4 and 8.4; manually dispatch the Tests
workflow with an `OPENAI_API_KEY` repository secret to run the live suite. Add the optional
`OPENAI_ADMIN_KEY` secret to include organization Usage and Costs tests.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## Contributing

See [CONTRIBUTING.md](.github/CONTRIBUTING.md).

## Security Vulnerabilities

Report security vulnerabilities to [orhanerday@gmail.com](mailto:orhanerday@gmail.com)

## Credits

- [Orhan Erday](https://github.com/orhanerday)
- [All Contributors](../../contributors)

## License

[MIT](LICENSE.md).
