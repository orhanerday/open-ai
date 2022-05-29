# OpenAI GPT-3 Api Client in PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/orhanerday/open-ai.svg?style=flat-square)](https://packagist.org/packages/orhanerday/open-ai)
[![Total Downloads](https://img.shields.io/packagist/dt/orhanerday/open-ai.svg?style=flat-square)](https://packagist.org/packages/orhanerday/open-ai)

<br />

<br />

<img src="./openai-elephpant.svg" width="1250" height="300" alt="orhanerday-open-ai-logo">

<br />

<br />

<br />

> #### For more information, you can read laravel news [blog post](https://laravel-news.com/openai-sdk-for-php).
> #### To get started with this package, you'll first want to be familiar with the [OpenAI API documentation](https://beta.openai.com/docs/introduction) and [examples](https://beta.openai.com/examples).

## News
orhanerday/open-ai added to community libraries php [section](https://beta.openai.com/docs/libraries/php). 

## Installation

You can install the package via composer:

```bash
composer require orhanerday/open-ai
```

## Usage

### Load your key from an environment variable.
> According to the following code `$open_ai` is the base variable for all open-ai operations. 
```php
use Orhanerday\OpenAi\OpenAi;

$open_ai = new OpenAi(env('OPEN_AI_API_KEY'));
```
## Completions
 Given a prompt, the model will return one or more predicted completions, and can also return the probabilities of alternative tokens at each position.

 ```php
$complete = $open_ai->complete([
    'engine' => 'davinci',
    'prompt' => 'Hello',
    'temperature' => 0.9,
    'max_tokens' => 150,
    'frequency_penalty' => 0,
    'presence_penalty' => 0.6,
]);
```


## Searches
Given a query and a set of documents or labels, the model ranks each document based on its semantic similarity to the provided query.
```php
$search = $open_ai->search([
    'engine' => 'ada',
    'documents' => ['White House', 'hospital', 'school'],
    'query' => 'the president',
]);
```

## Answers
 Given a question, a set of documents, and some examples, the API generates an answer to the question based on the information in the set of documents. This is useful for question-answering applications on sources of truth, like company documentation or a knowledge base.
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
Given a query and a set of labeled examples, the model will predict the most likely label for the query. Useful as a drop-in replacement for any ML classification or text-to-label task.
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

## List engines
Lists the currently available engines, and provides basic information about each one such as the owner and availability.
 ```php
$engines = $open_ai->engines();
```
## Files
Files are used to upload documents that can be used across features like Answers, Search, and Classifications
## List files
Returns a list of files that belong to the user's organization.
```php
$open_ai->listFiles();
```
## Upload file
Upload a file that contains document(s) to be used across various endpoints/features. Currently, the size of all the files uploaded by one organization can be up to 1 GB. Please contact OpenAI if you need to increase the storage limit.
```php
$c_file = curl_file_create(__DIR__ . 'files/sample_file_1.jsonl');
$open_ai->uploadFile([
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

use Orhanerday\OpenAi\OpenAi;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    ob_clean();
    $open_ai = new OpenAi('YOUR_API_KEY');
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
## Delete file
 ```php
$open_ai->deleteFile('file-xxxxxxxx');
```

## Retrieve file
 ```php
$open_ai->deleteFile('file-xxxxxxxx');
```

## Retrieve engine
Retrieves an engine instance, providing basic information about the engine such as the owner and availability.
 ```php
$engine = $open_ai->engine('davinci');
```

## Printing results *i.e.* `$search`
 ```php
echo $search;
```

## Testing

```bash
composer test
```

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

