<?php

namespace QuotesPackage\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use QuotesPackage\Services\QuotesApiService;
use QuotesPackage\Tests\TestCase;

class QuotesApiServiceTest extends TestCase
{
    protected QuotesApiService $service;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new QuotesApiService();
    }
    
    /** @test */
    public function it_can_get_all_quotes(): void
    {
        // Simular respuesta HTTP
        Http::fake([
            'https://dummyjson.com/quotes' => Http::response([
                'quotes' => [
                    ['id' => 1, 'quote' => 'Cita de prueba 1', 'author' => 'Autor 1'],
                    ['id' => 2, 'quote' => 'Cita de prueba 2', 'author' => 'Autor 2']
                ],
                'total' => 2,
                'skip' => 0,
                'limit' => 30
            ], 200)
        ]);
        
        // Ejecutar método
        $quotes = $this->service->getAllQuotes();
        
        // Verificar resultados
        $this->assertCount(2, $quotes);
        $this->assertSame('Cita de prueba 1', $quotes[0]['quote']);
        $this->assertSame('Autor 2', $quotes[1]['author']);
        
        // Verificar que se guarda en caché
        $this->assertTrue(Cache::has('quotes_all'));
        $this->assertTrue(Cache::has('quotes_cache'));
        
        // Verificar que se hizo la petición HTTP correcta
        Http::assertSent(function ($request) {
            return $request->url() === 'https://dummyjson.com/quotes';
        });
    }
    
    /** @test */
    public function it_can_get_quote_by_id(): void
    {
        // Simular respuesta HTTP
        Http::fake([
            'https://dummyjson.com/quotes/5' => Http::response([
                'id' => 5,
                'quote' => 'Cita específica',
                'author' => 'Autor específico'
            ], 200)
        ]);
        
        // Ejecutar método
        $quote = $this->service->getQuote(5);
        
        // Verificar resultados
        $this->assertIsArray($quote);
        $this->assertSame(5, $quote['id']);
        $this->assertSame('Cita específica', $quote['quote']);
        
        // Verificar que se guarda en caché y en el registro de claves
        $this->assertTrue(Cache::has('quotes_id_5'));
        $idKeys = Cache::get('quotes_id_keys', []);
        $this->assertContainsEquals('quotes_id_5', $idKeys);
        
        // Verificar que se hizo la petición HTTP correcta
        Http::assertSent(function ($request) {
            return $request->url() === 'https://dummyjson.com/quotes/5';
        });
    }
    
    /** @test */
    public function it_can_get_random_quote(): void
    {
        // Simular respuesta HTTP
        Http::fake([
            'https://dummyjson.com/quotes/random' => Http::response([
                'id' => 10,
                'quote' => 'Cita aleatoria',
                'author' => 'Autor aleatorio'
            ], 200)
        ]);
        
        // Ejecutar método
        $quote = $this->service->getRandomQuote();
        
        // Verificar resultados
        $this->assertIsArray($quote);
        $this->assertSame(10, $quote['id']);
        $this->assertSame('Cita aleatoria', $quote['quote']);
        
        // No verificamos caché específico porque usa uniqid() dinámico
        
        // Verificar que se hizo la petición HTTP correcta
        Http::assertSent(function ($request) {
            return $request->url() === 'https://dummyjson.com/quotes/random';
        });
    }
    
    /** @test */
    public function it_can_clear_cache(): void
    {
        // Poner datos en caché
        Cache::put('quotes_all', [['id' => 1]], 60);
        Cache::put('quotes_cache', [['id' => 1]], 60);
        
        // Crear claves de ID y registrarlas
        Cache::put('quotes_id_1', ['id' => 1], 60);
        Cache::put('quotes_id_2', ['id' => 2], 60);
        Cache::put('quotes_id_keys', ['quotes_id_1', 'quotes_id_2'], 60);
        
        // Crear claves de citas aleatorias por hora
        for ($h = 0; $h < 24; $h++) {
            $key = 'quotes_random_' . now()->format('Y-m-d-') . str_pad($h, 2, '0', STR_PAD_LEFT);
            Cache::put($key, ['id' => $h], 60);
        }
        
        // Verificar que están en caché
        $this->assertTrue(Cache::has('quotes_all'));
        $this->assertTrue(Cache::has('quotes_id_1'));
        $this->assertTrue(Cache::has('quotes_id_keys'));
        $this->assertTrue(Cache::has('quotes_random_' . now()->format('Y-m-d-') . '00'));
        
        // Limpiar caché
        $this->service->clearCache();
        
        // Verificar que la caché principal se limpió
        $this->assertFalse(Cache::has('quotes_all'));
        
        // Verificar que las claves de ID se limpiaron
        $this->assertFalse(Cache::has('quotes_id_1'));
        $this->assertFalse(Cache::has('quotes_id_2'));
        $this->assertFalse(Cache::has('quotes_id_keys'));
        
        // Verificar que las claves de citas aleatorias se limpiaron
        for ($h = 0; $h < 24; $h++) {
            $key = 'quotes_random_' . now()->format('Y-m-d-') . str_pad($h, 2, '0', STR_PAD_LEFT);
            $this->assertFalse(Cache::has($key));
        }
    }
    
    /** @test */
    public function it_finds_quote_in_local_cache_by_id(): void
    {
        // Preparar datos
        $testQuotes = [
            ['id' => 1, 'quote' => 'Primera cita'],
            ['id' => 5, 'quote' => 'Quinta cita'],
            ['id' => 10, 'quote' => 'Décima cita']
        ];
        
        // Poner en caché de Laravel para que se cargue durante construcción
        Cache::put('quotes_cache', $testQuotes, 60);
        
        // Crear una nueva instancia para que cargue la caché
        $service = new QuotesApiService();
        
        // No debería llamar a la API porque está en caché local
        Http::fake([
            'https://dummyjson.com/quotes/*' => Http::response(['error' => 'No debería llegar aquí'], 500)
        ]);
        
        // Ejecutar método
        $quote = $service->getQuote(5);
        
        // Verificar que encontró en caché
        $this->assertIsArray($quote);
        $this->assertSame(5, $quote['id']);
        $this->assertSame('Quinta cita', $quote['quote']);
        
        // Verificar que NO se hizo ninguna petición HTTP
        Http::assertNothingSent();
    }
}
