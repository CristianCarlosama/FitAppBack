<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Entrenamiento;
use App\Models\EntrenamientoSerie;
use App\Models\MedidaCorporal; // Importamos para el calendario
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
            $entrenamientoId = DB::transaction(function () use ($validated) {
                $entrenamiento = Entrenamiento::create([
                    'user_id' => auth()->id(),
                    'rutina_id' => $validated['rutina_id'],
                    'fecha_inicio' => $validated['fecha_inicio'],
                    'fecha_fin' => $validated['fecha_fin'],
                    'notas_sesion' => $validated['notas_sesion'] ?? null,
                ]);
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
            return response()->json([
                'error' => 'Error de servidor',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

public function getDetalleCalendario($fecha)
{
    try {
        $userId = auth()->id();

        $entrenamientos = Entrenamiento::with(['series.ejercicio', 'rutina'])
            ->where('user_id', $userId)
            ->whereDate('fecha_inicio', $fecha)
            ->get()
            ->map(function ($ent) {
                return [
                    'id' => $ent->id,
                    'nombre_rutina' => $ent->rutina->nombre ?? 'Sesión Libre',
                    'hora' => Carbon::parse($ent->fecha_inicio)->format('H:i'),
                    'notas' => $ent->notas_sesion,
                    'ejercicios' => $ent->series->groupBy('ejercicio_id')->map(function ($series) {
                        $primera = $series->first();
                        return [
                            'nombre' => $primera && $primera->ejercicio ? $primera->ejercicio->nombre : 'Ejercicio',
                            'series' => $series->map(fn($s) => [
                                'peso' => $s->peso,
                                'reps' => $s->reps,
                                'rpe' => $s->rpe
                            ])
                        ];
                    })->values()
                ];
            });

        return response()->json([
            'data' => [
                'entrenamientos' => $entrenamientos,
                'medidas' => null
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    public function getResumenMes($year, $month)
    {
        $userId = auth()->id(); 

        if (!$userId) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        $dias = \App\Models\Entrenamiento::where('user_id', $userId)
            ->whereYear('fecha_inicio', $year)
            ->whereMonth('fecha_inicio', $month)
            ->get()
            ->map(fn($ent) => (int) \Carbon\Carbon::parse($ent->fecha_inicio)->format('d'))
            ->unique()
            ->values();

        return response()->json($dias);
    }
}