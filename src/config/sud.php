<?php

return [
    // Базовый URL для всех запросов к my.sud.uz
    'base_url' => env('SUD_BASE_URL', 'https://jadvalapi.sud.uz/online-monitoring/ECONOMIC'),

    // Таймаут (в секундах) для Guzzle-запросов
    'timeout'  => env('SUD_TIMEOUT', 5),

    // Количество повторов при неудачном запросе
    'retries'  => env('SUD_RETRIES', 1),
];
