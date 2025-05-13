# QuotesPackage

A Laravel package that provides a Quotes API with a user interface developed in Vue.js.

## Overview

QuotesPackage allows you to get random quotes, search by ID, and list all available quotes, with an integrated user interface.

## Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js 16+ and npm
- Laravel 10.x

## Installation and Configuration

### 1. Clone the Repository

```bash
git clone [REPOSITORY_URL]
cd [PROJECT_NAME]
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Install Node.js Dependencies

```bash
npm install
```

### 5. Publish Package Assets

```bash
php artisan vendor:publish --tag=quotes-config
```

### 6. Compile Assets

```bash
cd packages/quotes-package
npm install
npm run build
cd ../..
```

### 7. Clear Cache

To ensure all changes are applied:
```bash
php artisan optimize:clear
php artisan view:clear
php artisan route:clear
php artisan cache:clear
```

## Project Structure

```
project/
├── app/
├── resources/
│   ├── js/
│   │   ├── quotes-app.js         # Entry point for Vue
│   │   ├── components/
│   │   │   └── QuotesApp.vue     # Main component (root)
├── packages/
│   └── quotes-package/           # Quotes package
│       ├── config/
│       │   └── quotes.php        # Package configuration
│       ├── resources/
│       │   ├── js/
│       │   │   ├── quotes-app.js # Package entry point
│       │   │   ├── components/
│       │   │   │   └── QuotesApp.vue # Package Vue component
│       │   ├── views/
│       │   │   └── quotes.blade.php  # Main view
│       ├── routes/
│       │   ├── api.php           # API routes
│       │   └── web.php           # Web routes
│       ├── src/
│       │   ├── Controllers/      # Controllers
│       │   ├── Middleware/       # Middleware
│       │   ├── Services/         # Services
│       │   └── QuotesServiceProvider.php # Service provider
│       └── package.json          # Package npm dependencies
```

## Usage

### Run Development Server

```bash
php artisan serve
```

### Access the Application

The user interface is available at:
```
http://localhost:8000/quotes-ui
```

### API Endpoints

- `GET /api/quotes` - Get all quotes
- `GET /api/quotes/random` - Get a random quote
- `GET /api/quotes/{id}` - Get a quote by ID
- `POST /api/quotes/clear-cache` - Clear server cache

## Main Features

- **Vue.js User Interface**: Modern interface for managing quotes.
- **Caching**: Caching system to improve performance.
- **Rate Limiting**: Rate limitation to protect the API.
- **Debug Panel**: Includes tools for debugging and testing.

## Package Development

If you want to modify the package directly:

```bash
cd packages/quotes-package
npm install
npm run build
```

After compiling the package assets:

```bash
cd ../..
php artisan vendor:publish --tag=quotes-assets
```

## Important Considerations

1. **Component Duplication**: There are currently two versions of the `QuotesApp.vue` component:
   - In `resources/js/vendor/quotes-package/components/QuotesApp.vue` (published)
   - In `packages/quotes-package/resources/js/components/QuotesApp.vue` (original)

   It's important to keep both versions synchronized or decide which one to use.

2. **Vite and Compilation**: The project uses Vite to compile assets. Make sure that:
   - The `vite.config.js` file includes the correct entries
   - The `@vite` directives in the views point to the correct files

3. **Configuration**: The package configuration can be customized by editing `config/quotes.php`.

4. **Development vs. Production**: For development, you can use uncompiled assets. For production, make sure to compile the assets.

5. **The main branch of the repository is the `main` branch.**
6. **To contribute to the project, create a branch from `main` and submit a Pull Request.**

## Troubleshooting

- **Vite Manifest Error**: If a "Vite manifest not found" error appears, make sure that:
  - You have run `npm run build`
  - You have published the assets with `php artisan vendor:publish --tag=quotes-assets`
  - The path in the `@vite` directive is correct

- **Blank Page**: Check for errors in the browser console (F12) and make sure that JavaScript files are loading correctly.

## License

MIT License

Copyright (c) 2023 [Your Name or Organization Name]

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

### Note on package structure

The package uses a specific Vite configuration:
- `public/`: Directory where compiled files are generated
- `static/`: Directory for static files that are copied directly without processing

This separation avoids Vite warnings about matching directories and ensures that compilation works correctly.

## Testing

The package includes unit and feature tests to verify its correct operation.

### Run all tests

```bash
cd packages/quotes-package
./vendor/bin/phpunit
```

### Run tests by category

```bash
# Unit tests
./vendor/bin/phpunit --testsuite Unit

# Feature tests
./vendor/bin/phpunit --testsuite Feature
```

### Run tests by file

```bash
# API service tests
./vendor/bin/phpunit tests/Unit/QuotesApiServiceTest.php

# Detailed API route tests
./vendor/bin/phpunit tests/Feature/QuotesApiRoutesTest.php

# Basic API route tests
./vendor/bin/phpunit tests/Feature/QuotesApiBasicRoutesTest.php
```

### Purpose of each test file

- **QuotesApiServiceTest.php**: Unit tests that verify the correct functioning of the service that communicates with the external API. Checks data retrieval, cache handling, and query optimization.

- **QuotesApiRoutesTest.php**: Detailed feature tests that verify the complete functioning of the API endpoints, including headers, JSON response content, cache management, and rate limits.

- **QuotesApiBasicRoutesTest.php**: Basic tests that verify the fundamental response structure of the API endpoints. These are simpler tests focused on ensuring that the response structure is correct.

# 🚀 TESTING GUIDE IN A NEW PROJECT 🚀

If you want to test this package in a clean Laravel project, follow these steps:

### 1. Create a new Laravel project

```bash
composer create-project laravel/laravel test-quotes
cd test-quotes
```

### 2. Configure the local package repository

Create the directory structure for the package:
```bash
mkdir -p packages/quotes-package
```

Clone or copy this repository into that folder:
```bash
# Option 1: Clone (recommended)
git clone https://github.com/your-username/quotes-package.git packages/quotes-package

# Option 2: Copy from an existing location
# xcopy /source/path/quotes-package packages/quotes-package /E /H /C /I
```

### 3. Configure composer.json to use the local repository

Edit your project's `composer.json` file and add:
```json
"repositories": [
    {
        "type": "path",
        "url": "./packages/quotes-package"
    }
]
```

### 4. Install the package

```bash
composer require quotes-package/quotes
```

### 5. Publish configuration and assets

```bash
php artisan vendor:publish --tag=quotes-config
php artisan vendor:publish --tag=quotes-assets
```

### 6. Start the server and test

```bash
php artisan serve
```

### 7. Access the different functionalities

- **API Endpoints**: 
  - http://localhost:8000/api/quotes
  - http://localhost:8000/api/quotes/random
  - http://localhost:8000/api/quotes/1

- **User interface**:
  - http://localhost:8000/quotes-ui

This should display both the functional API and the fully operational Vue.js user interface. 