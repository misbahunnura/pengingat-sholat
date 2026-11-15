<?php

return [
    'name' => env('APP_NAME', 'Pengingat Salat'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'Asia/Jakarta',
    'locale' => env('APP_LOCALE', 'id'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale' => 'id_ID',
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
];
