<?php

namespace Dwoodard\LaravelOllama;

use Illuminate\Support\Facades\Http;
use Swaggest\JsonSchema\Schema;
use Illuminate\Http\Client\PendingRequest;
use Mockery\Generator\Method;

class Ollama
{
    protected PendingRequest $http;

    public string $model;

    public ?string $system = 'you are a help assistant';

    public ?string $prompt = null;

    public ?string $suffix = null;

    public ?array $images = null;

    public $format = 'json';

    public bool $stream = false;

    public ?array $options = null;

    public ?string $template = null;

    public ?bool $raw = null;

    public ?int $keep_alive = null;

    public ?string $context = null;

    public function __construct(PendingRequest $http)
    {
        $this->http = $http;
        $this->api_url = config('laravel-ollama.api_url', 'http://localhost:11434');
        $this->model = env('OLLAMA_MODEL', 'llama3.2:latest');
    }

    // Initialize and return a new instance with dynamic parameters
    public static function init(array $params = [], PendingRequest $http = null): self
    {
        $instance = new self($http ?? app('ollama.http'));

        foreach ($params as $key => $value) {
            if (property_exists($instance, $key)) {
                $method = $key;

                if ($key === 'format') {
                    // If value is null, set format to null and continue
                    if ($value === null) {
                        $instance->format = null;
                        continue;
                    }

                    // If the value is exactly 'json', assign it and continue
                    if ($value === 'json') {
                        $instance->format = $value;
                        continue;
                    }

                    // If value is valid JSON, decode to an array
                    if (self::isValidJson($value)) {
                        $instance->format = json_decode($value, true);
                        continue;
                    }

                    throw new \Exception('Invalid JSON format provided.');
                }

                if (method_exists($instance, $method)) {
                    $instance->$method($value);
                } else {
                    $instance->$key = $value;
                }
            }
        }

        return $instance;
    }



    // Modify the generate method to return an array
    public function generate(): array
    {
        $payload = collect(get_object_vars($this))
            ->except(['http']) // Exclude the injected client
            ->filter(fn($value) => !is_null($value))
            ->toArray();

        $ollama = $this->http->post('/api/generate', $payload);

        $responseArray = $ollama->json(); // Get entire response as array
        if (!array_key_exists('response', $responseArray)) {
            return $responseArray;
        }

        // If 'response' is text, check if it's valid JSON
        if (!self::isValidJson($responseArray['response'])) {
            return $responseArray;
        }

        // Otherwise decode 'response' into an array
        return json_decode($responseArray['response'], true);
    }

    private static function isValidJson($string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
