<?php

namespace QuotesPackage\Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use QuotesPackage\Tests\TestCase;

class QuotesApiRoutesTest extends TestCase
{
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
        
        // Llamar a la API
        $response = $this->getJson('api/quotes');
        
        // Verificar respuesta
        $response->assertStatus(200)
                 ->assertJson([
                     'total' => 2,
                     'quotes' => [
                         ['id' => 1, 'quote' => 'Cita de prueba 1'],
                         ['id' => 2, 'quote' => 'Cita de prueba 2']
                     ]
                 ]);
                 
        // Verificar header de caché
        $this->assertSame('MISS', $response->headers->get('X-Cache'));
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
        
        // Llamar a la API
        $response = $this->getJson('api/quotes/5');
        
        // Verificar respuesta
        $response->assertStatus(200)
                 ->assertJson([
                     'id' => 5,
                     'quote' => 'Cita específica'
                 ]);
                 
        // Verificar header de caché
        $this->assertSame('MISS', $response->headers->get('X-Cache'));
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
        
        // Llamar a la API
        $response = $this->getJson('api/quotes/random');
        
        // Verificar respuesta
        $response->assertStatus(200)
                 ->assertJson([
                     'id' => 10,
                     'quote' => 'Cita aleatoria'
                 ]);
    }
    
    /** @test */
    public function it_can_clear_cache(): void
    {
        // Poner algo en caché
        Cache::put('quotes_all', [['id' => 1]], 60);
        Cache::put('quotes_id_1', ['id' => 1], 60);
        Cache::put('quotes_id_keys', ['quotes_id_1'], 60);
        
        // Verificar que está en caché
        $this->assertTrue(Cache::has('quotes_all'));
        
        // Limpiar caché mediante API
        $response = $this->postJson('api/quotes/clear-cache');
        
        // Verificar respuesta y que el caché se limpió
        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('message', 'Caché limpiado correctamente');
                 
        $this->assertFalse(Cache::has('quotes_all'));
        $this->assertFalse(Cache::has('quotes_id_1'));
    }
    
    /** @test */
    public function it_respects_rate_limiting(): void
    {
        // Configurar límite de tasa bajo para pruebas
        $this->app['config']->set('quotes.rate_limit_max_requests', 2);
        $this->app['config']->set('quotes.rate_limit_window', 60);
        
        // Simular respuesta HTTP
        Http::fake([
            'https://dummyjson.com/quotes/random' => Http::response([
                'id' => 5, 
                'quote' => 'Cita aleatoria', 
                'author' => 'Autor aleatorio'
            ], 200)
        ]);
        
        // Primeras dos peticiones deben ser exitosas
        $this->getJson('api/quotes/random')->assertStatus(200);
        $this->getJson('api/quotes/random')->assertStatus(200);
        
        // La tercera debe ser limitada
        $response = $this->getJson('api/quotes/random');
        $response->assertStatus(429); // Too Many Requests
        $response->assertJsonPath('error', 'Demasiadas solicitudes. Por favor, inténtalo de nuevo más tarde.');
    }
}
