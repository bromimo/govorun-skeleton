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
