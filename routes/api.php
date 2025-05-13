<?php

use Illuminate\Support\Facades\Route;
use QuotesPackage\Controllers\QuotesApiController;

Route::prefix(config('quotes.routes_prefix', 'api/quotes'))
    ->middleware(['api', 'quotes.ratelimit'])
    ->group(function () {
        Route::get('/', [QuotesApiController::class, 'index']);
        Route::get('/random', [QuotesApiController::class, 'random']);
        Route::get('/{id}', [QuotesApiController::class, 'show'])->where('id', '[0-9]+');
        Route::post('/clear-cache', [QuotesApiController::class, 'clearCache']);
    });
