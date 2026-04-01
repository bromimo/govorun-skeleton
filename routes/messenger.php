<?php

use App\Controllers\StartController;
use Govorun\Routing\Route;

/**
    |--------------------------------------------------------------------------
    | Messenger Routes
    |--------------------------------------------------------------------------
    |
    | Доступные методы маршрутизации:
    |
    | Route::command('name', Action)      — команда /name
    | Route::phrase('text', Action)       — текстовая фраза (поиск вхождения)
    | Route::pattern('/regex/', Action)   — регулярное выражение
    | Route::action('name', Action)       — callback-действие (inline-кнопки)
    | Route::event('name', Action)        — событие (member_joined и т.д.)
    | Route::media('type', Action)        — медиафайл (photo, video, document)
    | Route::location(Action)             — геолокация
    | Route::contact(Action)              — контакт
    | Route::referral('code', Action)     — реферальный код
    | Route::fallback(Action)             — всё, что не совпало с другими
    |
    | Route::middleware(Class, fn)        — обернуть группу маршрутов в middleware
    | Route::phrase('text', fn)           — группа вложенных маршрутов
    |
    | Action — класс контроллера (метод handle() или __invoke())
    | RouteEntry->alias(['синоним', ...]) — алиасы для phrase-маршрутов
    |
    */

Route::command('start', StartController::class);
