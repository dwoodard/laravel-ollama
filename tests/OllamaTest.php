<?php

namespace Dwoodard\LaravelOllama\Tests;

use Dwoodard\LaravelOllama\Facades\LaravelOllamaFacade as Ollama;
use Orchestra\Testbench\TestCase; // Added use statement
use Illuminate\Support\Facades\Http;
use Swaggest\JsonSchema\JsonSchema;

use function Pest\Laravel\json;

class OllamaTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \Dwoodard\LaravelOllama\LaravelOllamaServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [

            'Ollama' => \Dwoodard\LaravelOllama\Facades\LaravelOllamaFacade::class,

        ];
    }

    /** @test
     * it can access the api url
     **/
    public function it_can_access_the_api_url()
    {
        $apiUrl = env('OLLAMA_API_URL', 'http://localhost:11434');
        $this->assertEquals('http://localhost:11434', $apiUrl);
    }

    /** @test
     * it can access the api url
     **/
    public function it_can_access_the_model()
    {
        $model = env('OLLAMA_MODEL', 'llama3.2:latest');
        $this->assertEquals('llama3.2:latest', $model);
    }

    /** @test
     * it can access the facade
     **/
    public function it_access_the_facade()
    {
        Http::fake();
        $this->assertInstanceOf(\Dwoodard\LaravelOllama\Ollama::class, Ollama::init());
    }

    /** @test
     * it set model as a parameter
     **/
    public function it_sets_model()
    {
        Http::fake();
        $model = 'llama3.2:latest';

        $ollama = Ollama::init(['model' => $model]); // Passed as array
        $this->assertEquals($ollama->model, $model);
    }

    /** @test
     * it can access the api url
     **/
    public function it_can_return_a_response()
    {
        Http::fake([
            'http://localhost:11434/api/generate' => Http::response([
                'model' => 'llama3.2:latest',
                'created_at' => now()->toDateTimeString(),
                'response' => 'The sky is blue because...',
                'done' => true,
            ], 200),
        ]);

        $prompt = 'why is the sky blue?';

        $response = Ollama::init([
            'model' => 'llama3.2:latest',
            'prompt' => $prompt
        ])->generate();

        $this->assertArrayHasKey('response', $response);

        $this->assertEquals('llama3.2:latest', $response['model']);
    }

    /** @test
     * it handles null format gracefully
     **/
    public function it_handles_null_format_gracefully()
    {
        $ollama = Ollama::init([
            'prompt' => 'tell me a story',
            'system' => 'you are a grate storyteller, but return as json',
            'format' => null,
        ])->generate();

        $this->assertIsString($ollama['response']);
        $this->assertNotEmpty($ollama['response']);
    }

    /** @test
     * it handles json format gracefully
     **/
    public function it_handles_json_format_gracefully()
    {
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

        $ollama = Ollama::init(
            [
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
            ]
        )->generate();

        $this->assertIsArray($ollama);
        $this->assertArrayHasKey('firstName', $ollama);
        $this->assertArrayHasKey('lastName', $ollama);
        $this->assertArrayHasKey('age', $ollama);
        $this->assertArrayHasKey('backstory', $ollama);
    }

    /** @test */
    public function it_throws_exception_for_invalid_json_schema()
    {
        $invalidSchema = '{ "type": "object", "broken": }'; // Deliberate syntax error
        $this->expectException(\Exception::class);
        Ollama::init([
            'format' => $invalidSchema,
            'prompt' => 'test prompt',
        ]);
    }

    /** @test */
    public function it_handles_missing_required_fields_gracefully()
    {
        Http::fake([
            'http://localhost:11434/api/generate' => Http::response([
                // Missing 'firstName' for example
                'lastName' => 'MissingFirstName',
                'age' => 30,
            ], 200),
        ]);

        $response = Ollama::init([
            'prompt' => 'Create a fictional character with a firstName.',
            'format' => json_encode([
                'type' => 'object',
                'properties' => [
                    'firstName' => ['type' => 'string'],
                    'lastName' => ['type' => 'string'],
                ],
                'required' => ['firstName'],
            ]),
        ])->generate();

        $this->assertArrayHasKey('lastName', $response);
        $this->assertArrayNotHasKey('firstName', $response); // verify missing
    }

    /** @test */
    public function it_handles_large_json_schema()
    {
        $largeSchema = [
            "type" => "object",
            "properties" => array_fill_keys(
                range(1, 100),
                ["type" => "string"]
            ),
            "required" => ["1", "50", "100"]
        ];

        $ollama = Ollama::init([
            'prompt' => 'Generate detailed information',
            'format' => json_encode($largeSchema),
        ])->generate();

        $this->assertIsArray($ollama);
        $this->assertArrayHasKey('1', $ollama);
        $this->assertArrayHasKey('50', $ollama);
        $this->assertArrayHasKey('100', $ollama);
    }

    /** @test */
    public function it_handles_invalid_model()
    {
        Http::fake([
            'http://localhost:11434/api/generate' => Http::response([
                "error" => "model 'unknown-model' not found"
            ], 400),
        ]);

        $ollama = Ollama::init([
            'model' => 'unknown-model',
            'prompt' => 'Who are you?',
        ])->generate();

        $this->assertArrayHasKey('error', $ollama);
    }
}
