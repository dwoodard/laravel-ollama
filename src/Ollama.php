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

    public ?string $format = 'json';

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

                // Handle the case where format is explicitly set to null
                if ($key === 'format' && $value === null) {
                    $instance->format = null;
                    continue;
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

    // Set the model and return the instance
    public function model($modelName)
    {
        $this->model = $modelName;

        return $this;
    }

    // Set the system message and return the instance
    public function system($message)
    {
        $this->system = $message;

        return $this;
    }

    // Set the prompt and return the instance
    public function prompt($prompt)
    {
        $this->prompt = $prompt;

        return $this;
    }

    // Set the suffix and return the instance
    public function suffix($suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }

    // Set the images and return the instance
    public function images($images)
    {
        $this->images = $images;

        return $this;
    }

    // Set the format and return the instance
    public function format(?string $format): self
    {
        // if format is null, return the instance
        if ($format === null) {
            return $this;
        }

        if ($format !== 'json' && !$this->isValidJson($format)) {
            throw new \InvalidArgumentException('Invalid JSON format provided.');
        }
        $this->format = $format;

        return $this;
    }

    // Set the options and return the instance
    public function options($options)
    {
        $this->options = $options;

        return $this;
    }

    // Set the template and return the instance
    public function template($template)
    {
        $this->template = $template;

        return $this;
    }

    // Set the stream and return the instance
    public function stream($stream)
    {
        $this->stream = $stream;

        return $this;
    }

    // Set the raw and return the instance
    public function raw($raw)
    {
        $this->raw = $raw;

        return $this;
    }

    // Set the keep_alive and return the instance
    public function keep_alive($keep_alive)
    {
        $this->keep_alive = $keep_alive;

        return $this;
    }

    // Set the context and return the instance
    public function context($context)
    {
        $this->context = $context;

        return $this;
    }

    // Modify the generate method to return an array
    public function generate(): array
    {
        $payload = collect(get_object_vars($this))
            ->except(['http']) // Exclude the injected client
            ->filter(fn($value) => !is_null($value))
            ->toArray();


        $response = $this->http->post('/api/generate', $payload);

        return collect($response->json())->toArray();
    }

    private function isValidJson($string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
