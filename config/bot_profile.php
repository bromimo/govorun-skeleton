<?php

/*
 * Конфиг Telegram-профиля бота для CLI:
 *
 *     php govorun bot:profile-sync
 *
 * По умолчанию файл возвращает null — команда сообщит «Профиль не найден» и ничего
 * не запушит. Чтобы включить — закомментируйте `return null;` и раскомментируйте
 * массив ниже, заполните нужные поля.
 *
 * Фото профиля кладите в storage/app/bot-profile.jpg
 * (или .mp4 для animated profile photo) — команда подхватит сама.
 */

return null;

/*
return [
    'name' => 'Имя бота (≤ 64 символа)',
    'short_description' => 'Короткое описание (≤ 120 символов)',
    'description' => 'Длинное описание для пустого чата (≤ 512 символов)',
    'commands' => [
        ['command' => 'start', 'description' => 'Начать'],
        // ['command' => 'help',  'description' => 'Справка'],
    ],
];
*/