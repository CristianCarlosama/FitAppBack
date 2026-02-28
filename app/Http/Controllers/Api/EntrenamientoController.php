<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Entrenamiento;
use App\Models\EntrenamientoSerie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EntrenamientoController extends Controller
{
public function store(Request $request)
{
    $validated = $request->validate([
        'rutina_id' => 'nullable|exists:rutinas,id',
        'fecha_inicio' => 'required',
        'fecha_fin' => 'required',
        'notas_sesion' => 'nullable|string',
        'series' => 'required|array|min:1',
        'series.*.ejercicio_id' => 'required|exists:ejercicios,id',
        'series.*.numero_serie' => 'required|integer',
        'series.*.peso' => 'required|numeric',
        'series.*.reps' => 'required|integer',
        'series.*.rpe' => 'nullable|integer',
    ]);

    try {
        $entrenamientoId = DB::transaction(function () use ($validated, $request) {
            // Crear entrenamiento
            $entrenamiento = Entrenamiento::create([
                'user_id' => auth()->id(),
                'rutina_id' => $validated['rutina_id'],
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'notas_sesion' => $validated['notas_sesion'],
            ]);

            // Crear series
            foreach ($validated['series'] as $serieData) {
                $entrenamiento->series()->create([
                    'ejercicio_id' => $serieData['ejercicio_id'],
                    'numero_serie' => $serieData['numero_serie'],
                    'peso' => $serieData['peso'],
                    'reps' => $serieData['reps'],
                    'rpe' => $serieData['rpe'] ?? null,
                ]);
            }

            return $entrenamiento->id;
        });

        return response()->json([
            'message' => '¡Guardado con éxito!',
            'id' => $entrenamientoId
        ], 201);

    } catch (\Exception $e) {
        // Esto aparecerá en storage/logs/laravel.log
        Log::error("ERROR CRÍTICO ENTRENAMIENTO: " . $e->getMessage());
        
        return response()->json([
            'error' => 'Error de servidor',
            'detalle' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
}
}