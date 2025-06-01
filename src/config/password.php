<?php
return [
    // Здесь задаём ID и Secret вашего Password Grant клиента
    // Возьмите их из вывода `php artisan passport:install`
    'password_client_id'     => env('PASSWORD_CLIENT_ID', 2),
    'password_client_secret' => env('PASSWORD_CLIENT_SECRET', 'ваш_секрет_из_passport:install'),
];
