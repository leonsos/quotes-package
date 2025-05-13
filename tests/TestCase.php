<?php

namespace QuotesPackage\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Orchestra\Testbench\Concerns\CreatesApplication;
use QuotesPackage\QuotesServiceProvider;
use Illuminate\Support\Facades\Cache;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function getPackageProviders($app): array
    {
        return [
            QuotesServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Configuración específica para pruebas
        $app['config']->set('quotes.api_url', 'https://dummyjson.com');
        $app['config']->set('quotes.cache_duration', 60);
        $app['config']->set('quotes.rate_limit_max_requests', 30);
        $app['config']->set('quotes.rate_limit_window', 60);
        $app['config']->set('quotes.routes_enabled', true);
        $app['config']->set('quotes.routes_prefix', 'api/quotes');
        
        // Usar driver de cache array para pruebas
        $app['config']->set('cache.default', 'array');
    }
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Limpiar caché antes de cada prueba
        Cache::flush();
    }
}
