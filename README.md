# Govorun Skeleton

Шаблон проекта для [Govorun Framework](https://github.com/bromimo/govorun-framework) — мульти-мессенджер бот-фреймворк на PHP 8.3+.

## Установка

```bash
composer create-project govorun/skeleton my-bot
cd my-bot
```

## Настройка

Откройте `.env` и укажите токен бота:

```env
TELEGRAM_BOT_TOKEN=your-bot-token-here
```

## Запуск

### Webhook

Установите вебхук:

```bash
php govorun webhook:install
```

Или направьте веб-сервер на директорию `public/`.

URL вебхука: `https://example.com/webhook/telegram` — последний сегмент определяет драйвер.

### Структура проекта

В свежем скелетоне присутствует только `app/Controllers/` (+ `app/Support/` с composer-хуком). Остальные папки (`app/Flows/`, `app/Services/`, `app/Middleware/`, `app/Providers/`) создавайте по мере необходимости — CLI-генераторы (`make:flow`, `make:api-client`, …) положат файлы в соответствующие места автоматически.

```
app/Controllers/        — контроллеры бота (есть из коробки)
app/Flows/              — Flow-диалоги (создаётся через make:flow)
app/Services/           — API-клиенты (создаётся через make:api-client)
app/Middleware/         — middleware (создаётся вручную)
app/Providers/          — сервис-провайдеры (создаётся вручную)
config/                 — app, messenger, database, cache, logging
routes/messenger.php    — маршруты команд
bootstrap/app.php       — точка создания Application
public/index.php        — точка входа для вебхука
govorun                 — CLI-утилита (php govorun ...)
storage/logs/           — логи приложения
storage/state/          — состояния Flow-диалогов
storage/app/            — пользовательские файлы (включая bot-profile.{jpg,mp4})
```

## Маршрутизация

Маршруты описываются в `routes/messenger.php`:

```php
use App\Controllers\StartController;
use Govorun\Routing\Route;

Route::command('start', StartController::class);
```

### Доступные методы

| Метод | Описание | Пример |
|-------|----------|--------|
| `Route::command($name, $action)` | Команда `/name` | `Route::command('start', StartController::class)` |
| `Route::phrase($text, $action)` | Текстовая фраза | `Route::phrase('привет', HelloController::class)` |
| `Route::pattern($regex, $action)` | Регулярное выражение | `Route::pattern('/^\\d+$/', NumberController::class)` |
| `Route::action($name, $action)` | Callback-действие (inline-кнопки) | `Route::action('confirm', ConfirmController::class)` |
| `Route::event($name, $action)` | Событие мессенджера | `Route::event('member_joined', WelcomeController::class)` |
| `Route::media($type, $action)` | Медиафайл | `Route::media('photo', PhotoController::class)` |
| `Route::location($action)` | Геолокация | `Route::location(LocationController::class)` |
| `Route::contact($action)` | Контакт | `Route::contact(ContactController::class)` |
| `Route::referral($code, $action)` | Реферальный код | `Route::referral('promo', PromoController::class)` |
| `Route::fallback($action)` | Всё, что не совпало | `Route::fallback(FallbackController::class)` |

### Приоритет маршрутов

`event` > `command` > `action` > `referral` > `media` > `location` > `contact` > `pattern` > `phrase` > `fallback`

### Алиасы фраз

```php
Route::phrase('привет', HelloController::class)
    ->alias(['здравствуйте', 'добрый день']);
```

### Middleware

```php
Route::middleware(AuthMiddleware::class, function () {
    Route::command('admin', AdminController::class);
    Route::command('stats', StatsController::class);
});
```

### Вложенные маршруты

```php
Route::phrase('меню', function () {
    Route::phrase('цены', PriceController::class);
    Route::phrase('контакты', ContactInfoController::class);
});
```

## Контроллеры

Контроллер наследует `Govorun\Routing\Controller`. Входная точка — метод `handle()` (без аргументов):

```php
<?php

namespace App\Controllers;

use Govorun\Routing\Controller;

class StartController extends Controller
{
    public function handle(): void
    {
        $this->reply('Привет! Я бот на Govorun Framework.');
    }
}
```

### Доступные свойства и методы контроллера

| Свойство / метод | Тип | Описание |
|---|---|---|
| `$this->message` | `IncomingMessage` | Входящее сообщение (свойство, не метод) |
| `$this->driver` | `MessengerDriver` | Драйвер мессенджера |
| `$this->state` | `StateAccessor` | `PersistentState` — write-through state, каждый `set()` сразу пишет в `StateStorage`. При отсутствии хранилища — пустая `StateData`. |
| `$this->reply(string $text)` | `void` | Отправить текстовый ответ |
| `$this->send(OutgoingMessage $message)` | `void` | Отправить сообщение с клавиатурой/медиа |
| `$this->user()` | `UserDto` | Сокращение для `$this->message->user` |
| `$this->param(string $key)` | `?string` | Параметр callback-действия (для `Route::action()`) |
| `$this->startFlow(string $flowClass)` | `void` | Запустить Flow-диалог |
| `$this->http()` | `HttpManager` | Через трейт `MakesHttpCalls` — доступ к подключениям из `config/connections.php`: `$this->http()->connection('slug')->get(...)`. |

> **Auto-finalize inline-клавиатуры.** Когда контроллер отправляет через `send()` сообщение с inline-клавиатурой, имеющей action-кнопки, фреймворк сохраняет в storage контекст. На следующем Action-сообщении исходное сообщение редактируется: клавиатура убирается, к тексту дописывается `(выбрано: <label>)`. Дополнительно ничего делать не нужно.

### Входящее сообщение (IncomingMessage)

```php
$msg = $this->message;

$msg->id;           // ID сообщения
$msg->chatId;       // ID чата
$msg->text;         // текст сообщения
$msg->driverName;   // имя драйвера (telegram)
$msg->type;         // ContentType enum
$msg->action;       // callback action
$msg->actionParams; // параметры action
$msg->event;        // имя события
$msg->media;        // MediaDto
$msg->location;     // LocationDto
$msg->contact;      // ContactDto
$msg->referral;     // реферальный код
$msg->raw;          // сырые данные от мессенджера
```

### Данные пользователя (UserDto)

```php
$user = $this->user();

$user->id;        // ID пользователя
$user->firstName; // имя
$user->lastName;  // фамилия
$user->username;  // username
$user->phone;     // телефон
$user->locale;    // локаль
$user->raw;       // сырые данные
```

### Типы контента (ContentType)

| Значение | Описание |
|----------|----------|
| `ContentType::Text` | Текстовое сообщение |
| `ContentType::Action` | Callback-действие |
| `ContentType::Media` | Медиафайл |
| `ContentType::Location` | Геолокация |
| `ContentType::Contact` | Контакт |
| `ContentType::Event` | Событие |

## Клавиатуры

### Inline-клавиатура

```php
use Govorun\Messaging\Button;
use Govorun\Messaging\Keyboard;
use Govorun\Messaging\Message;

$msg = Message::make('Выберите действие:')
    ->keyboard(
        Keyboard::make()->buttons([
            [
                Button::make('Да')->action('confirm', ['id' => 1]),
                Button::make('Нет')->action('cancel'),
            ],
            [
                Button::make('Ссылка')->url('https://example.com'),
            ],
        ])
    );

$this->send($msg);
```

### Reply-клавиатура

```php
$msg = Message::make('Поделитесь контактом:')
    ->keyboard(
        Keyboard::reply()
            ->resize()   // подогнать высоту под содержимое
            ->oneTime()  // скрыть после нажатия
            ->buttons([
                [Button::make('Отправить контакт')->requestContact()],
                [Button::make('Отправить локацию')->requestLocation()],
            ])
    );

$this->send($msg);
```

### Удаление клавиатуры

```php
$msg = Message::make('Клавиатура убрана.')
    ->keyboard(Keyboard::remove());

$this->send($msg);
```

## Медиа

```php
use Govorun\Messaging\Media;

$this->send(Media::photo('https://example.com/image.jpg')->caption('Описание'));
$this->send(Media::document('https://example.com/file.pdf'));
$this->send(Media::video('https://example.com/clip.mp4'));
$this->send(Media::audio('https://example.com/track.mp3'));
$this->send(Media::voice('https://example.com/voice.ogg'));
$this->send(Media::animation('https://example.com/anim.gif'));
```

**Локальный файл.** Telegram-драйвер умеет отправлять локальные файлы через multipart-upload — если в URL передать существующий локальный путь, фреймворк сам отправит файл, а не URL:

```php
$this->send(Media::photo('/var/www/storage/uploads/cat.jpg'));
```

## Flow-диалоги

Flow — пошаговый диалог с пользователем. Состояние сохраняется между шагами.

### Создание

```bash
php govorun make:flow RegistrationFlow
```

### Пример

```php
<?php

namespace App\Flows;

use Govorun\State\Flow;
use Govorun\State\Step;
use Govorun\Messaging\Button;
use Govorun\Messaging\Keyboard;
use Govorun\Messaging\IncomingMessage;

class RegistrationFlow extends Flow
{
    protected array $steps = ['name', 'phone', 'confirm'];

    // Команды, которые прерывают flow
    protected array $interruptCommands = ['/start', '/cancel'];

    // Прерывать flow при получении события (по умолчанию true)
    protected bool $interruptOnEvent = true;

    public function nameStep(Step $step): void
    {
        $step->ask('Как вас зовут?');

        $step->receive(function (IncomingMessage $msg) {
            // Шорткат-валидатор: ошибка автоматически летит пользователю,
            // если fails() — текущий шаг не двигаем, ждём корректный ответ.
            if ($this->validator($msg->text)->required()->fails()) {
                return;
            }

            $this->state->set('name', $msg->text);
            $this->nextStep();
        });
    }

    public function phoneStep(Step $step): void
    {
        $step->ask(
            'Ваш номер телефона?',
            Keyboard::reply()->resize()->oneTime()->buttons([
                [Button::make('Отправить контакт')->requestContact()],
            ]),
        );

        $step->receive(function (IncomingMessage $msg) {
            $phone = $msg->contact?->phone ?? $msg->text;
            $this->state->set('phone', $phone);
            $this->nextStep();
        });
    }

    public function confirmStep(Step $step): void
    {
        $name = $this->state->get('name');
        $phone = $this->state->get('phone');

        $step->ask(
            "Имя: {$name}\nТелефон: {$phone}\n\nВсё верно?",
            Keyboard::make()->buttons([
                [
                    Button::make('Да')->action('confirm'),
                    Button::make('Нет')->action('cancel'),
                ],
            ]),
        );

        $step->receive(function (IncomingMessage $msg) {
            if ($msg->action === 'confirm') {
                $this->reply('Регистрация завершена!');
            } else {
                // Прыжок на конкретный шаг — должен быть в $steps.
                // Без аргумента nextStep() переходит к следующему по порядку
                // или завершает flow, если шаг последний.
                $this->nextStep('name');
                return;
            }

            $this->nextStep(); // на последнем шаге — финализирует и чистит state
        });
    }

    public function onComplete(): void
    {
        // Вызывается после nextStep() на последнем шаге
    }

    public function onCancel(): void
    {
        $this->reply('Диалог прерван.');
    }
}
```

### Прерывание flow

`shouldInterrupt(IncomingMessage)` возвращает true в трёх случаях:

1. Активна inline-клавиатура `ask` (есть `__ask_keyboard_ctx` в state) и пришёл **текст** — пользователь не нажал кнопку, а написал что-то ещё. События в этом режиме flow не прерывают.
2. `$interruptOnEvent === true` и пришло событие.
3. Текст совпадает с одной из `$interruptCommands` (или начинается на `<cmd> `).

При прерывании вызывается `onCancel()`, состояние очищается, исходное `ask`-сообщение (если была inline-клавиатура) редактируется — клавиатура убирается, дописывается `(отменено)`.

### Запуск Flow из контроллера

```php
class StartController extends Controller
{
    public function handle(): void
    {
        $this->startFlow(\App\Flows\RegistrationFlow::class);
    }
}
```

**Важно:** `$this->nextStep()` обязателен в каждом `receive` callback. На последнем шаге он завершает flow и очищает состояние. Без него flow останется активным и будет перехватывать все последующие сообщения.

### Данные состояния (StateData)

```php
$this->state->set('key', 'value');   // сохранить
$this->state->get('key');            // получить
$this->state->has('key');            // проверить наличие
$this->state->all();                 // получить всё
```

## Middleware

Middleware обрабатывает сообщение до передачи в контроллер:

```php
<?php

namespace App\Middleware;

use Govorun\Routing\Middleware;
use Govorun\Messaging\IncomingMessage;

class AuthMiddleware implements Middleware
{
    public function handle(IncomingMessage $message, \Closure $next): void
    {
        $allowedUsers = [123456789];

        if (!in_array($message->user->id, $allowedUsers)) {
            // Не вызываем $next — блокируем обработку
            return;
        }

        $next($message);
    }
}
```

## HTTP-подключения (рекомендуемый путь)

Конфигурация — `config/connections.php`. Каждый ключ массива — slug. Поддерживаемые типы auth: `none`, `bearer`, `api_key` (header или query), `basic`.

```php
// config/connections.php
return [
    'payment' => [
        'base_url' => 'https://api.payment.com/v1',
        'default_headers' => ['Accept' => 'application/json'],
        'auth' => [
            'type' => 'bearer',
            'token' => env('PAYMENT_TOKEN', ''),
        ],
    ],
];
```

Использование через трейт `MakesHttpCalls`, который подмешан в `Controller` и `Flow`:

```php
$response = $this->http()->connection('payment')->post('/charge', [
    'json' => ['amount' => 100, 'currency' => 'RUB'],
]);

if ($response->successful()) {
    $data = $response->json();
}
```

`HttpResponse` — `status(): int`, `successful(): bool`, `failed(): bool`, `body(): string`, `json(): ?array`. Таймаут 10 секунд, `http_errors=false` (исключений на 4xx/5xx не бросает).

Опции Guzzle (`json`, `form_params`, `query`, `headers`, …) пробрасываются через второй аргумент метода.

## API-клиенты (альтернатива)

Если удобнее иметь типизированный клиент-наследник, а не вызывать `connection('slug')`:

```bash
php govorun make:api-client WeatherClient
```

```php
<?php

namespace App\Services;

use Govorun\Http\ApiClient;

class WeatherClient extends ApiClient
{
    protected int $timeout = 10;
    protected int $retries = 2;

    public function baseUrl(): string
    {
        return 'https://api.weather.com/v1';
    }

    public function headers(): array
    {
        return ['Authorization' => 'Bearer ' . env('WEATHER_API_KEY')];
    }

    public function forecast(string $city): array
    {
        return $this->get('/forecast', ['city' => $city]);
    }
}
```

## Валидация ввода

`Govorun\Support\Validator` — fluent-валидатор с дефолтными текстами ошибок (`resources/validation-messages.json` во фреймворке).

```php
use Govorun\Support\Validator;

$error = Validator::make($msg->text)
    ->required()
    ->numeric()
    ->min(1)
    ->max(999)
    ->validate();   // null если ок

if ($error !== null) {
    $this->reply($error);
}
```

Во `Flow` есть шорткат `$this->validator($value)` — он сам отправит ошибку пользователю через `errorHandler`. Терминал `fails(): bool` возвращает true, если ошибка была отправлена:

```php
if ($this->validator($msg->text)->required()->email()->fails()) {
    return; // остаёмся на том же шаге, ошибка уже у пользователя
}
```

Доступные правила: `required`, `email`, `string`, `numeric`, `integer`, `url`, `phone`, `regex`, `min`, `max`, `between`, `in`, `date`.

## Сервис-провайдеры

Для регистрации своих сервисов в контейнере:

```php
<?php

namespace App\Providers;

use Govorun\Foundation\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('weather', function () {
            return new \App\Services\WeatherClient();
        });
    }

    public function boot(): void
    {
        // После регистрации всех провайдеров
    }
}
```

Подключите в `config/app.php`:

```php
'providers' => [
    App\Providers\AppServiceProvider::class,
],
```

## CLI-команды

| Команда | Описание |
|---------|----------|
| `php govorun webhook:install` | Установить вебхук |
| `php govorun webhook:remove` | Удалить вебхук |
| `php govorun migrate` | Запустить миграции БД (создаёт таблицу `govorun_states` при `STATE_DRIVER=database`) |
| `php govorun make:controller {name}` | Создать контроллер |
| `php govorun make:flow {name}` | Создать Flow-диалог |
| `php govorun make:api-client {name}` | Создать API-клиент |
| `php govorun state:clear` | Очистить состояния Flow |
| `php govorun bot:profile-sync` | Идемпотентно запушить в Telegram Bot API имя/описания/команды из `config/bot_profile.php` и фото из `storage/app/bot-profile.{jpg,mp4}`. Флаги `--only=<section>...` / `--skip=<section>...` для частичной синхронизации (секции: `name`, `short_description`, `description`, `commands`, `photo`). |
| `php govorun test` | Запустить тесты |

## Конфигурация

### config/app.php

```php
'name' => env('APP_NAME', 'GovorunBot'),
'url' => env('APP_URL', 'http://localhost'),
'env' => env('APP_ENV', 'production'),
'debug' => (bool) env('APP_DEBUG', false),
'providers' => [],
```

### config/messenger.php

```php
'default' => env('MESSENGER_DRIVER', 'telegram'),
'drivers' => [env('MESSENGER_DRIVER', 'telegram')],
'telegram' => [
    'token' => env('TELEGRAM_BOT_TOKEN', ''),
    'secret' => env('TELEGRAM_WEBHOOK_SECRET', null),
],
```

`secret` — опционально. Если задан, Telegram будет присылать заголовок `X-Telegram-Bot-Api-Secret-Token` для верификации вебхука.

### config/state.php

```php
'driver' => env('STATE_DRIVER', 'file'),  // file | database | cache
'ttl' => (int) env('STATE_TTL', 3600),    // для cache-драйвера
```

Для `database` — выполнить `php govorun migrate` (создаст таблицу `govorun_states`).

### config/connections.php

HTTP-подключения для `$this->http()->connection('slug')` (см. раздел [HTTP-подключения](#http-подключения-рекомендуемый-путь)). По умолчанию — пустой массив.

### config/bot_profile.php

Профиль Telegram для `bot:profile-sync` (имя, описания, команды). По умолчанию возвращает `null` — команда сообщит «Профиль не найден» и ничего не запушит. Раскомментируйте массив в файле, чтобы включить.

### config/database.php

Поддерживаемые драйверы: `sqlite` (по умолчанию), `mysql`, `pgsql`.

### config/cache.php

Поддерживаемые хранилища: `file` (по умолчанию), `database`.

### config/logging.php

Каналы: `single` (один файл), `daily` (ротация по дням, 14 дней).

## Документация

Полная документация фреймворка: [govorun/framework](https://github.com/bromimo/govorun-framework)
