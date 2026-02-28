<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MedidaController;
use App\Http\Controllers\Api\RutinaController;
use App\Http\Controllers\Api\EjercicioController;
use App\Http\Controllers\Api\MusculoController;
use App\Http\Controllers\Api\EntrenamientoController; 
use Illuminate\Support\Facades\Artisan; 

Route::get('/fix-storage', function () {
    Artisan::call('storage:link');
    return response()->json(['message' => 'Enlace simbólico creado con éxito']);
});

// --- RUTAS PÚBLICAS ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('ejercicios', [EjercicioController::class, 'index']);
Route::get('ejercicios/{ejercicio}', [EjercicioController::class, 'show']);

Route::get('/musculos', [MusculoController::class, 'index']); 

Route::apiResource('rutinas', RutinaController::class);
Route::post('rutinas/{rutina}/calificar', [RutinaController::class, 'calificar']);

// --- RUTAS PROTEGIDAS ---
Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/medidas', [MedidaController::class, 'storeOrUpdate']);

    Route::post('ejercicios', [EjercicioController::class, 'store']);
    
    Route::match(['put', 'post'], 'ejercicios/{ejercicio}', [EjercicioController::class, 'update']);
    
    Route::delete('ejercicios/{ejercicio}', [EjercicioController::class, 'destroy']);
    Route::post('ejercicios/{ejercicio}/calificar', [EjercicioController::class, 'calificar']);

    Route::post('/entrenamientos', [EntrenamientoController::class, 'store']);
});