<?php

namespace Dwoodard\LaravelOllama;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;

class LaravelOllamaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-ollama');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-ollama');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('laravel-ollama.php'),
            ], 'laravel-ollama');

            // php artisan vendor:publish --provider="Dwoodard\LaravelOllama\LaravelOllamaServiceProvider" --tag="laravel-ollama"

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-ollama'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/laravel-ollama'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-ollama'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'laravel-ollama');

        $this->app->singleton('ollama.http', function ($app) {
            return Http::withOptions([
                'base_uri' => config('laravel-ollama.api_url'),
                // ...other default options...
            ])->withToken(config('laravel-ollama.api_token', ''));
        });

        // Remove the old no-argument instantiation
        // $this->app->singleton('Ollama', function () {
        //     return new Ollama;
        // });

        // Replace with an instantiation that injects the HTTP client
        $this->app->singleton('Ollama', function ($app) {
            return new Ollama($app->make('ollama.http'));
        });

        // Optionally unify the class binding
        $this->app->singleton(Ollama::class, function ($app) {
            return $app->make('Ollama');
        });
    }
}
