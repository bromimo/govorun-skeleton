# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Что это

Скелетон-приложение для [`govorun/framework`](https://github.com/bromimo/govorun-framework). Тонкий starter: точки входа (`public/index.php`, `govorun`, `bootstrap/app.php`), пустой `app/Controllers/StartController` и конфиги. Бизнес-логики **нет** — её добавляет пользователь или генерирует [`govorun-factory`](https://github.com/bromimo/govorun-factory) поверх этого скелетона.

Локальные репозитории: `C:\domains\govorun-framework`, `C:\domains\govorun-factory`.

## Команды

```bash
composer install
php govorun webhook:install     # установить вебхук Telegram
php govorun bot:profile-sync    # запушить имя/описания/команды/фото в Bot API
php govorun state:clear         # очистить state Flow-диалогов
php govorun migrate             # миграции (для STATE_DRIVER=database)
```

Тестов в самом скелетоне нет. `php govorun test` есть, но в чистом репо запускает пустой набор.

## Что генерирует фабрика поверх скелетона

`govorun-factory` использует `resources/stubs/skeleton/` как заготовку — копирует целиком и поверх кладёт `app/Controllers/**`, `app/Flows/**`, `routes/messenger.php` (перезаписывая дефолт), `config/connections.php`, `config/bot_profile.php`, `composer.json`. Поэтому **в скелетоне нет лишнего**: только то, что одинаково для всех ботов.

## Архитектурные точки

- **Точка входа вебхука** — `public/index.php`: `Application::handleWebhook(Request::capture())`. Никакой Router/middleware-bootstrap'а здесь нет — всё внутри `Application`.
- **CLI** — `./govorun`: `Application::handleConsole()`. Команды регистрирует `ConsoleServiceProvider` из фреймворка.
- **Конфиги** — `Application::loadConfiguration()` сканирует `config/*.php` glob'ом. Имя файла = ключ в `config()` (т.е. `config/state.php` → `config('state.driver')`).
- **State storage** — `StateServiceProvider` фреймворка читает `config('state.driver', 'file')`. Без `config/state.php` работает на дефолте `file`.
- **HTTP-подключения** — трейт `MakesHttpCalls` подмешан в `Controller` и `Flow`. Читает `config('connections', [])`. Используется через `$this->http()->connection('slug')->...`.

## Code Style

PHP-конвенции из `govorun/framework`:

- PHPDocs на русском, описание класса на первой строке `/**` без пустой строки. Теги: `@param`, `@return`, `@throws`.
- `use`-импорты по возрастанию длины строки.

## Релизы и теги

После мержа feature-PR в `develop` тегировать semver. Bump-правила:

- `chore: bump govorun/framework to ^X.0` → **minor** (или major, если ломает потребителей: смотреть BREAKING CHANGE во фреймворке).
- Docs-only / config-only → **patch**.

Последний тег: `git tag --sort=-v:refname | head -3`.

## Что НЕ трогать без причины

- `composer.json::require::govorun/framework` — менять только синхронно с тегом фреймворка, отдельным PR.
- `public/index.php`, `bootstrap/app.php`, `govorun` — точки входа: минимальные, любые изменения тянут за собой обратную несовместимость для существующих ботов.
- `.gitignore` исключает `/.claude/`, `/.idea/`, `/docs/superpowers/`, `composer.phar` — эти артефакты не должны попадать в релизный архив (см. также `.gitattributes` `export-ignore`).