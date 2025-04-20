# Laravel Ollama

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dwoodard/laravel-ollama.svg?style=flat-square)](https://packagist.org/packages/dwoodard/laravel-ollama)
[![Total Downloads](https://img.shields.io/packagist/dt/dwoodard/laravel-ollama.svg?style=flat-square)](https://packagist.org/packages/dwoodard/laravel-ollama)
![GitHub Actions](https://github.com/dwoodard/laravel-ollama/actions/workflows/main.yml/badge.svg)

This is where your description should go. Try and limit it to a paragraph or two, and maybe throw in a mention of what PSRs you support to avoid any confusion with users and contributors.

## Installation

You can install the package via composer:

```bash
composer require dwoodard/laravel-ollama
```

```bash
// add config/laravel-ollama.php
php artisan vendor:publish --tag=laravel-ollama
```

## Usage

```php
 $ollama = Ollama::init([
        'prompt' => 'where are you?',
        'format' => null
    ])->generate();
```

```php
$ollama = Ollama::init([
            'prompt' => 'tell me a story',
            'system' => 'you are a great storyteller',
            'format' => null,
        ])->generate();
```

## Examples

Use the facade to generate a simple response:

```php
$response = \Dwoodard\LaravelOllama\Facades\LaravelOllamaFacade::init([
    'model' => 'llama3.2:latest',
    'prompt' => 'why is the sky blue?',
])->generate();

echo $response['response']; // "The sky is blue because..."
```

```php
$personSchema = [
        "type" => "object",
        "properties" => [
            "firstName" => ["type" => "string"],
            "lastName" => ["type" => "string"],
            "age" => ["type" => "integer", "minimum" => 20],
            "backstory" => ["type" => "string"],
            'details' => [
                'type' => 'object',
                'properties' => [
                    'height' => ['type' => 'integer'],
                    'weight' => ['type' => 'integer'],
                    'hairColor' => ['type' => 'string'],
                    'eyeColor' => ['type' => 'string'],
                ],
            ]
        ],
        "required" => [
            'firstName',
            'lastName',
            "age",
            "backstory"
        ]
    ];


    $ollama = Ollama::init([
        'prompt' => 'Create a fictional character profile with the following details: 
                    1. A first name.
                    2. A last name.
                    3. An age (must be 20 or older).
                    Ensure the response strictly follows this JSON format: 
                    {
                        "firstName": "John",
                        "lastName": "Doe",
                        "age": 30
                    } Respond using JSON.',
        'system' => 'You are a great storyteller. response strictly in JSON format without additional text.',
        'format' => json_encode($personSchema),
    ])->generate();

    return $ollama;

```

Set a specific model parameter:

```php
$ollama = \Dwoodard\LaravelOllama\Facades\LaravelOllamaFacade::init(['model' => 'llama3.2:latest']);
echo $ollama->model; // "llama3.2:latest"
```

Handle a JSON schema-based response:

```php
$personSchema = [
    "type" => "object",
    // ...existing code...
];

$ollama = \Dwoodard\LaravelOllama\Facades\LaravelOllamaFacade::init([
    'prompt' => 'Create a character profile strictly in JSON format',
    'format' => json_encode($personSchema),
])->generate();

// $ollama will be an array with validated JSON fields 
```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email <dustin.woodard@gmil.com> instead of using the issue tracker.

## Credits

- [Dustin Woodard](https://github.com/dwoodard)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
