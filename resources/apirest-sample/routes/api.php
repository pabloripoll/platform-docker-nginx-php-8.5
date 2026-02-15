<?php

use Core\Route;
use App\Controller\ApiController;

/**
 * API routes
 */

Route::post('/api/test/mail', [ApiController::class, 'testMail']);
Route::post('/api/test/queue', [ApiController::class, 'testQueue']);
Route::get('/api/users/{id}', [ApiController::class, 'show'], ['AuthMiddleware']);
Route::post('/api/users/{id}/notes', [ApiController::class, 'addNote'], ['AuthMiddleware']);
