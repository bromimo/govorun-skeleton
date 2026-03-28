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
