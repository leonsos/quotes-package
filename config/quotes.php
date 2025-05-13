<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | La URL base de la API de cotizaciones.
    |
    */
    'api_url' => env('QUOTES_API_URL', 'https://dummyjson.com'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configuración para limitar las solicitudes a la API.
    |
    */
    'rate_limit_max_requests' => env('QUOTES_API_RATE_LIMIT_MAX', 30),
    'rate_limit_window' => env('QUOTES_API_RATE_LIMIT_WINDOW', 60), // en segundos

    /*
    |--------------------------------------------------------------------------
    | Caché
    |--------------------------------------------------------------------------
    |
    | Configuración relacionada con el almacenamiento en caché.
    |
    */
    'cache_duration' => env('QUOTES_CACHE_DURATION', 1440), // en minutos (1 día)

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | Configuración para las rutas del paquete.
    |
    */
    'routes_enabled' => true,
    'routes_prefix' => 'api/quotes',
    'ui_route' => 'quotes-ui',
];
