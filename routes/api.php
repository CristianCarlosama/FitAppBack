<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MedidaController;
use App\Http\Controllers\Api\RutinaController;
use App\Http\Controllers\Api\EjercicioController;

// --- RUTAS PÚBLICAS (Sin token) ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Ejercicios: Permitir ver la lista y el detalle a todo el mundo
Route::get('ejercicios', [EjercicioController::class, 'index']);
Route::get('ejercicios/{ejercicio}', [EjercicioController::class, 'show']);


// --- RUTAS PROTEGIDAS (Con Token) ---
Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/medidas', [MedidaController::class, 'storeOrUpdate']);
    Route::apiResource('rutinas', RutinaController::class);
    Route::post('rutinas/{rutina}/calificar', [RutinaController::class, 'calificar']);

    Route::post('ejercicios/{ejercicio}/calificar', [EjercicioController::class, 'calificar']);

    Route::post('ejercicios', [EjercicioController::class, 'store']);
    Route::put('ejercicios/{ejercicio}', [EjercicioController::class, 'update']);
    Route::delete('ejercicios/{ejercicio}', [EjercicioController::class, 'destroy']);
});