# OpenAI GPT-3 Api Client in PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/orhanerday/open-ai.svg?style=flat-square)](https://packagist.org/packages/orhanerday/open-ai)
[![Total Downloads](https://img.shields.io/packagist/dt/orhanerday/open-ai.svg?style=flat-square)](https://packagist.org/packages/orhanerday/open-ai)

OpenAI GPT-3 Api Client in PHP
## Installation

You can install the package via composer:

```bash
composer require orhanerday/open-ai
```

## Usage

```php

use Orhanerday\OpenAi\OpenAi;

$open_ai = new Orhanerday\OpenAi('OPEN-AI-KEY');

$result = $open_ai->complete([
        'engine' => 'davinci',
        'prompt' => "Hello",
        'temperature' => 0.9,
        "max_tokens" => 150,
        "frequency_penalty" => 0,
        "presence_penalty" => 0.6,
    ]);

dd($result)
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
