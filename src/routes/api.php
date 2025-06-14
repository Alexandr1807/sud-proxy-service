<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SudController;
use App\Http\Controllers\LogController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout',      [AuthController::class, 'logout']);
    Route::get('users',        [AuthController::class, 'allUsers']);
    Route::get('sud/logs',     [LogController::class, 'index']);
    Route::get('sud/{jurisdiction}/{operation}/{value}', [SudController::class, 'dynamicProxy']);
});
