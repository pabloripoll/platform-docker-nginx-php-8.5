<?php

ini_set('error_log', './../storage/logs/errors.log');

require dirname(__DIR__) . '/vendor/autoload.php';

if (env('APP_DEBUG') === true) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

use Core\Route;

require dirname(__DIR__) . '/routes/api.php';

require dirname(__DIR__) . '/routes/web.php';

Route::fallback(function () {
    return response()->json(['message' => 'not found'], 404);
});

Route::dispatch();
