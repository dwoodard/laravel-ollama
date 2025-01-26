<?php

namespace Dwoodard\LaravelOllama\Tests;

use Dwoodard\LaravelOllama\Facades\LaravelOllamaFacade as Ollama;
use Orchestra\Testbench\TestCase; // Added use statement
use Illuminate\Support\Facades\Http;

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
        ])->generate(); // Passed as array

        $this->assertArrayHasKey('model', $response);
        $this->assertArrayHasKey('created_at', $response);
        $this->assertArrayHasKey('response', $response);
        $this->assertArrayHasKey('done', $response);

        $this->assertEquals('llama3.2:latest', $response['model']);
    }



    /** @test
     * it validates the JSON response against a schema
     **/
    public function it_validates_json_response_against_schema()
    {
        Http::fake([
            'http://localhost:11434/api/generate' => Http::response([
                'model' => 'llama3.2:latest',
                'created_at' => now()->toDateTimeString(),
                'response' => json_encode([
                    'times' => [
                        (object)['time' => 'morning', 'sky_color' => 'blue'],
                        (object)['time' => 'noon', 'sky_color' => 'light blue'],
                        (object)['time' => 'afternoon', 'sky_color' => 'dark blue'],
                        (object)['time' => 'evening', 'sky_color' => 'orange'],
                    ],
                ]),
                'done' => true,
            ], 200),
        ]);

        $response = Ollama::init([
            'model' => 'llama3.2:latest',
            'prompt' => 'What color is the sky at different times of the day?',
            'format' => 'json',
            'stream' => false,
            'options' => [
                'temperature' => 0,
            ],
        ])->generate();

        $responseData = is_string($response['response']) ? json_decode($response['response']) : $response['response'];

        // Load the JSON schema from a file or define inline
        $schemaData = json_decode('{
        "type": "object",
        "properties": {
            "times": {
                "type": "array",
                "items": {
                    "type": "object",
                    "properties": {
                        "time": { "type": "string" },
                        "sky_color": { "type": "string" }
                    },
                    "required": ["time", "sky_color"]
                }
            }
        },
        "required": ["times"]
    }');

        try {
            $schema = \Swaggest\JsonSchema\Schema::import($schemaData);
            $schema->in($responseData);  // Validate as an object
            $this->assertTrue(true, 'The JSON response matches the schema.');
        } catch (\Swaggest\JsonSchema\Exception\ValidationException $e) {
            $this->fail('JSON response does not match schema: ' . $e->getMessage());
        }
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
}
