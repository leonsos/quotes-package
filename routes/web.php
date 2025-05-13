<?php

use Illuminate\Support\Facades\Route;
use QuotesPackage\Controllers\QuotesViewController;

// Ruta para la interfaz de usuario
Route::get(config('quotes.ui_route', 'quotes-ui'), [QuotesViewController::class, 'index'])
    ->name('quotes.ui');
