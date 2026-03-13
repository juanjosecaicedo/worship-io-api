<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ─── Autenticación (pública) ──────────────────────────────
Route::prefix('v1/auth')->group(function () {
  Route::post('register', [AuthController::class, 'register']);
  Route::post('login', [UserController::class, 'login']);
});

// ─── Rutas protegidas con Sanctum ────────────────────────
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
  // Auth
  Route::post('auth/logout', [AuthController::class, 'logout']);
  Route::get('auth/me',      [AuthController::class, 'me']);
  Route::patch('user/profile',       [UserController::class, 'update']);
  Route::patch('user/vocal-profile', [UserController::class, 'updateVocalProfile']);
  Route::delete('user',              [UserController::class, 'destroy']);
});
