<?php

return [
    'default' => env('MESSENGER_DRIVER', 'telegram'),

    'telegram' => [
        'token' => env('TELEGRAM_BOT_TOKEN', ''),
        'secret' => env('TELEGRAM_WEBHOOK_SECRET', null),
    ],
];
