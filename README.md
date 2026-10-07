# OpenAI API Client in PHP

<br />

<br />


### Feel free to support this project:

* [Buy me a coffee](https://www.buymeacoffee.com/orhane)
* [Patreon](https://patreon.com/orhann)

<br />

*A message from creator,<br />Thank you for visiting the __@orhanerday/open-ai__ repository! If you find this repository helpful or useful, we encourage you to **star** it
on GitHub. Starring a repository is a way to show your support for the project. It also helps to increase the visibility
of the project and to let the community know that it is valuable. Thanks again for your support and we hope you find the
repository useful! <br /><br /> Orhan*

<br />

<br />


[![Latest Version on Packagist](https://img.shields.io/packagist/v/orhanerday/open-ai.svg?style=flat-square)](https://packagist.org/packages/orhanerday/open-ai)
[![Total Downloads](https://img.shields.io/packagist/dt/orhanerday/open-ai.svg?style=flat-square)](https://packagist.org/packages/orhanerday/open-ai)

<br />

<br />

<img src="./openai-elephpant.svg" width="1250" height="300" alt="orhanerday-open-ai-logo">

<br />

<br />

# Featured in


[![Jetbrains Blog](https://user-images.githubusercontent.com/22305274/222431781-86591161-ccd5-4889-bd80-97a0fd0fdf0d.png)](https://blog.jetbrains.com/phpstorm/2022/12/php-annotated-december-2022/#:~:text=orhanerday/open%2Dai%20%E2%80%93%20A%20PHP%20SDK%20for%20accessing%20the%20OpenAI%20GPT%2D3%20API)

[![Laravel News](https://user-images.githubusercontent.com/22305274/222430084-be097d59-e6bc-408d-8adb-7b751d5a05b2.png)](https://laravel-news.com/openai-sdk-for-php)

[![日思录](https://user-images.githubusercontent.com/22305274/222431699-f3a8a146-e27c-4fe3-8c93-1d762559752f.png)](http://tubring.cn/articles/59)

![logo_new](https://github.com/orhanerday/open-ai/assets/22305274/398b3a1e-7323-46f3-8a53-a9f115cf2281)



# Comparison With Other Packages

| Project Name           | Required PHP Version (Lower is better) | Description                                                                                                                                                | Type (Official / Community) | Support                                                                                                                                  |
|------------------------|----------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------|-----------------------------|------------------------------------------------------------------------------------------------------------------------------------------|
| **orhanerday/open-ai** | **PHP 7.4+**                           | **Community-maintained OpenAI PHP SDK with Responses, Chat, Images, and streaming support.** | Community                   | Available, ([Community driven Discord Server](https://discord.gg/xpGUD528XJ) or personal mail [orhann@duck.com](mailto:orhann@duck.com)) |
| openai-** */c****t     | PHP 8.1+                               | OpenAI PHP API client.                                                                                                                                     | Community                   | -                                                                                                                                        |


<br />

## About this package

An open-source, community-maintained PHP SDK for the OpenAI API.

> #### For more information, you can read laravel news [blog post](https://laravel-news.com/openai-sdk-for-php).
> #### Free support is available. [Join our discord server](#join-our-discord-server)
> #### To get started with this package, you'll first want to be familiar with the [OpenAI API documentation](https://platform.openai.com/docs/overview) and [examples](https://platform.openai.com/docs/examples). Also you can get help from our discord channel that called [#api-support](https://discord.gg/R9CpVUdqQR)

## News

- orhanerday/open-ai added to community libraries php [section](https://platform.openai.com/docs/libraries/php).
- orhanerday/open-ai featured
  on [PHPStorm blog post](https://blog.jetbrains.com/phpstorm/2022/12/php-annotated-december-2022/#:~:text=orhanerday/open%2Dai%20%E2%80%93%20A%20PHP%20SDK%20for%20accessing%20the%20OpenAI%20GPT%2D3%20API),
  thanks JetBrains!

> Requires PHP 7.4+

## Join our discord server

![Discord Banner 2](https://discordapp.com/api/guilds/1047074572488417330/widget.png?style=banner2)

[Click here to join the Discord server](https://discord.gg/xpGUD528XJ)

## Support this project

As you may know, OpenAI PHP is an open-source project wrapping tool for OpenAI. We rely on the support of our community
to continue developing and maintaining the project, and one way that you can help is by making a donation.

Donations allow us to cover expenses such as hosting costs(for testing), development tools, and other resources that are
necessary to keep the project running smoothly. Every contribution, no matter how small, helps us to continue improving
OpenAI PHP for everyone.

If you have benefited from using OpenAI PHP and would like to support its continued development, we would greatly
appreciate a donation of any amount. You can make a donation through;

* [Buy me a coffee](https://www.buymeacoffee.com/orhane)
* [Patreon](https://patreon.com/orhann)

Thank you for considering a donation to Orhanerday/OpenAI PHP SDK. Your support is greatly appreciated and helps to
ensure that the project can continue to grow and improve.

*Sincerely,*

**Orhan Erday** / Creator.

# Documentation
Use the examples below, [endpoint support](README.md#endpoint-support), and [MIGRATING.md](MIGRATING.md) for this release.

# Endpoint Support

- Chat
    - [x] [ChatGPT API](#chat-as-known-as-chatgpt-api)
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

See [endpoint support](README.md#endpoint-support) for exact routes, guide support, and testing limits.
WebSocket mode and mid-turn steering require a separate WebSocket client.

## Upcoming major release

The current refactor targets **6.0.0** because 5.x releases already exist. See [MIGRATING.md](MIGRATING.md) for breaking changes and replacements.

## Installation

You can install the package via composer:

```bash
composer require orhanerday/open-ai
```

## Quick Start ⚡

Before you get starting, you should set OPENAI_API_KEY as ENV key, and set OpenAI key as env value with the following
commands;

_Powershell_

```powershell
$Env:OPENAI_API_KEY = "sk-gjtv....."
```

_Cmd_

```cmd
set OPENAI_API_KEY=sk-gjtv.....
```

_Linux or macOS_

```shell
export OPENAI_API_KEY=sk-gjtv.....
```

> Getting issues while setting up env? Please read
> the [article](https://help.openai.com/en/articles/5112595-best-practices-for-api-key-safety) or you can check
> my [StackOverflow answer](https://stackoverflow.com/a/73904271/15196622) for the Windows® ENV setup.

Create your `index.php` file and paste the following code part into the file.

```php
<?php

require __DIR__ . '/vendor/autoload.php'; // remove this line if you use a PHP Framework.

use Orhanerday\OpenAi\OpenAi;

$open_ai_key = getenv('OPENAI_API_KEY');
$open_ai = new OpenAi($open_ai_key);

$chat = $open_ai->chat([
   'model' => 'gpt-4o-mini',
   'messages' => [
       [
           "role" => "system",
           "content" => "You are a helpful assistant."
       ],
       [
           "role" => "user",
           "content" => "Who won the world series in 2020?"
       ],
       [
           "role" => "assistant",
           "content" => "The Los Angeles Dodgers won the World Series in 2020."
       ],
       [
           "role" => "user",
           "content" => "Where was it played?"
       ],
   ],
   'temperature' => 1.0,
   'max_tokens' => 4000,
   'frequency_penalty' => 0,
   'presence_penalty' => 0,
]);


var_dump($chat);
echo "<br>";
echo "<br>";
echo "<br>";
// decode response
$d = json_decode($chat);
// Get Content
echo($d->choices[0]->message->content);
```

_Run the server with the following command_

```shell
php -S localhost:8000 -t .
```

## NVIDIA NIM INTEGRATION

orhanerday/open-ai supports Nvidia NIM. The below example is MixtralAI. Check https://build.nvidia.com/explore/discover for more examples.

```php
<?php

require __DIR__ . '/vendor/autoload.php'; // remove this line if you use a PHP Framework.

use Orhanerday\OpenAi\OpenAi;

$nvidia_ai_key = getenv('NVIDIA_AI_API_KEY');
error_log($open_ai_key);
$open_ai = new OpenAi($nvidia_ai_key);
$open_ai->setBaseURL("https://integrate.api.nvidia.com");
$chat = $open_ai->chat([
    'model' => 'mistralai/mixtral-8x7b-instruct-v0.1',
    'messages' => [["role" => "user", "content" => "Write a limmerick about the wonders of GPU computing."]],
    'temperature' => 0.5,
    'max_tokens' => 1024,
    'top_p' => 1,
]);

var_dump($chat);
echo "<br>";
echo "<br>";
echo "<br>";
// decode response
$d = json_decode($chat);
// Get Content
echo ($d->choices[0]->message->content);

```


## Usage

### Load your key from an environment variable.

> According to the following code `$open_ai` is the base variable for all open-ai operations.

```php
use Orhanerday\OpenAi\OpenAi;

$open_ai = new OpenAi(getenv('OPENAI_API_KEY'));
```

## Requesting organization

For users who belong to multiple organizations, you can pass a header to specify which organization is used for an API
request.
Usage from these API requests will count against the specified organization's subscription quota.

````php
$open_ai_key = getenv('OPENAI_API_KEY');
$open_ai = new OpenAi($open_ai_key);
$open_ai->setORG("org-IKN2E1nI3kFYU8ywaqgFRKqi");
````

## Base URL

You can specify Origin URL with `setBaseURL()` method;

````php
$open_ai_key = getenv('OPENAI_API_KEY');
$open_ai = new OpenAi($open_ai_key);
$open_ai->setBaseURL("https://ai.example.com");
````

## Use Proxy

You can use some proxy servers for your requests api;

````php
$open_ai->setProxy("http://127.0.0.1:1086");
````

## Set header

 ```php
$open_ai->setHeader(["Connection: keep-alive"]);
```

## Get cURL request info

You can get transport metadata after the request, including the HTTP status code:

````php
$open_ai = new OpenAi($open_ai_key);
echo $open_ai->listModels(); // you should execute the request FIRST!
var_dump($open_ai->getCURLInfo()); // You can call the request
````

## Chat (as known as ChatGPT API)

Given a chat conversation, the model will return a chat completion response.

 ```php
$complete = $open_ai->chat([
    'model' => 'gpt-4o-mini',
    'messages' => [
        [
            "role" => "system",
            "content" => "You are a helpful assistant."
        ],
        [
            "role" => "user",
            "content" => "Who won the world series in 2020?"
        ],
        [
            "role" => "assistant",
            "content" => "The Los Angeles Dodgers won the World Series in 2020."
        ],
        [
            "role" => "user",
            "content" => "Where was it played?"
        ],
    ],
    'temperature' => 1.0,
    'max_tokens' => 4000,
    'frequency_penalty' => 0,
    'presence_penalty' => 0,
]);
```

## Accessing the Element

```php
<?php
// Dummy Response For Chat API
$j = '
{
   "id":"chatcmpl-*****",
   "object":"chat.completion",
   "created":1679748856,
   "model":"gpt-4o-mini",
   "usage":{
      "prompt_tokens":9,
      "completion_tokens":10,
      "total_tokens":19
   },
   "choices":[
      {
         "message":{
            "role":"assistant",
            "content":"This is a test of the AI language model."
         },
         "finish_reason":"length",
         "index":0
      }
   ]
}
';

// decode response
$d = json_decode($j);

// Get Content
echo($d->choices[0]->message->content);
```

> ### Related: [ChatGPT Clone Project](#chatgpt-clone-project)

### Stream Example

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

#### ChatGPT Clone Project

The [ChatGPT clone](https://github.com/orhanerday/ChatGPT) is a separate example application.
Check its API usage against this release's [migration guide](MIGRATING.md).

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

Get a vector representation of a given input that can be easily consumed by machine learning models and algorithms.

Related guide: [Embeddings](https://platform.openai.com/docs/guides/embeddings)

### Create embeddings

```php
$result = $open_ai->embeddings([
    "model" => "text-embedding-3-small",
    "input" => "The food was delicious and the waiter..."
]);
```

## Content Moderations

Given a input text, outputs if the model classifies it as violating OpenAI's content policy.

```php
$flags = $open_ai->moderation([
    'model' => 'omni-moderation-latest',
    'input' => 'I want to kill them.'
]);
```

Know more about Content Moderations here: [OpenAI Moderations](https://developers.openai.com/api/reference/resources/moderations)

## Audio

### Text To Speech (TTS)

`tts($opts)` wraps `/v1/audio/speech` for existing integrations. Its currently documented speech models are deprecated;
see [OpenAI's deprecations](https://developers.openai.com/api/docs/deprecations) and the
[Realtime guide](https://developers.openai.com/api/docs/guides/realtime) when planning a new voice integration.
Pass a model supported by the speech endpoint explicitly; Realtime models use a different API.

### Create Transcription

Transcribe a recording using a file you supply:

```php
$result = $open_ai->transcribe([
    'model' => 'gpt-transcribe',
    'file' => new CURLFile('/path/to/recording.mp3', 'audio/mpeg', 'recording.mp3'),
]);
```

See the [official file transcription guide](https://developers.openai.com/api/docs/guides/speech-to-text).

### Create Translation

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

### Upload file with HTML Form

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

List and describe the various models available in the API.

### List models

Lists the currently available models, and provides basic information about each one such as the owner and availability.

 ```php
$result = $open_ai->listModels();
```

### Retrieve model

Retrieves a model instance, providing basic information about the model such as the owner and permissioning.

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
passed through as request options; see [endpoint support](README.md#endpoint-support).
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

## Testing

Composer selects a compatible Pest version for your PHP runtime. The library continues to support PHP 7.4+.

Run the actual API tests with a key from the environment or your local `.env`:

```bash
export OPENAI_API_KEY='your-api-key'
composer test
```

`composer test-live` runs only live API tests. Without a key, API tests are explicitly skipped.
To run native cURL transport and webhook signature checks without API calls:

```bash
vendor/bin/pest --exclude-group=live
```

There are no cURL mocks. Live tests may incur API charges. They create and delete their own files,
Responses, conversations, stored chats, uploads, and vector stores. They never launch a training job.
Stored chat tests poll briefly because persistence can be asynchronous.

Models can be selected with `OPENAI_CHAT_MODEL`, `OPENAI_IMAGE_MODEL`, `OPENAI_COMPACTION_MODEL`,
`OPENAI_DECISION_MODEL`, and `OPENAI_REALTIME_MODEL`. Defaults follow the examples and current guides.
Additional account-dependent tests and required fixtures are documented in [endpoint support](README.md#endpoint-support).
Push/PR CI runs native transport and signature checks on PHP 7.4 and 8.4; manually dispatch the Tests
workflow with an `OPENAI_API_KEY` repository secret to run the live suite.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please report security vulnerabilities to [orhanerday@gmail.com](mailto:orhanerday@gmail.com)

## Credits

- [Orhan Erday](https://github.com/orhanerday)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Donation

<a href="https://www.buymeacoffee.com/orhane" target="_blank"><img src="https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png" alt="Buy Me A Coffee" style="height: 41px !important;width: 174px !important;box-shadow: 0px 3px 2px 0px rgba(190, 190, 190, 0.5) !important;-webkit-box-shadow: 0px 3px 2px 0px rgba(190, 190, 190, 0.5) !important;" ></a>

## Star History

[![Star History Chart](https://api.star-history.com/svg?repos=orhanerday/open-ai&type=Date)](https://star-history.com/#orhanerday/open-ai&Date)
