<?php

namespace Dwoodard\LaravelOllama;

use Illuminate\Support\Facades\Http;
use Swaggest\JsonSchema\Schema;

class Ollama
{
    public $model;

    public $system;

    public $prompt;

    public $suffix;

    public $images;

    public $format;

    public $options;

    public $template;

    public $stream;

    public $raw;

    public $keep_alive;

    public $context;

    public function __construct()
    {
        $this->api_url = config('laravel-ollama.api_url', 'http://localhost:11434');
        $this->model = env('OLLAMA_MODEL', 'llama3.2:latest');
        $this->system = 'you are a help assistant';
        $this->prompt = null;
        $this->format = 'json';
        $this->stream = 'false';
        $this->raw = null;
        $this->keep_alive = null;
        $this->context = null;
        $this->options = null;
        $this->template = null;
        $this->images = null;
        $this->suffix = null;
    }

    // Initialize and return a new instance
    public static function init(
        $model = null,
        $prompt = '',
        $format = null,
        $stream = false,
        $suffix = null,
        $images = null,
        $options = null,
        $system = null,
        $template = null,
        $raw = null,
        $keep_alive = null,
        $context = null
    ) {
        $ollama = new self;

        $ollama->model($model ? $model : env('OLLAMA_MODEL', 'llama3.2:latest'));
        $ollama->prompt($prompt);
        if ($system) {
            $ollama->system($system);
        }
        if ($suffix) {
            $ollama->suffix($suffix);
        }
        if ($images) {
            $ollama->images($images);
        }
        if ($format) {
            $ollama->format($format);
        }
        if ($options) {
            $ollama->options($options);
        }
        if ($template) {
            $ollama->template($template);
        }
        if ($stream) {
            $ollama->stream($stream);
        }
        if ($raw) {
            $ollama->raw($raw);
        }
        if ($keep_alive) {
            $ollama->keep_alive($keep_alive);
        }
        if ($context) {
            $ollama->context($context);
        }

        return $ollama;
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
    public function format($format)
    {

        if ($format === null || $format === 'json') {
            $this->format = $format;

            return $this;
        }

        if (is_string($format) && $format !== 'json') {

            $schema = new Schema(collect(json_decode($format))->toArray());

            if ($schema->validate(json_decode($format))) {
                $this->format = $format;

                return $this;
            }
        }

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

    // Example method to demonstrate chaining
    public function generate()
    {
        $data = collect(self::init()
            ->model($this->model)
            ->prompt($this->prompt)
        )->filter(fn ($value) => ! is_null($value))
            ->toArray();

        $response = Http::post(config('laravel-ollama.api_url', 'http://localhost:11434').'/api/generate',
            [
                'model' => $this->model,
                'prompt' => $this->prompt,
                'suffix' => $this->suffix,
                'images' => $this->images,
                'format' => $this->format,
                'options' => $this->options,
                'template' => $this->template,
                'stream' => false,
                'raw' => $this->raw,
                'keep_alive' => $this->keep_alive,
                'context' => $this->context,
            ]
        );

        return $response;

    }
}
