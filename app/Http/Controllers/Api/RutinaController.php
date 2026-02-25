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
            'ejercicios.*' => 'exists:ejercicios,id',
            'ejercicios.*.series' => 'nullable|integer',
            'ejercicios.*.repeticiones' => 'nullable|integer',
            'ejercicios.*.descanso' => 'nullable|integer',
            'ejercicios.*.video_url' => 'nullable|url',
            'ejercicios.*.foto_1' => 'nullable|url',
            'ejercicios.*.foto_2' => 'nullable|url',
            'ejercicios.*.foto_3' => 'nullable|url',
        ]);

        $rutina = Rutina::create([
            'user_id' => auth()->id(), // 👈 AQUÍ va el user
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'dificultad' => $data['dificultad'] ?? null,
            'duracion' => $data['duracion'] ?? null,
            'es_publica' => $data['es_publica'] ?? false,
        ]);

        foreach ($data['ejercicios'] as $ej) {
            $rutina->ejercicios()->attach($data['ejercicios']);
        }

        return response()->json($rutina->load('ejercicios'), 201);
    }

    // GET /api/rutinas/{id}
    public function show(Rutina $rutina)
    {
        $rutina->load('ejercicios');

        // agregar promedio de calificación
        $rutina->promedio_calificacion = $rutina->calificaciones()->avg('puntos');
        return response()->json($rutina);
    }

    // PUT /api/rutinas/{id}
    public function update(Request $request, Rutina $rutina)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|string',
            'descripcion' => 'nullable|string',
            'dificultad' => 'nullable|in:baja,media,alta',
            'duracion' => 'nullable|integer',
            'es_publica' => 'boolean',
        ]);

        $rutina->update($data);
        return response()->json($rutina);
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
            ['user_id' => auth()->id()], // 👈 aquí el usuario autenticado
            ['puntos' => $data['puntos']]
        );

        return response()->json([
            'promedio' => $rutina->calificaciones()->avg('puntos')
        ]);
    }
}