<?php

namespace QuotesPackage;

use Illuminate\Support\ServiceProvider;
use QuotesPackage\Services\QuotesApiService;
use Illuminate\Support\Facades\Route;

class QuotesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        // Publicar configuración
        $this->publishes([
            __DIR__.'/../config/quotes.php' => config_path('quotes.php'),
        ], 'quotes-config');

        // Publicar assets compilados
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/quotes'),
        ], 'quotes-assets');

        // Publicar vistas
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/quotes'),
        ], 'quotes-views');

        // Publicar componentes Vue para desarrollo
        $this->publishes([
            __DIR__.'/../resources/js' => resource_path('js/vendor/quotes'),
        ], 'quotes-vue-sources');

        // Cargar rutas
        if (config('quotes.routes_enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        // Cargar vistas
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'quotes');
    }

    /**
     * Register any package services.
     */
    public function register(): void
    {
        // Combinar configuración
        $this->mergeConfigFrom(
            __DIR__.'/../config/quotes.php', 'quotes'
        );

        // Registrar servicio de API
        $this->app->singleton(QuotesApiService::class, function ($app) {
            return new QuotesApiService();
        });

        // Registrar middleware
        $this->app['router']->aliasMiddleware(
            'quotes.ratelimit', 
            \QuotesPackage\Middleware\QuotesRateLimitMiddleware::class
        );
    }
}
