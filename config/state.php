<?php

return [
    /*
     * Драйвер хранилища состояний Flow-диалогов.
     * Доступные значения: file (по умолчанию), database, cache.
     */
    'driver' => env('STATE_DRIVER', 'file'),

    /*
     * TTL для cache-драйвера, в секундах. Игнорируется для file и database.
     */
    'ttl' => (int) env('STATE_TTL', 3600),
];
