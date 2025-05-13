<?php

namespace QuotesPackage\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class QuotesRateLimitMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $config = config('quotes');
        $maxRequests = $config['rate_limit_max_requests'] ?? 30;
        $timeWindow = $config['rate_limit_window'] ?? 60;
        
        // Crear una clave única por IP
        $key = 'quotes_api_' . ($request->ip() ?? 'unknown');
        
        // Verificar y aplicar el límite
        if (RateLimiter::tooManyAttempts($key, $maxRequests)) {
            return response()->json([
                'error' => 'Demasiadas solicitudes. Por favor, inténtalo de nuevo más tarde.',
                'retry_after' => RateLimiter::availableIn($key)
            ], 429)->withHeaders([
                'Retry-After' => RateLimiter::availableIn($key),
                'X-RateLimit-Limit' => $maxRequests,
                'X-RateLimit-Remaining' => 0,
            ]);
        }
        
        // Incrementar el contador
        RateLimiter::hit($key, $timeWindow);
        
        // Agregar cabeceras de rate limit
        $response = $next($request);
        $response->headers->add([
            'X-RateLimit-Limit' => $maxRequests,
            'X-RateLimit-Remaining' => RateLimiter::remaining($key, $maxRequests),
        ]);
        
        return $response;
    }
} 