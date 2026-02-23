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
            'user_id' => 'required|exists:users,id',
            'ejercicios' => 'required|array|min:1',
            'ejercicios.*.nombre' => 'required|string',
            'ejercicios.*.clase' => 'required|string',
            'ejercicios.*.series' => 'nullable|integer',
            'ejercicios.*.repeticiones' => 'nullable|integer',
            'ejercicios.*.descanso' => 'nullable|integer',
            'ejercicios.*.video_url' => 'nullable|url',
            'ejercicios.*.foto_1' => 'nullable|url',
            'ejercicios.*.foto_2' => 'nullable|url',
            'ejercicios.*.foto_3' => 'nullable|url',
        ]);

        $rutina = Rutina::create($data);

        foreach ($data['ejercicios'] as $ej) {
            $rutina->ejercicios()->create($ej);
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
            'user_id' => 'required|exists:users,id',
            'puntos' => 'required|integer|min:1|max:5',
        ]);

        $rutina->calificaciones()->updateOrCreate(
            ['user_id' => $data['user_id']],
            ['puntos' => $data['puntos']]
        );

        return response()->json([
            'promedio' => $rutina->calificaciones()->avg('puntos')
        ]);
    }
}