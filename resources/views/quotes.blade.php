<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('quotes.app_title', 'API de Citas') }}</title>
    
    <!-- Cargar directamente los assets compilados del paquete -->
    <script type="module" src="{{ asset('vendor/quotes/js/quotes-app.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('vendor/quotes/css/quotes-app.css') }}">
</head>
<body>
    <?php
    $configData = [
        'apiBaseUrl' => url(config('quotes.routes_prefix', 'api/quotes')),
        'appTitle' => config('quotes.app_title', 'API de Cotizaciones')
    ];
    ?>
    <div id="quotes-app" data-config='{{ json_encode($configData) }}'></div>
</body>
</html>
