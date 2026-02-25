<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rutina;
use Illuminate\Http\Request;

class RutinaController extends Controller
{
    // GET /api/rutinas
    public function index()
    {
        return Rutina::with('ejercicios')->get();
    }

    // POST /api/rutinas
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
            'dificultad' => 'nullable|in:baja,media,alta',
            'duracion' => 'nullable|integer',
            'es_publica' => 'boolean',
            'ejercicios' => 'required|array|min:1',
            'ejercicios.*' => 'exists:ejercicios,id', // solo IDs
        ]);

        $rutina = Rutina::create([
            'user_id' => auth()->id(),
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'dificultad' => $data['dificultad'] ?? null,
            'duracion' => $data['duracion'] ?? null,
            'es_publica' => $data['es_publica'] ?? false,
        ]);

        // Solo IDs, nada más en la pivote
        $rutina->ejercicios()->sync($data['ejercicios']);

        return response()->json($rutina->load('ejercicios'), 201);
    }

    // GET /api/rutinas/{id}
    public function show(Rutina $rutina)
    {
        $rutina->load('ejercicios');
        $rutina->promedio_calificacion = $rutina->calificaciones()->avg('puntos');
        return response()->json($rutina);
    }

    // PUT /api/rutinas/{id}
    // PUT /api/rutinas/{id}
    public function update(Request $request, Rutina $rutina)
    {
        // 1. Limpiamos el input ANTES de validar
        if ($request->has('ejercicios')) {
            $ejerciciosLimpios = collect($request->input('ejercicios'))->map(function($ej) {
                // Si es un objeto/array, sacamos solo el ID. Si ya es ID, lo dejamos.
                return is_array($ej) ? ($ej['id'] ?? null) : $ej;
            })->filter()->values()->toArray();

            // Reemplazamos los ejercicios en el request para que la validación pase
            $request->merge(['ejercicios' => $ejerciciosLimpios]);
        }

        // 2. Ahora sí validamos (ya son IDs puros)
        $data = $request->validate([
            'nombre' => 'sometimes|string',
            'descripcion' => 'nullable|string',
            'dificultad' => 'nullable|in:baja,media,alta',
            'duracion' => 'nullable|integer',
            'es_publica' => 'boolean',
            'ejercicios' => 'sometimes|array|min:1',
            'ejercicios.*' => 'exists:ejercicios,id', 
        ]);

        // 3. Actualizamos rutina
        $rutina->update($request->only(['nombre', 'descripcion', 'dificultad', 'duracion', 'es_publica']));

        // 4. Sincronizamos (sync borra los viejos y pone los nuevos del form)
        if ($request->has('ejercicios')) {
            $rutina->ejercicios()->sync($request->input('ejercicios'));
        }

        return response()->json($rutina->load('ejercicios'));
    }

    // DELETE /api/rutinas/{id}
    public function destroy(Rutina $rutina)
    {
        $rutina->delete();
        return response()->noContent();
    }

    // POST /api/rutinas/{id}/calificar
    public function calificar(Request $request, Rutina $rutina)
    {
        $data = $request->validate([
            'puntos' => 'required|integer|min:1|max:5',
        ]);

        $rutina->calificaciones()->updateOrCreate(
            ['user_id' => auth()->id()],
            ['puntos' => $data['puntos']]
        );

        return response()->json([
            'promedio' => $rutina->calificaciones()->avg('puntos')
        ]);
    }
}