# govorun/skeleton — Design Spec

## Overview

Шаблон проекта для `govorun/framework` (мульти-мессенджер бот-фреймворк на PHP 8.3+). Устанавливается через `composer create-project govorun/skeleton my-bot`.

## Структура каталогов

```
govorun-skeleton/
├── app/
│   ├── Controllers/
│   │   └── StartController.php
│   └── Support/
│       └── ComposerScripts.php
├── bootstrap/
│   └── app.php
├── config/
│   ├── app.php
│   ├── messenger.php
│   ├── database.php
│   ├── cache.php
│   └── logging.php
├── routes/
│   └── messenger.php
├── public/
│   └── index.php
├── storage/
│   ├── logs/
│   │   └── .gitkeep
│   └── state/
│       └── .gitkeep
├── govorun
├── .env.example
├── composer.json
├── .gitignore
└── README.md
```

## Файлы и их содержимое

### `composer.json`

```json
{
    "name": "govorun/skeleton",
    "description": "Skeleton application for the Govorun bot framework",
    "type": "project",
    "license": "MIT",
    "require": {
        "php": "^8.3",
        "govorun/framework": "^1.0"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    },
    "scripts": {
        "post-create-project-cmd": [
            "App\\Support\\ComposerScripts::postCreateProject"
        ]
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
```

### `bootstrap/app.php`

Единая точка создания Application. Используется и CLI, и webhook entry point.

```php
<?php

use Govorun\Foundation\Application;

$app = new Application(dirname(__DIR__));

return $app;
```

Конструктор `Application` принимает `$basePath`. Внутри фреймворка при вызове `handleConsole()` и `handleWebhook()` автоматически выполняется:
1. `loadEnvironment()` — загрузка `.env`
2. `loadConfiguration()` — загрузка `config/*.php`
3. `registerCoreProviders()` — EventServiceProvider, LogServiceProvider, StateServiceProvider
4. `registerConfiguredProviders()` — провайдеры из `config('app.providers')`
5. `boot()` — загрузка всех провайдеров

### `govorun` (CLI entry point)

```php
#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$status = $app->handleConsole();

exit($status);
```

Метод `handleConsole(): int` — загружает env, конфиги, регистрирует провайдеры (включая ConsoleServiceProvider), бутит, запускает `app('artisan')->run()`.

Доступные команды фреймворка:
- `webhook:install` — установка вебхука
- `webhook:remove` — удаление вебхука
- `migrate` — миграции БД
- `make:controller {name}` — генерация контроллера
- `make:flow` — генерация Flow
- `make:api-client` — генерация API-клиента
- `state:clear` — очистка состояний
- `test` — запуск тестов

### `public/index.php` (webhook entry point)

```php
<?php

use Govorun\Http\Request;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$request = Request::capture();

$app->handleWebhook($request);
```

`Request::capture()` — создаёт объект из `$_SERVER`, `$_GET`, `$_POST`, `php://input`.

`handleWebhook(Request $request): int` — определяет драйвер по последнему сегменту URL, верифицирует вебхук, парсит update в `IncomingMessage`, прогоняет через FlowHandler → Router.

URL вебхука: `https://example.com/index.php/telegram` — последний сегмент определяет драйвер.

### `routes/messenger.php`

```php
<?php

use App\Controllers\StartController;
use Govorun\Routing\Route;

Route::command('start', StartController::class);
```

`Route::command($name, $action)` — регистрирует обработчик команды. Имя без слеша. Action — класс контроллера (вызывается метод `handle()` или `__invoke()`).

### `app/Controllers/StartController.php`

```php
<?php

namespace App\Controllers;

use Govorun\Messaging\IncomingMessage;
use Govorun\Routing\Controller;

class StartController extends Controller
{
    public function handle(IncomingMessage $message): void
    {
        $this->reply('Привет! Я бот на Govorun Framework.');
    }
}
```

Наследует `Govorun\Routing\Controller`. Доступные методы:
- `$this->reply(string $text)` — отправить текстовый ответ
- `$this->send(OutgoingMessage $message)` — отправить сообщение с клавиатурой/медиа
- `$this->message()` — получить IncomingMessage
- `$this->user()` — получить UserDto
- `$this->param(string $key)` — параметр action
- `$this->startFlow(string $flowClass)` — запустить Flow-диалог

### `app/Support/ComposerScripts.php`

```php
<?php

namespace App\Support;

use Composer\Script\Event;

class ComposerScripts
{
    public static function postCreateProject(Event $event): void
    {
        $basePath = realpath($event->getComposer()->getConfig()->get('vendor-dir') . '/..');

        // Copy .env.example → .env
        $envExample = $basePath . '/.env.example';
        $env = $basePath . '/.env';
        if (file_exists($envExample) && !file_exists($env)) {
            copy($envExample, $env);
            $event->getIO()->write('<info>Created .env from .env.example</info>');
        }

        // Print instructions
        $event->getIO()->write('');
        $event->getIO()->write('<comment>Govorun skeleton installed!</comment>');
        $event->getIO()->write('');
        $event->getIO()->write('Next steps:');
        $event->getIO()->write('  1. Set your bot token in <info>.env</info>');
        $event->getIO()->write('  2. Run <info>php govorun webhook:install</info> for webhook mode');
        $event->getIO()->write('     Or point your web server to <info>public/</info> directory');
        $event->getIO()->write('');
    }
}
```

### `config/app.php`

```php
<?php

return [
    'name' => env('APP_NAME', 'GovorunBot'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'providers' => [],
];
```

`providers` — массив дополнительных ServiceProvider-классов пользователя.

### `config/messenger.php`

```php
<?php

return [
    'default' => env('MESSENGER_DRIVER', 'telegram'),

    'telegram' => [
        'token' => env('TELEGRAM_BOT_TOKEN', ''),
        'secret' => env('TELEGRAM_WEBHOOK_SECRET', null),
    ],
];
```

- `default` — активный драйвер.
- `telegram.token` — токен бота от @BotFather.
- `telegram.secret` — секрет для верификации webhook (опционально, заголовок `X-Telegram-Bot-Api-Secret-Token`).

### `config/database.php`

```php
<?php

return [
    'default' => env('DB_CONNECTION', 'sqlite'),

    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
        ],
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'govorun'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ],
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'govorun'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
        ],
    ],
];
```

SQLite по умолчанию — для быстрого старта без внешних зависимостей.

### `config/cache.php`

```php
<?php

return [
    'default' => env('CACHE_DRIVER', 'file'),

    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => storage_path('cache'),
        ],
        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null,
        ],
    ],
];
```

### `config/logging.php`

```php
<?php

return [
    'default' => env('LOG_CHANNEL', 'single'),

    'channels' => [
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/govorun.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/govorun.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],
    ],
];
```

### `.env.example`

```env
APP_NAME=GovorunBot
APP_ENV=production
APP_DEBUG=false

MESSENGER_DRIVER=telegram
TELEGRAM_BOT_TOKEN=
TELEGRAM_WEBHOOK_SECRET=

DB_CONNECTION=sqlite
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=file

LOG_CHANNEL=single
LOG_LEVEL=debug
```

### `.gitignore`

```
/vendor/
/.env
/.phpunit.result.cache
/storage/logs/*.log
!/storage/logs/.gitkeep
/storage/state/*.json
!/storage/state/.gitkeep
```

### `README.md`

Краткое описание проекта:
- Что это
- Установка через `composer create-project`
- Настройка `.env`
- Запуск (webhook / polling)
- Ссылка на документацию фреймворка

## Post-install поведение

При `composer create-project govorun/skeleton my-bot`:
1. Composer клонирует репозиторий и устанавливает зависимости
2. Срабатывает `post-create-project-cmd`
3. `ComposerScripts::postCreateProject()`:
   - Копирует `.env.example` → `.env`
   - Выводит инструкции

## Важные замечания

- В фреймворке нет встроенной команды polling. Webhook — основной режим работы. Polling может быть добавлен позже как отдельная команда.
- Драйвер определяется по последнему сегменту URL вебхука (`/index.php/telegram`).
- `config/messenger.php` хранит конфиг драйверов. Ключ конфига совпадает с именем драйвера.
- `Route::command('start', ...)` — без слеша в имени команды.
- Контроллер должен наследовать `Govorun\Routing\Controller`, метод `handle()` вызывается автоматически.
