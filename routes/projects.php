<?php

use Illuminate\Support\Facades\Route;
use Whilesmart\Projects\Http\Controllers\ProjectController;

Route::get('/projects', [ProjectController::class, 'index']);
Route::post('/projects', [ProjectController::class, 'store']);
Route::get('/projects/{id}', [ProjectController::class, 'show']);
Route::put('/projects/{id}', [ProjectController::class, 'update']);
Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
Route::post('/projects/{id}/archive', [ProjectController::class, 'archive']);
Route::post('/projects/{id}/unarchive', [ProjectController::class, 'unarchive']);
