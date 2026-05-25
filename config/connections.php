<?php

/*
 * HTTP-подключения к внешним API.
 *
 * Используются через трейт Govorun\Http\MakesHttpCalls — он подмешан в
 * Govorun\Routing\Controller и Govorun\State\Flow:
 *
 *     $response = $this->http()->connection('payment')->post('/charge', [
 *         'json' => ['amount' => 100],
 *     ]);
 *
 *     if ($response->successful()) {
 *         $data = $response->json();
 *     }
 *
 * Поля подключения:
 *   - base_url        — базовый URL (обязательно)
 *   - default_headers — массив дефолтных заголовков
 *   - auth.type       — none | bearer | api_key | basic
 *   - auth.token      — для bearer
 *   - auth.key + auth.value + auth.in (header|query) — для api_key
 *   - auth.login + auth.password — для basic
 */

return [

    // 'payment' => [
    //     'base_url' => 'https://api.payment.com/v1',
    //     'default_headers' => ['Accept' => 'application/json'],
    //     'auth' => [
    //         'type' => 'bearer',
    //         'token' => env('PAYMENT_TOKEN', ''),
    //     ],
    // ],

];