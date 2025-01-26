<?php

namespace Dwoodard\LaravelOllama\Tests;

use Dwoodard\LaravelOllama\Facades\LaravelOllamaFacade as Ollama;
use Orchestra\Testbench\TestCase; // Added use statement

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
        $this->assertInstanceOf(\Dwoodard\LaravelOllama\Ollama::class, Ollama::init());
    }

    /** @test
     * it set model as a parameter
     **/
    public function it_sets_model()
    {
        $model = 'llama3.2:latest';

        $ollama = \Ollama::init(model: $model); // Changed from model to modelName
        $this->assertEquals($ollama->model, $model);
    }

    /** @test
     * it can access the api url
     **/
    public function it_can_return_a_response()
    {
        $prompt = 'why is the sky blue?';
        $request = Ollama::init(
            model: 'llama3.2:latest',
            prompt: $prompt
        )->generate();

        $response = $request->json();

        $this->assertArrayHasKey('model', $response);
        $this->assertArrayHasKey('created_at', $response);
        $this->assertArrayHasKey('response', $response);
        $this->assertArrayHasKey('done', $response);

        $this->assertEquals('llama3.2:latest', $response['model']);
        

        
    }
}
