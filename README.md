# OpenAI GPT-3 Api Client in PHP

<br />

> Fork from [orhanerday/open-ai](https://github.com/orhanerday/open-ai)    
> Add http exception catching
> Add curl prox support

> Requires PHP 7.4+

# Update Record

| date | features |
| ---- | ---- |
| 2023-04-12 | - add support to Azure OpenAI API |


# Endpoint Support

- Chat
  - [x] [ChatGPT API](https://platform.openai.com/docs/api-reference/chat)
- Models
    - [x] [List models](https://beta.openai.com/docs/api-reference/models/list)
    - [x] [Retrieve model](https://beta.openai.com/docs/api-reference/models/retrieve)
- Completions
    - [x] [Create completion](https://beta.openai.com/docs/api-reference/completions/create)
- Edits
    - [x] [Create edits](https://beta.openai.com/docs/api-reference/edits/create)
- Images
    - [x] [Create image](https://beta.openai.com/docs/api-reference/images/create)
    - [x] [Create image edit](https://beta.openai.com/docs/api-reference/images/create-edit)
    - [x] [Create image variation](https://beta.openai.com/docs/api-reference/images/create-variation)
- Embeddings
    - [x] [Create embeddings](https://beta.openai.com/docs/api-reference/embeddings/create)
- Files
    - [x] [List files](https://beta.openai.com/docs/api-reference/files/list)
    - [x] [Upload file](https://beta.openai.com/docs/api-reference/files/upload)
    - [x] [Delete file](https://beta.openai.com/docs/api-reference/files/delete)
    - [x] [Retrieve file](https://beta.openai.com/docs/api-reference/files/retrieve)
    - [x] [Retrieve file content](https://beta.openai.com/docs/api-reference/files/retrieve-content)
- Fine-tunes
    - [x] [Create fine-tune (beta)](https://beta.openai.com/docs/api-reference/fine-tunes/create)
    - [x] [List fine-tunes (beta)](https://beta.openai.com/docs/api-reference/fine-tunes/list)
    - [x] [Retrieve fine-tune (beta)](https://beta.openai.com/docs/api-reference/fine-tunes/retrieve)
    - [x] [Cancel fine-tune (beta)](https://beta.openai.com/docs/api-reference/fine-tunes/cancel)
    - [x] [List fine-tune events (beta)](https://beta.openai.com/docs/api-reference/fine-tunes/events)
    - [x] [Delete fine-tune model (beta)](https://beta.openai.com/docs/api-reference/fine-tunes/delete-model)
- Moderation
    - [x] [Create moderation](https://beta.openai.com/docs/api-reference/moderations/create)
- ~~Engines~~ *(deprecated)*
    - ~~[List engines](https://beta.openai.com/docs/api-reference/engines/list)~~
    - ~~[Retrieve engine](https://beta.openai.com/docs/api-reference/engines/retrieve)~~

## Installation

You can install the package via composer:

```bash
composer require ze/openai-php
```

## Quick Start

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

use Ze\OpenAi\OpenAi;

$open_ai_key = getenv('OPENAI_API_KEY');
$open_ai = new OpenAi($open_ai_key);

$complete = $open_ai->chat([
   'model' => 'gpt-3.5-turbo',
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

var_dump($complete);
```

_Run the server with the following command_

```shell
php -S localhost:8000 -t .
```

## Usage

### Load your key from an environment variable.

> According to the following code `$open_ai` is the base variable for all open-ai operations.

```php
use Ze\OpenAi\OpenAi;

$open_ai = new OpenAi(env('OPEN_AI_API_KEY'));
```

## Requesting organization

For users who belong to multiple organizations, you can pass a header to specify which organization is used for an API
request.
Usage from these API requests will count against the specified organization's subscription quota.

````php
$open_ai_key = getenv('OPENAI_API_KEY');
$open_ai = new OpenAi($open_ai_key, "org-IKN2E1nI3kFYU8ywaqgFRKqi");
````

## Custom URL

You can specify Origin URL with the third parameter of the OpenAI constructor method;

````php
$open_ai_key = getenv('OPENAI_API_KEY');
$organization = ""; // the empty string means there is no organization
$originURL = "https://ai.example.com/"; // the empty string mean the origin URL is 'https://api.openai.com'
$open_ai = new OpenAi($open_ai_key, $organization, $originURL);
````

## Chat (as known as ChatGPT API)

Given a chat conversation, the model will return a chat completion response.

 ```php
$complete = $open_ai->chat([
    'model' => 'gpt-3.5-turbo',
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

## Completions

Given a prompt, the model will return one or more predicted completions, and can also return the probabilities of
alternative tokens at each position.

 ```php
$complete = $open_ai->completion([
    'model' => 'text-davinci-002',
    'prompt' => 'Hello',
    'temperature' => 0.9,
    'max_tokens' => 150,
    'frequency_penalty' => 0,
    'presence_penalty' => 0.6,
]);
```

### Stream Example

This feature might sound familiar from [ChatGPT](https://chat.openai.com/chat).

<hr>


Whether to stream back partial progress. If set, tokens will be sent as
data-only [server-sent events](https://developer.mozilla.org/en-US/docs/Web/API/Server-sent_events/Using_server-sent_events#event_stream_format)
as they become available, with the stream terminated by a data: [DONE] message.

 ````php
$open_ai = new OpenAi(env('OPEN_AI_API_KEY'));

$opts = [
    'prompt' => "Hello",
    'temperature' => 0.9,
    "max_tokens" => 150,
    "frequency_penalty" => 0,
    "presence_penalty" => 0.6,
    "stream" => true,
];

header('Content-type: text/event-stream');
header('Cache-Control: no-cache');

$open_ai->completion($opts, function ($curl_info, $data) {
    echo $data . "<br><br>";
    echo PHP_EOL;
    ob_flush();
    flush();
    return strlen($data);
});

````

Add this part inside `<body>` of the HTML

 ````php
 
<div id="divID">Hello</div>
<script>
    var eventSource = new EventSource("/");
    var div = document.getElementById('divID');


    eventSource.onmessage = function (e) {
       if(e.data == "[DONE]")
       {
           div.innerHTML += "<br><br>Hello";
       }
        div.innerHTML += JSON.parse(e.data).choices[0].text;
    };
    eventSource.onerror = function (e) {
        console.log(e);
    };
</script>
````

You should see a response like the in video;

https://user-images.githubusercontent.com/22305274/209847128-f72c9345-dd34-46f0-bbc5-daf1d7b6121f.mp4

## Edits

Creates a new edit for the provided input, instruction, and parameters

 ```php
    $result = $open_ai->createEdit([
        "model" => "text-davinci-edit-001",
        "input" => "What day of the wek is it?",
        "instruction" => "Fix the spelling mistakes",
    ]);
```

## Images (DALL·E)

> All DALL·E Examples available in this [repo](https://github.com/orhanerday/DALLE-Examples).

Given a prompt, the model will return one or more generated images as urls or base64 encoded.

### Create image

Creates an image given a prompt.

 ```php
$complete = $open_ai->image([
    "prompt" => "A cat drinking milk",
    "n" => 1,
    "size" => "256x256",
    "response_format" => "url",
]);
```

### Create image edit

Creates an edited or extended image given an original image and a prompt.
> You need HTML upload for image edit or variation? Please
> check [DALL·E Examples](https://github.com/orhanerday/DALLE-Examples)

````php
$otter = curl_file_create(__DIR__ . './files/otter.png');
$mask = curl_file_create(__DIR__ . './files/mask.jpg');

$result = $open_ai->imageEdit([
    "image" => $otter,
    "mask" => $mask,
    "prompt" => "A cute baby sea otter wearing a beret",
    "n" => 2,
    "size" => "1024x1024",
]);
````

### Create image variation

Creates a variation of a given image.

````php
$otter = curl_file_create(__DIR__ . './files/otter.png');

$result = $open_ai->createImageVariation([
    "image" => $otter,
    "n" => 2,
    "size" => "256x256",
]);
````

## Searches

**_(Deprecated)_**
> This endpoint is deprecated and will be removed on December 3rd, 2022
> OpenAI developed new methods with better
> performance. [Learn more.](https://help.openai.com/en/articles/6272952-search-transition-guide)

Given a query and a set of documents or labels, the model ranks each document based on its semantic similarity to the
provided query.

```php
$search = $open_ai->search([
    'engine' => 'ada',
    'documents' => ['White House', 'hospital', 'school'],
    'query' => 'the president',
]);
```

## Embeddings

Get a vector representation of a given input that can be easily consumed by machine learning models and algorithms.

Related guide: [Embeddings](https://beta.openai.com/docs/guides/embeddings)

### Create embeddings

```php
$result = $open_ai->embeddings([
    "model" => "text-similarity-babbage-001",
    "input" => "The food was delicious and the waiter..."
]);
```

## Answers

**_(Deprecated)_**

> This endpoint is deprecated and will be removed on December 3rd, 2022
> We’ve developed new methods with better
> performance. [Learn more](https://help.openai.com/en/articles/6233728-answers-transition-guide).

Given a question, a set of documents, and some examples, the API generates an answer to the question based on the
information in the set of documents. This is useful for question-answering applications on sources of truth, like
company documentation or a knowledge base.

  ```php
$answer = $open_ai->answer([
    'documents' => ['Puppy A is happy.', 'Puppy B is sad.'],
    'question' => 'which puppy is happy?',
    'search_model' => 'ada',
    'model' => 'curie',
    'examples_context' => 'In 2017, U.S. life expectancy was 78.6 years.',
    'examples' => [['What is human life expectancy in the United States?', '78 years.']],
    'max_tokens' => 5,
    'stop' => ["\n", '<|endoftext|>'],
]);
```

## Classifications

**_(Deprecated)_**
> This endpoint is deprecated and will be removed on December 3rd, 2022
> OpenAI developed new methods with better
> performance. [Learn more.](https://help.openai.com/en/articles/6272941-classifications-transition-guide)

Given a query and a set of labeled examples, the model will predict the most likely label for the query. Useful as a
drop-in replacement for any ML classification or text-to-label task.

 ```php
$classification = $open_ai->classification([
    'examples' => [
        ['A happy moment', 'Positive'],
        ['I am sad.', 'Negative'],
        ['I am feeling awesome', 'Positive'],
    ],
    'labels' => ['Positive', 'Negative', 'Neutral'],
    'query' => 'It is a raining day =>(',
    'search_model' => 'ada',
    'model' => 'curie',
]);
```

## Content Moderations

Given a input text, outputs if the model classifies it as violating OpenAI's content policy.

```php
$flags = $open_ai->moderation([
    'input' => 'I want to kill them.'
]);
```

Know more about Content Moderations here: [OpenAI Moderations](https://beta.openai.com/docs/api-reference/moderations)

## List engines

**_(Deprecated)_**

> The Engines endpoints are deprecated.
> Please use their replacement, [Models](#list-models), instead. [Learn more](TODO?).

Lists the currently available engines, and provides basic information about each one such as the owner and availability.

 ```php
$engines = $open_ai->engines();
```

## Files

Files are used to upload documents that can be used across features like Answers, Search, and Classifications

### List files

Returns a list of files that belong to the user's organization.

```php
$files = $open_ai->listFiles();
```

### Upload file

Upload a file that contains document(s) to be used across various endpoints/features. Currently, the size of all the
files uploaded by one organization can be up to 1 GB. Please contact OpenAI if you need to increase the storage limit.

```php
$c_file = curl_file_create(__DIR__ . 'files/sample_file_1.jsonl');
$result = $open_ai->uploadFile([
            "purpose" => "answers",
            "file" => $c_file,
]);
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

use Ze\OpenAi\OpenAi;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    ob_clean();
    $open_ai = new OpenAi(env('OPEN_AI_API_KEY'));
    $tmp_file = $_FILES['fileToUpload']['tmp_name'];
    $file_name = basename($_FILES['fileToUpload']['name']);
    $c_file = curl_file_create($tmp_file, $_FILES['fileToUpload']['type'], $file_name);

    echo "[";
    echo $open_ai->uploadFile(
        [
            "purpose" => "answers",
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
$result = $open_ai->deleteFile('file-xxxxxxxx');
```

### Retrieve file

 ```php
$file = $open_ai->retrieveFile('file-xxxxxxxx');
```

### Retrieve file content

 ```php
$file = $open_ai->retrieveFileContent('file-xxxxxxxx');
```

## Fine-tunes

Manage fine-tuning jobs to tailor a model to your specific training data.

### Create fine-tune

 ```php
$result = $open_ai->createFineTune([
        "training_file" => "file-U3KoAAtGsjUKSPXwEUDdtw86",
]);
```

### List fine-tune

 ```php
$fine_tunes = $open_ai->listFineTunes();
```

### Retrieve fine-tune

 ```php
$fine_tune = $open_ai->retrieveFineTune('ft-AF1WoRqd3aJAHsqc9NY7iL8F');
```

### Cancel fine-tune

 ```php
$result = $open_ai->cancelFineTune('ft-AF1WoRqd3aJAHsqc9NY7iL8F');
```

### List fine-tune events

 ```php
$fine_tune_events = $open_ai->listFineTuneEvents('ft-AF1WoRqd3aJAHsqc9NY7iL8F');
```

### Delete fine-tune model

 ```php
$result = $open_ai->deleteFineTune('curie:ft-acmeco-2021-03-03-21-44-20');
```

## Retrieve engine

**_(Deprecated)_**

Retrieves an engine instance, providing basic information about the engine such as the owner and availability.

 ```php
$engine = $open_ai->engine('davinci');
```

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
$result = $open_ai->retrieveModel("text-ada-001");
```

## Printing results *i.e.* `$search`

 ```php
echo $search;
```

## Testing

To run all tests:

```bash
composer test
```

To run only those tests that work for most user (exclude those that require a missing folder or that hit deprecated
endpoints no longer available to most users):

```bash
./vendor/bin/pest --group=working
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

