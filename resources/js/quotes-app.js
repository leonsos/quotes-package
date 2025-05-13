import { createApp } from 'vue';
import QuotesApp from './components/QuotesApp.vue';

// Obtener configuración desde el atributo data del elemento
const appElement = document.getElementById('quotes-app');
const config = appElement ? JSON.parse(appElement.getAttribute('data-config') || '{}') : {};

const app = createApp(QuotesApp, { config });
app.mount('#quotes-app');
