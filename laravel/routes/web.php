<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/users', [UserController::class, 'getUsers']);
Route::get('/users/{id}', [UserController::class, 'getUserItem'])->whereNumber('id');
Route::post('/users', [UserController::class, 'createUser']);
Route::patch('/users/{id}', [UserController::class, 'updateUser'])->whereNumber('id');
Route::delete('/users/{id}', [UserController::class, 'deleteUser'])->whereNumber('id');
