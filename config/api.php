<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the configuration for the API. You can add any
    | configuration options that you need for your API here.
    |
    */
    'app_url' => env('APP_URL', 'http://localhost:8000'),
    'db_connection' => env('DB_CONNECTION', 'psql'),
    'db_host' => env('DB_HOST', 'localhost'),
    'db_port' => env('DB_PORT', '5432'),
    'db_database' => env('DB_DATABASE', 'postgres'),
    'db_username' => env('DB_USERNAME', 'postgres'),
    'db_password' => env('DB_PASSWORD', 'postgres'),
];
