<?php

use Govorun\Http\Request;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$response = $app->handleWebhook(Request::capture());

http_response_code($response->status);
echo $response->body;