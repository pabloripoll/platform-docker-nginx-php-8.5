<?php

use Core\Route;
use App\Controller\WebController;

/**
 * Web routes
 */

Route::get('/', [WebController::class, 'home']);
Route::get('/home', [WebController::class, 'home']);
