<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'api_url' => env('OLLAMA_API_URL', 'http://localhost:11434'),
    'model' => env('OLLAMA_MODEL', 'llama3.2:latest'),
];
