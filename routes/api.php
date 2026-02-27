<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MedidaController;
use App\Http\Controllers\Api\RutinaController;
use App\Http\Controllers\Api\EjercicioController;
use App\Http\Controllers\Api\MusculoController; 
use Illuminate\Support\Facades\Artisan; // Necesario para el fix

// --- RUTA DE MANTENIMIENTO (FIX PARA RAILWAY) ---
// Ejecuta esto una vez en el navegador: fitappback-production.up.railway.app/api/fix-storage
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

    // Gestión de ejercicios
    Route::post('ejercicios', [EjercicioController::class, 'store']);
    
    // CAMBIO VITAL: Usamos match para que acepte el POST que envía el FormData con el _method PUT
    Route::match(['put', 'post'], 'ejercicios/{ejercicio}', [EjercicioController::class, 'update']);
    
    Route::delete('ejercicios/{ejercicio}', [EjercicioController::class, 'destroy']);
    Route::post('ejercicios/{ejercicio}/calificar', [EjercicioController::class, 'calificar']);
});