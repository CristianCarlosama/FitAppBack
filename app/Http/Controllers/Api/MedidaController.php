<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MedidaCorporal;
use App\Models\Entrenamiento;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; 

class MedidaController extends Controller
{
    public function storeOrUpdate(Request $request)
    {
        $user = Auth::guard('api')->user();
        $data = $request->only(['peso', 'pecho', 'cintura', 'brazo', 'pierna']);

        if (empty(array_filter($data, fn($v) => $v !== null))) {
            return response()->json(['message' => 'No se enviaron medidas'], 200);
        }

        $medida = MedidaCorporal::updateOrCreate(
            [
                'user_id' => $user->id,
                'created_at' => Carbon::today() // O la fecha que prefieras
            ],
            $data
        );

        return response()->json([
            'message' => 'Medidas guardadas con éxito',
            'medida' => $medida
        ]);
    }

    public function getDetalleCalendario(Request $request, $fecha)
    {
        $userId = auth()->id();

        $entrenamientos = Entrenamiento::with(['series.ejercicio', 'rutina'])
            ->where('user_id', $userId)
            ->whereDate('fecha_inicio', $fecha)
            ->get()
            ->map(function ($entrenamiento) {
                return [
                    'id' => $entrenamiento->id,
                    'tipo' => 'entrenamiento',
                    'rutina_nombre' => $entrenamiento->rutina->nombre ?? 'Entrenamiento Libre',
                    'notas_sesion' => $entrenamiento->notas_sesion,
                    'hora_inicio' => Carbon::parse($entrenamiento->fecha_inicio)->format('H:i'),
                    'hora_fin' => $entrenamiento->fecha_fin ? Carbon::parse($entrenamiento->fecha_fin)->format('H:i') : null,
                    'ejercicios' => $entrenamiento->series->groupBy('ejercicio_id')->map(function ($series) {
                        $primerSerie = $series->first();
                        return [
                            'ejercicio_nombre' => $primerSerie->ejercicio->nombre ?? 'Ejercicio borrado',
                            'series' => $series->map(function ($s) {
                                return [
                                    'numero' => $s->numero_serie,
                                    'peso' => $s->peso,
                                    'reps' => $s->reps,
                                    'rpe' => $s->rpe
                                ];
                            })
                        ];
                    })->values()
                ];
            });

        $medidas = MedidaCorporal::where('user_id', $userId)
            ->whereDate('created_at', $fecha) 
            ->first();

        return response()->json([
            'fecha' => $fecha,
            'tiene_datos' => ($entrenamientos->isNotEmpty() || $medidas),
            'data' => [
                'entrenamientos' => $entrenamientos,
                'medidas' => $medidas ? [
                    'peso' => $medidas->peso,
                    'pecho' => $medidas->pecho,
                    'cintura' => $medidas->cintura,
                    'brazo' => $medidas->brazo,
                    'pierna' => $medidas->pierna,
                    'notas' => $medidas->notas ?? null
                ] : null,
                'fotos' => null // Placeholder para el futuro
            ]
        ]);
    }
}