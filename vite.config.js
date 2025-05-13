import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
  plugins: [vue()],
  build: {
    // Directorio de salida para los archivos compilados
    outDir: 'public',
    assetsDir: '',
    // Asegurarse de limpiar el directorio antes de construir
    emptyOutDir: true,
    // Opciones de Rollup
    rollupOptions: {
      input: {
        'quotes-app': resolve(__dirname, 'resources/js/quotes-app.js')
      },
      output: {
        // Estructura de directorios para los archivos generados
        entryFileNames: 'js/[name].js',
        chunkFileNames: 'js/chunks/[name].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name.endsWith('.css')) {
            return 'css/[name][extname]';
          }
          return 'assets/[name][extname]';
        }
      }
    }
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources/js')
    }
  },
  publicDir: 'static',
});
