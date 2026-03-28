<?php

use Govorun\Http\Request;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$request = Request::capture();

$app->handleWebhook($request);
