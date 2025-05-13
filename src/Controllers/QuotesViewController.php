<?php

namespace QuotesPackage\Controllers;

use Illuminate\Routing\Controller;

class QuotesViewController extends Controller
{
    /**
     * Muestra la interfaz de usuario de citas.
     */
    public function index()
    {
        // Pasar datos de configuración a la vista
        return view('quotes::quotes', [
            'appConfig' => [
                'apiBaseUrl' => url(config('quotes.routes_prefix', 'api/quotes')),
                'appTitle' => config('quotes.app_title', 'API de Cotizaciones')
            ]
        ]);
    }
}
