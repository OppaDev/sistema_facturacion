<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración segura de CORS para la API REST del sistema de facturación.
    | Se restringen los orígenes, métodos y headers para mejorar la seguridad.
    |
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie'
    ],

    'allowed_methods' => [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS'
    ],

    // IMPORTANTE: En producción, especificar dominios exactos
    'allowed_origins' => [
        'http://localhost:3000',      // React/Next.js development
        'http://localhost:5173',      // Vite development  
        'http://localhost:8080',      // Vue CLI development
        'http://127.0.0.1:3000',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:8080',
        '*', // En desarrollo local
    ],

    'allowed_origins_patterns' => [
        // Patrones para subdominios en producción
        // '/^https:\/\/.*\.midominio\.com$/',
    ],

    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'X-CSRF-TOKEN',
        'X-Socket-ID',
    ],

    'exposed_headers' => [
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
        'X-RateLimit-Reset',
        'Retry-After'
    ],

    'max_age' => 86400, // 24 horas

    'supports_credentials' => true, // Necesario para Sanctum

];
