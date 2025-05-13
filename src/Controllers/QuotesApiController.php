<?php

namespace QuotesPackage\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use QuotesPackage\Services\QuotesApiService;
use Illuminate\Support\Facades\Cache;

class QuotesApiController extends Controller
{
    protected QuotesApiService $quotesService;

    public function __construct(QuotesApiService $quotesService)
    {
        $this->quotesService = $quotesService;
    }

    /**
     * Obtiene todas las citas.
     */
    public function index(): JsonResponse
    {
        $fromCache = Cache::has('quotes_all');
        $quotes = $this->quotesService->getAllQuotes();
        
        return response()->json([
            'quotes' => $quotes,
            'total' => $quotes->count(),
            'skip' => 0,
            'limit' => $quotes->count()
        ])->header('X-Cache', $fromCache ? 'HIT' : 'MISS');
    }

    /**
     * Obtiene una cita aleatoria.
     */
    public function random(): JsonResponse
    {
        $cacheKey = 'quotes_random_' . now()->format('Y-m-d-H');
        $fromCache = Cache::has($cacheKey);
        
        $quote = $this->quotesService->getRandomQuote();
        
        if ($quote) {
            return response()->json($quote)
                ->header('X-Cache', $fromCache ? 'HIT' : 'MISS');
        }
        
        return response()->json(['error' => 'No se pudo obtener una cita aleatoria'], 404);
    }

    /**
     * Obtiene una cita por ID.
     */
    public function show(int $id): JsonResponse
    {
        $cacheKey = "quotes_id_{$id}";
        $fromCache = Cache::has($cacheKey);
        
        $quote = $this->quotesService->getQuote($id);
        
        if ($quote) {
            return response()->json($quote)
                ->header('X-Cache', $fromCache ? 'HIT' : 'MISS');
        }
        
        return response()->json(['error' => 'Cita no encontrada'], 404);
    }

    /**
     * Limpia la caché de citas
     */
    public function clearCache(): JsonResponse
    {
        $this->quotesService->clearCache();
        return response()->json(['success' => true, 'message' => 'Caché limpiado correctamente']);
    }
}
