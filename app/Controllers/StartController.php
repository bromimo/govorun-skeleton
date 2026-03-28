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
