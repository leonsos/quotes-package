# QuotesPackage

Un paquete Laravel que proporciona una API de cotizaciones con una interfaz de usuario desarrollada en Vue.js.

## Descripción General

QuotesPackage permite obtener citas aleatorias, buscar por ID y listar todas las citas disponibles, con interfaz de usuario integrada.

## Requisitos Previos

- PHP 8.1 o superior
- Composer
- Node.js 16+ y npm
- Laravel 10.x

## Instalación y Configuración

### 1. Clonar el Repositorio

```bash
git clone [URL_DEL_REPOSITORIO]
cd [NOMBRE_DEL_PROYECTO]
```

### 2. Instalar Dependencias de PHP

```bash
composer install
```

### 3. Configuración del Entorno

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Instalar Dependencias de Node.js

```bash
npm install
```

### 5. Publicar Assets del Paquete

```bash
php artisan vendor:publish --tag=quotes-config
```

### 6. Compilar Assets

```bash
cd packages/quotes-package
npm install
npm run build
cd ../..
```

### 7. Limpiar Caché

Para asegurarte de que todos los cambios son aplicados:
```bash
php artisan optimize:clear
php artisan view:clear
php artisan route:clear
php artisan cache:clear
```

## Estructura del Proyecto

```
project/
├── app/
├── resources/
│   ├── js/
│   │   ├── quotes-app.js         # Punto de entrada para Vue
│   │   ├── components/
│   │   │   └── QuotesApp.vue     # Componente principal (raíz)
├── packages/
│   └── quotes-package/           # Paquete de cotizaciones
│       ├── config/
│       │   └── quotes.php        # Configuración del paquete
│       ├── resources/
│       │   ├── js/
│       │   │   ├── quotes-app.js # Punto de entrada del paquete
│       │   │   ├── components/
│       │   │   │   └── QuotesApp.vue # Componente Vue del paquete
│       │   ├── views/
│       │   │   └── quotes.blade.php  # Vista principal
│       ├── routes/
│       │   ├── api.php           # Rutas de API
│       │   └── web.php           # Rutas web
│       ├── src/
│       │   ├── Controllers/      # Controladores
│       │   ├── Middleware/       # Middleware
│       │   ├── Services/         # Servicios
│       │   └── QuotesServiceProvider.php # Proveedor de servicios
│       └── package.json          # Dependencias npm del paquete
```

## Uso

### Ejecutar Servidor de Desarrollo

```bash
php artisan serve
```

### Acceder a la Aplicación

La interfaz de usuario está disponible en:
```
http://localhost:8000/quotes-ui
```

### API Endpoints

- `GET /api/quotes` - Obtener todas las citas
- `GET /api/quotes/random` - Obtener una cita aleatoria
- `GET /api/quotes/{id}` - Obtener una cita por ID
- `POST /api/quotes/clear-cache` - Limpiar caché del servidor

## Características Principales

- **Interfaz de Usuario Vue.js**: Interfaz moderna para gestionar citas.
- **Caché**: Sistema de caché para mejorar rendimiento.
- **Rate Limiting**: Limitación de tasas para proteger la API.
- **Panel de Depuración**: Incluye herramientas para depuración y pruebas.

## Desarrollo del Paquete

Si deseas modificar el paquete directamente:

```bash
cd packages/quotes-package
npm install
npm run build
```

Después de compilar los assets del paquete:

```bash
cd ../..
php artisan vendor:publish --tag=quotes-assets
```

## Consideraciones Importantes

1. **Duplicación de Componentes**: Actualmente hay dos versiones del componente `QuotesApp.vue`:
   - En `resources/js/vendor/quotes-package/components/QuotesApp.vue` (publicado)
   - En `packages/quotes-package/resources/js/components/QuotesApp.vue` (original)

   Es importante mantener sincronizadas ambas versiones o decidir cuál usar.

2. **Vite y Compilación**: El proyecto usa Vite para compilar assets. Asegúrate de que:
   - El archivo `vite.config.js` incluya las entradas correctas
   - Las directivas `@vite` en las vistas apunten a los archivos correctos

3. **Configuración**: La configuración del paquete se puede personalizar editando `config/quotes.php`.

4. **Desarrollo vs. Producción**: Para desarrollo, puedes usar assets no compilados. Para producción, asegúrate de compilar los assets.

5. **La rama principal del repositorio es la rama `main`.**
6. **Para contribuir al proyecto, crea una rama a partir de `main` y envía un Pull Request.**

## Solución de Problemas

- **Error de Vite Manifest**: Si aparece un error de "Vite manifest not found", asegúrate de que:
  - Has ejecutado `npm run build`
  - Has publicado los assets con `php artisan vendor:publish --tag=quotes-assets`
  - La ruta en la directiva `@vite` es correcta

- **Página en Blanco**: Verifica errores en la consola del navegador (F12) y asegúrate de que los archivos JavaScript se estén cargando correctamente.

## Licencia

MIT License

Copyright (c) 2023 [Tu Nombre o Nombre de la Organización]

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

### Nota sobre la estructura del paquete

El paquete utiliza una configuración específica de Vite:
- `public/`: Directorio donde se generan los archivos compilados
- `static/`: Directorio para archivos estáticos que se copian directamente sin procesamiento

Esta separación evita advertencias de Vite sobre directorios coincidentes y asegura que la compilación funcione correctamente.

## Pruebas

El paquete incluye pruebas unitarias y de característica para verificar su funcionamiento correcto.

### Ejecutar todas las pruebas

```bash
cd packages/quotes-package
./vendor/bin/phpunit
```

### Ejecutar pruebas por categoría

```bash
# Pruebas unitarias
./vendor/bin/phpunit --testsuite Unit

# Pruebas de característica
./vendor/bin/phpunit --testsuite Feature
```

### Ejecutar pruebas por archivo

```bash
# Pruebas de servicio de API
./vendor/bin/phpunit tests/Unit/QuotesApiServiceTest.php

# Pruebas detalladas de rutas de API
./vendor/bin/phpunit tests/Feature/QuotesApiRoutesTest.php

# Pruebas básicas de rutas de API
./vendor/bin/phpunit tests/Feature/QuotesApiBasicRoutesTest.php
```

### Propósito de cada archivo de prueba

- **QuotesApiServiceTest.php**: Pruebas unitarias que verifican el correcto funcionamiento del servicio que se comunica con la API externa. Comprueba la obtención de datos, manejo de caché y optimización de consultas.

- **QuotesApiRoutesTest.php**: Pruebas detalladas de característica que verifican el funcionamiento completo de los endpoints de la API, incluyendo headers, contenido de respuestas JSON, gestión de caché y límites de tasa.

- **QuotesApiBasicRoutesTest.php**: Pruebas básicas que verifican la estructura fundamental de respuesta de los endpoints de la API. Son pruebas más simples enfocadas en garantizar que la estructura de la respuesta sea correcta.

# 🚀 GUÍA DE PRUEBA EN UN PROYECTO NUEVO 🚀

Si desea probar este paquete en un proyecto Laravel limpio, siga estos pasos:

### 1. Crear un nuevo proyecto Laravel

```bash
composer create-project laravel/laravel test-quotes
cd test-quotes
```

### 2. Configurar el repositorio local del paquete

Cree la estructura de directorios para el paquete:
```bash
mkdir -p packages/quotes-package
```

Clone o copie este repositorio en esa carpeta:
```bash
# Opción 1: Clonar (recomendado)
git clone https://github.com/su-usuario/quotes-package.git packages/quotes-package

# Opción 2: Copiar desde una ubicación existente
# xcopy /ruta/origen/quotes-package packages/quotes-package /E /H /C /I
```

### 3. Configurar composer.json para usar el repositorio local

Edite el archivo `composer.json` de su proyecto y agregue:
```json
"repositories": [
    {
        "type": "path",
        "url": "./packages/quotes-package"
    }
]
```

### 4. Instalar el paquete

```bash
composer require quotes-package/quotes
```

### 5. Publicar configuración y assets

```bash
php artisan vendor:publish --tag=quotes-config
php artisan vendor:publish --tag=quotes-assets
```

### 6. Iniciar el servidor y probar

```bash
php artisan serve
```

### 7. Acceder a las diferentes funcionalidades

- **API Endpoints**: 
  - http://localhost:8000/api/quotes
  - http://localhost:8000/api/quotes/random
  - http://localhost:8000/api/quotes/1

- **Interfaz de usuario**:
  - http://localhost:8000/quotes-ui

Esto debe mostrar tanto la API funcional como la interfaz de usuario en Vue.js completamente operativa.