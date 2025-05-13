<template>
  <div class="quotes-container">
    <div class="quotes-header">
      <h1>API de Cotizaciones</h1>
      <p>Explora cotizaciones inspiradoras</p>
    </div>
    
    <div class="actions-container">
      <button @click="fetchRandomQuote" class="action-button primary">
        <span v-if="loading">Cargando...</span>
        <span v-else>Cita Aleatoria</span>
      </button>
      
      <button @click="fetchAllQuotes" class="action-button secondary">
        Ver Todas
      </button>
      
      <div class="id-search">
        <input 
          type="number" 
          v-model="quoteId" 
          placeholder="ID de cita" 
          class="id-input"
          min="1"
        />
        <button @click="fetchQuoteById" class="action-button tertiary">
          Buscar
        </button>
      </div>
      
      <button @click="toggleDebugPanel" class="action-button debug">
        {{ showDebugPanel ? '🔽 Ocultar Debug' : '🔼 Mostrar Debug' }}
      </button>
    </div>
    
    <div v-if="error" class="error-message">
      {{ error }}
    </div>
    
    <div v-if="currentQuote" class="quote-single">
      <div class="quote-content">
        <p class="quote-text">"{{ currentQuote.quote }}"</p>
        <p class="quote-author">— {{ currentQuote.author }}</p>
      </div>
      <div class="quote-meta">
        <span class="quote-id">ID: {{ currentQuote.id }}</span>
      </div>
    </div>
    
    <div v-if="quotes.length" class="quotes-list">
      <h2>Todas las Citas</h2>
      <div v-for="quote in quotes" :key="quote.id" class="quote-item">
        <p class="quote-text">"{{ quote.quote }}"</p>
        <p class="quote-author">— {{ quote.author }}</p>
        <span class="quote-id">ID: {{ quote.id }}</span>
      </div>
      
      <div v-if="pagination.hasMore" class="load-more">
        <button @click="loadMore" class="action-button secondary">
          Cargar Más
        </button>
      </div>
    </div>
    
    <!-- Panel de Depuración -->
    <div v-if="showDebugPanel" class="debug-panel">
      <h3 class="text-sm font-bold mb-2">Panel de Depuración</h3>
      <div class="text-xs space-y-1">
        <p>Tiempo de respuesta: {{ responseTime }}ms</p>
        <p>Estado caché: {{ cacheHit ? '✅ HIT' : '❌ MISS' }}</p>
        <p>Rate limit: {{ rateLimitRemaining }}/{{ rateLimitLimit }}</p>
      </div>
      <div class="mt-2">
        <button @click="clearCache" class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded">
          Limpiar caché
        </button>
        <button @click="testRateLimit" class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded ml-2">
          Test Rate Limit
        </button>
      </div>
      <div v-if="testResults.length" class="mt-2 text-xs">
        <p>Resultados:</p>
        <div class="max-h-24 overflow-y-auto">
          <div v-for="(result, index) in testResults" :key="index" class="p-1">
            {{ result }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    config: {
      type: Object,
      default: () => ({
        apiBaseUrl: '/api/quotes'
      })
    }
  },
  
  data() {
    return {
      quotes: [],
      currentQuote: null,
      quoteId: null,
      loading: false,
      error: null,
      pagination: {
        page: 1,
        limit: 10,
        hasMore: false
      },
      // Variables para depuración
      showDebugPanel: false,
      responseTime: 0,
      cacheHit: false,
      rateLimitLimit: 0,
      rateLimitRemaining: 0,
      testResults: []
    };
  },
  
  mounted() {
    this.fetchRandomQuote();
  },
  
  methods: {
    /**
     * Obtiene todas las citas
     */
    async fetchAllQuotes() {
      this.loading = true;
      this.currentQuote = null;
      this.error = null;
      const startTime = performance.now();
      
      try {
        const response = await fetch(`${this.config.apiBaseUrl || '/api/quotes'}`);
        this.responseTime = Math.round(performance.now() - startTime);
        this.cacheHit = response.headers.get('X-Cache') === 'HIT';
        this.rateLimitLimit = response.headers.get('X-RateLimit-Limit') || 0;
        this.rateLimitRemaining = response.headers.get('X-RateLimit-Remaining') || 0;
        
        const data = await response.json();
        
        if (response.ok) {
          this.quotes = data.quotes || [];
          this.pagination.hasMore = this.quotes.length < data.total;
        } else {
          this.error = data.error || 'Error al cargar las citas';
        }
      } catch (err) {
        this.error = 'Error de red al cargar las citas';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },
    
    /**
     * Obtiene una cita aleatoria
     */
    async fetchRandomQuote() {
      this.loading = true;
      this.quotes = [];
      this.error = null;
      const startTime = performance.now();
      
      try {
        const response = await fetch(`${this.config.apiBaseUrl || '/api/quotes'}/random`);
        this.responseTime = Math.round(performance.now() - startTime);
        this.cacheHit = response.headers.get('X-Cache') === 'HIT';
        this.rateLimitLimit = response.headers.get('X-RateLimit-Limit') || 0;
        this.rateLimitRemaining = response.headers.get('X-RateLimit-Remaining') || 0;
        
        const data = await response.json();
        
        if (response.ok) {
          this.currentQuote = data;
        } else {
          this.error = data.error || 'Error al cargar cita aleatoria';
        }
      } catch (err) {
        this.error = 'Error de red al cargar cita aleatoria';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },
    
    /**
     * Obtiene una cita por ID
     */
    async fetchQuoteById() {
      if (!this.quoteId) {
        this.error = 'Por favor, ingrese un ID válido';
        return;
      }
      
      this.loading = true;
      this.quotes = [];
      this.error = null;
      const startTime = performance.now();
      
      try {
        const response = await fetch(`${this.config.apiBaseUrl || '/api/quotes'}/${this.quoteId}`);
        this.responseTime = Math.round(performance.now() - startTime);
        this.cacheHit = response.headers.get('X-Cache') === 'HIT';
        this.rateLimitLimit = response.headers.get('X-RateLimit-Limit') || 0;
        this.rateLimitRemaining = response.headers.get('X-RateLimit-Remaining') || 0;
        
        const data = await response.json();
        
        if (response.ok) {
          this.currentQuote = data;
        } else {
          this.error = data.error || `No se encontró la cita con ID ${this.quoteId}`;
        }
      } catch (err) {
        this.error = 'Error de red al cargar la cita';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },
    
    /**
     * Carga más citas en la paginación
     */
    loadMore() {
      this.pagination.page++;
      // Implementación para cargar más citas
    },
    
    /**
     * Limpia la caché del servidor
     */
    async clearCache() {
      try {
        await fetch(`${this.config.apiBaseUrl || '/api/quotes'}/clear-cache`, { method: 'POST' });
        alert('Caché limpiado correctamente');
      } catch (err) {
        console.error('Error al limpiar caché', err);
        alert('Error al limpiar caché');
      }
    },
    
    /**
     * Prueba el límite de tasa de solicitudes (rate limit)
     */
    async testRateLimit() {
      this.testResults = [];
      const maxTests = 40; // Intentar exceder el límite
      
      for (let i = 0; i < maxTests; i++) {
        try {
          const start = performance.now();
          const response = await fetch(`${this.config.apiBaseUrl || '/api/quotes'}/random`);
          const time = Math.round(performance.now() - start);
          
          if (response.status === 429) {
            this.testResults.push(`Petición ${i+1}: ⛔ Rate limit excedido`);
            break;
          } else {
            const remaining = response.headers.get('X-RateLimit-Remaining');
            this.testResults.push(
              `Petición ${i+1}: ✅ ${time}ms (Restantes: ${remaining})`
            );
          }
        } catch (err) {
          this.testResults.push(`Petición ${i+1}: ❌ Error: ${err.message}`);
          break;
        }
        
        // Pequeña pausa para no bloquear la UI
        await new Promise(r => setTimeout(r, 100));
      }
    },
    
    /**
     * Alterna la visibilidad del panel de depuración
     */
    toggleDebugPanel() {
      this.showDebugPanel = !this.showDebugPanel;
    }
  }
};
</script>

<style scoped>
.quotes-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 30px 20px;
  background-color: #f8f5ff;
  min-height: 100vh;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}

.quotes-header {
  text-align: center;
  margin-bottom: 30px;
}

.quotes-header h1 {
  color: #9d8ac7;
  font-size: 2.2rem;
  margin-bottom: 8px;
  font-weight: 600;
}

.quotes-header p {
  color: #afa3c8;
  font-size: 1.1rem;
}

.actions-container {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 20px;
  justify-content: center;
}

.action-button {
  padding: 10px 16px;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  border: none;
  font-size: 0.95rem;
}

.action-button.primary {
  background-color: #9d8ac7;
  color: white;
}

.action-button.primary:hover {
  background-color: #8a76b6;
}

.action-button.secondary {
  background-color: #e0d9f6;
  color: #6b5c9f;
}

.action-button.secondary:hover {
  background-color: #d3caf0;
}

.action-button.tertiary {
  background-color: #f8f5ff;
  color: #9d8ac7;
  border: 1px solid #d3caf0;
}

.action-button.tertiary:hover {
  background-color: #f0ebfc;
}

.action-button.debug {
  background-color: #f0ebfc;
  color: #6b5c9f;
  font-size: 0.8rem;
  padding: 6px 12px;
}

.id-search {
  display: flex;
  gap: 5px;
}

.id-input {
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #d3caf0;
  width: 100px;
  font-size: 0.95rem;
}

.error-message {
  padding: 10px 15px;
  background-color: #ffeded;
  border-left: 4px solid #e77373;
  color: #c53030;
  margin-bottom: 20px;
  border-radius: 4px;
}

.quote-single {
  padding: 30px;
  background-color: white;
  box-shadow: 0 4px 6px rgba(157, 138, 199, 0.1);
  border-radius: 10px;
  margin-bottom: 30px;
}

.quote-content {
  margin-bottom: 15px;
}

.quote-text {
  font-size: 1.4rem;
  color: #4a4a4a;
  line-height: 1.4;
  margin-bottom: 15px;
  font-style: italic;
}

.quote-author {
  font-size: 1.1rem;
  color: #9d8ac7;
  text-align: right;
  font-weight: 500;
}

.quote-meta {
  display: flex;
  justify-content: flex-end;
}

.quote-id {
  color: #c0b5db;
  font-size: 0.9rem;
}

.quotes-list {
  margin-top: 30px;
}

.quotes-list h2 {
  font-size: 1.5rem;
  color: #6b5c9f;
  margin-bottom: 20px;
  font-weight: 600;
}

.quote-item {
  padding: 20px;
  background-color: white;
  box-shadow: 0 2px 4px rgba(157, 138, 199, 0.1);
  border-radius: 8px;
  margin-bottom: 15px;
}

.quote-item .quote-text {
  font-size: 1.1rem;
  margin-bottom: 10px;
}

.quote-item .quote-author {
  font-size: 0.95rem;
}

.load-more {
  text-align: center;
  margin-top: 20px;
}

.debug-panel {
  margin-top: 30px;
  padding: 15px;
  background-color: #f0f9ff;
  border: 1px solid #cce3f6;
  border-radius: 6px;
  font-size: 0.85rem;
}

.debug-panel h3 {
  font-size: 0.95rem;
  color: #4a6e8c;
  margin-bottom: 10px;
  font-weight: 600;
}

.debug-panel p {
  margin-bottom: 5px;
  color: #4a6e8c;
}

.text-xs {
  font-size: 0.75rem;
}

.text-sm {
  font-size: 0.875rem;
}

.font-bold {
  font-weight: 700;
}

.mb-2 {
  margin-bottom: 0.5rem;
}

.mt-2 {
  margin-top: 0.5rem;
}

.space-y-1 > * + * {
  margin-top: 0.25rem;
}

.px-2 {
  padding-left: 0.5rem;
  padding-right: 0.5rem;
}

.py-1 {
  padding-top: 0.25rem;
  padding-bottom: 0.25rem;
}

.ml-2 {
  margin-left: 0.5rem;
}

.bg-red-100 {
  background-color: #fee2e2;
}

.text-red-700 {
  color: #b91c1c;
}

.bg-blue-100 {
  background-color: #dbeafe;
}

.text-blue-700 {
  color: #1d4ed8;
}

.rounded {
  border-radius: 0.25rem;
}

.max-h-24 {
  max-height: 6rem;
}

.overflow-y-auto {
  overflow-y: auto;
}

.p-1 {
  padding: 0.25rem;
}
</style>
