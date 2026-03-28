# Govorun Skeleton

Шаблон проекта для [Govorun Framework](https://github.com/govorun/framework) — мульти-мессенджер бот-фреймворк на PHP 8.3+.

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

URL вебхука: `https://example.com/index.php/telegram`

## Структура проекта

```
app/Controllers/    — контроллеры бота
config/             — конфигурационные файлы
routes/messenger.php — маршруты команд
bootstrap/app.php   — точка создания Application
public/index.php    — точка входа для вебхука
govorun             — CLI-утилита
```

## Документация

Полная документация фреймворка: [govorun/framework](https://github.com/govorun/framework)
