<?php

namespace QuotesPackage\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class QuotesApiService
{
    protected array $cachedQuotes = [];
    protected int $lastRequestTimestamp = 0;
    protected int $requestCount = 0;

    public function __construct()
    {
        // Cargar las citas en caché al inicializar el servicio
        $this->loadCachedQuotes();
    }

    /**
     * Obtiene todas las cotizaciones de la API
     */
    public function getAllQuotes(): Collection
    {
        // Verificar si está en caché del sistema
        if (Cache::has('quotes_all')) {
            return collect(Cache::get('quotes_all'));
        }
        
        $this->enforceRateLimit();

        $response = Http::get($this->getBaseUrl() . '/quotes');
        
        if ($response->successful()) {
            $quotes = collect($response->json('quotes'));
            
            // Guardar en caché local y del sistema
            $this->cacheQuotes($quotes);
            Cache::put('quotes_all', $quotes->toArray(), now()->addMinutes(config('quotes.cache_duration', 60)));
            
            return $quotes;
        }
        
        return collect();
    }

    /**
     * Obtiene una cita aleatoria
     */
    public function getRandomQuote(): ?array
    {
        // Generar una clave única para cada solicitud
        $cacheKey = 'quotes_random_' . uniqid();
        
        $this->enforceRateLimit();
        
        $response = Http::get($this->getBaseUrl() . '/quotes/random');
        
        if ($response->successful()) {
            $quote = $response->json();
            // Podemos seguir guardando en caché brevemente para las 
            // solicitudes masivas inmediatas (5 segundos)
            Cache::put($cacheKey, $quote, now()->addSeconds(5));
            
            return $quote;
        }
        
        return null;
    }

    /**
     * Obtiene una cita específica por ID utilizando búsqueda binaria en caché
     */
    public function getQuote(int $id): ?array
    {
        // Primero intentar buscar en caché con búsqueda binaria
        $quote = $this->findQuoteInCacheById($id);
        
        if ($quote) {
            return $quote;
        }
        
        // Si no está en caché, buscar en la API
        $this->enforceRateLimit();
        
        $cacheKey = "quotes_id_{$id}";
        
        // Verificar caché del sistema
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        $response = Http::get($this->getBaseUrl() . "/quotes/{$id}");
        
        if ($response->successful()) {
            $quote = $response->json();
            
            // Guardar en ambas cachés
            $this->cachedQuotes[] = $quote;
            
            // Ordenar por ID para mantener la eficiencia de la búsqueda binaria
            usort($this->cachedQuotes, function($a, $b) {
                return $a['id'] <=> $b['id'];
            });
            
            // Guardar en caché del sistema
            Cache::put($cacheKey, $quote, now()->addMinutes(config('quotes.cache_duration', 60)));
            
            // Guardar la lista de claves para limpieza posterior
            $idKeys = Cache::get('quotes_id_keys', []);
            $idKeys[] = $cacheKey;
            Cache::put('quotes_id_keys', $idKeys, now()->addDay());
            
            return $quote;
        }
        
        return null;
    }

    /**
     * Busca una cita en la caché local utilizando búsqueda binaria
     */
    protected function findQuoteInCacheById(int $id): ?array
    {
        if (empty($this->cachedQuotes)) {
            return null;
        }
        
        $low = 0;
        $high = count($this->cachedQuotes) - 1;
        
        while ($low <= $high) {
            $mid = (int)(($low + $high) / 2);
            $midQuote = $this->cachedQuotes[$mid];
            
            if ($midQuote['id'] === $id) {
                return $midQuote;
            }
            
            if ($midQuote['id'] < $id) {
                $low = $mid + 1;
            } else {
                $high = $mid - 1;
            }
        }
        
        return null;
    }

    /**
     * Almacena citas en caché y mantiene ordenado por ID
     */
    protected function cacheQuotes(Collection $quotes): void
    {
        foreach ($quotes as $quote) {
            $this->addToCache($quote);
        }
        
        // Guardar en cache de Laravel para persistencia entre solicitudes
        Cache::put('quotes_cache', $this->cachedQuotes, now()->addDay());
    }
    
    /**
     * Añade una cita a la caché manteniendo el orden
     */
    protected function addToCache(array $quote): void
    {
        // Comprobar si la cita ya existe en la caché
        foreach ($this->cachedQuotes as $index => $cachedQuote) {
            if ($cachedQuote['id'] === $quote['id']) {
                $this->cachedQuotes[$index] = $quote;
                return;
            }
        }
        
        // Añadir la nueva cita
        $this->cachedQuotes[] = $quote;
        
        // Ordenar por ID para mantener la búsqueda binaria funcionando
        usort($this->cachedQuotes, function ($a, $b) {
            return $a['id'] <=> $b['id'];
        });
        
        // Actualizar la cache de Laravel
        Cache::put('quotes_cache', $this->cachedQuotes, now()->addDay());
    }
    
    /**
     * Carga las citas almacenadas en cache
     */
    protected function loadCachedQuotes(): void
    {
        $this->cachedQuotes = Cache::get('quotes_cache', []);
    }

    /**
     * Enforce API rate limiting with adaptive retry
     */
    protected function enforceRateLimit(): void
    {
        $config = config('quotes');
        $maxRequests = $config['rate_limit_max_requests'] ?? 30;
        $timeWindow = $config['rate_limit_window'] ?? 60;
        
        // Verificar si estamos en la misma ventana de tiempo
        $currentTime = time();
        $timeElapsed = $currentTime - $this->lastRequestTimestamp;
        
        // Reset contadores si hemos cambiado de ventana
        if ($timeElapsed >= $timeWindow) {
            $this->requestCount = 0;
            $this->lastRequestTimestamp = $currentTime;
            return;
        }
        
        // Verificar si hemos excedido el límite
        if ($this->requestCount >= $maxRequests) {
            // Calcular tiempo de espera adaptativo
            $waitTime = $timeWindow - $timeElapsed + 1;
            $waitTime = min($waitTime, 10); // Máximo 10 segundos de espera
            
            // Registrar la espera en los logs
            \Log::info("Rate limit excedido. Esperando {$waitTime} segundos antes de reintentar.");
            
            // Esperar y luego reiniciar
            sleep($waitTime);
            $this->requestCount = 0;
            $this->lastRequestTimestamp = time();
            return;
        }
        
        // Incrementar contador
        $this->requestCount++;
    }
    
    /**
     * Obtiene la URL base de la API desde la configuración
     */
    protected function getBaseUrl(): string
    {
        return config('quotes.api_url', 'https://dummyjson.com');
    }

    /**
     * Limpia toda la caché de citas
     */
    public function clearCache(): void
    {
        // Limpiar caché local
        $this->cachedQuotes = [];
        
        // Limpiar caché del sistema
        Cache::forget('quotes_all');
        
        // Limpiar caché de citas aleatorias
        for ($h = 0; $h < 24; $h++) {
            $key = 'quotes_random_' . now()->format('Y-m-d-') . str_pad($h, 2, '0', STR_PAD_LEFT);
            Cache::forget($key);
        }
        
        // Limpiar caché de citas por ID
        $idKeys = Cache::get('quotes_id_keys', []);
        foreach ($idKeys as $key) {
            Cache::forget($key);
        }
        Cache::forget('quotes_id_keys');
        
        \Log::info('Caché de citas limpiado correctamente');
    }
}
