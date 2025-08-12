<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Esta configuración define varios aspectos de seguridad para la aplicación.
    |
    */

    'allowed_hosts' => explode(',', env('ALLOWED_HOSTS', 'localhost,127.0.0.1')),

    'api_max_request_size' => (int) env('API_MAX_REQUEST_SIZE', 1048576), // 1MB por defecto

    'rate_limit' => (int) env('SECURITY_RATE_LIMIT', 300), // 300 requests por minuto

    'rate_limit_decay' => 60, // segundos

];
