<?php

namespace QuotesPackage\Tests\Feature;

use Illuminate\Support\Facades\Http;
use QuotesPackage\Tests\TestCase;

class QuotesApiBasicRoutesTest extends TestCase
{
    /** @test */
    public function api_quotes_endpoint_returns_json_response(): void
    {
        // Simular respuesta HTTP básica
        Http::fake([
            'https://dummyjson.com/quotes' => Http::response([
                'quotes' => [['id' => 1], ['id' => 2]],
                'total' => 2
            ], 200)
        ]);
        
        // Probar endpoint básico
        $response = $this->get('api/quotes');
        
        // Verificar que devuelve respuesta correcta
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['quotes', 'total']);
    }

    /** @test */
    public function api_quote_by_id_endpoint_returns_correct_structure(): void
    {
        // Simular respuesta HTTP básica
        Http::fake([
            'https://dummyjson.com/quotes/1' => Http::response([
                'id' => 1,
                'quote' => 'Test quote',
                'author' => 'Test author'
            ], 200)
        ]);
        
        // Probar endpoint de cita por ID
        $response = $this->get('api/quotes/1');
        
        // Verificar estructura básica de respuesta
        $response->assertStatus(200);
        $response->assertJsonStructure(['id', 'quote']);
    }

    /** @test */
    public function api_random_quote_endpoint_returns_quote(): void
    {
        // Simular respuesta HTTP básica
        Http::fake([
            'https://dummyjson.com/quotes/random' => Http::response([
                'id' => 5,
                'quote' => 'Random quote'
            ], 200)
        ]);
        
        // Probar endpoint de cita aleatoria
        $response = $this->get('api/quotes/random');
        
        // Verificar estructura de la respuesta
        $response->assertStatus(200);
        $response->assertJsonStructure(['id', 'quote']);
    }

    /** @test */
    public function clear_cache_endpoint_returns_success_response(): void
    {
        // Probar endpoint para limpiar caché
        $response = $this->post('api/quotes/clear-cache');
        
        // Verificar respuesta básica
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function nonexistent_quote_returns_404(): void
    {
        // Simular respuesta HTTP para un ID que no existe
        Http::fake([
            'https://dummyjson.com/quotes/999' => Http::response([
                'message' => 'Quote not found'
            ], 404)
        ]);
        
        // Probar acceso a un ID inexistente
        $response = $this->get('api/quotes/999');
        
        // Verificar que devuelve error 404
        $response->assertStatus(404);
        $response->assertJsonStructure(['error']);
    }
} 