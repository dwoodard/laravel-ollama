<?php

namespace Dwoodard\LaravelOllama\Tests;

use Orchestra\Testbench\TestCase;

class OllamaTest extends TestCase
{
    
    

    /** @test
     * it can access the api url
     * */
    public function it_can_access_the_api_url()
    {
        $apiUrl =env('OLLAMA_API_URL', 'http://localhost:11434');
 $this->assertEquals('http://localhost:11434', $apiUrl); 
    }
}
