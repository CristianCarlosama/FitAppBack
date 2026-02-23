<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; 
use App\Models\Ejercicio;
use Illuminate\Http\Request;

class EjercicioController extends Controller
{
    // GET /api/ejercicios
    public function index()
    {
        return Ejercicio::all();
    }

    // POST /api/ejercicios
    public function store(Request $request)
    {
        $data = $request->validate([
            'rutina_id' => 'nullable|exists:rutinas,id',
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
            'clase' => 'required|string',
            'series' => 'nullable|integer',
            'repeticiones' => 'nullable|integer',
            'descanso' => 'nullable|integer',
            'video_url' => 'nullable|url',
            'foto_1' => 'nullable|url',
            'foto_2' => 'nullable|url',
            'foto_3' => 'nullable|url',
            'editable' => 'sometimes|boolean', // para ejercicios de usuario opcionales
        ]);

        $ejercicio = Ejercicio::create($data);
        return response()->json($ejercicio, 201);
    }

    // GET /api/ejercicios/{id}
    public function show(Ejercicio $ejercicio)
    {
        $ejercicio->promedio_calificacion = $ejercicio->calificaciones()->avg('puntos');
        return response()->json($ejercicio);
    }

    // PUT /api/ejercicios/{id}
    public function update(Request $request, Ejercicio $ejercicio)
    {
        if (!$ejercicio->editable) {
            return response()->json(['error' => 'Este ejercicio no puede ser editado'], 403);
        }

        $data = $request->validate([
            'nombre' => 'sometimes|string',
            'descripcion' => 'nullable|string',
            'clase' => 'sometimes|string',
            'series' => 'nullable|integer',
            'repeticiones' => 'nullable|integer',
            'descanso' => 'nullable|integer',
            'video_url' => 'nullable|url',
            'foto_1' => 'nullable|url',
            'foto_2' => 'nullable|url',
            'foto_3' => 'nullable|url',
        ]);

        $ejercicio->update($data);
        return response()->json($ejercicio);
    }

    // DELETE /api/ejercicios/{id}
    public function destroy(Ejercicio $ejercicio)
    {
        if (!$ejercicio->editable) {
            return response()->json(['error' => 'Este ejercicio no puede ser eliminado'], 403);
        }

        $ejercicio->delete();
        return response()->noContent();
    }

    // POST /api/ejercicios/{id}/calificar
    public function calificar(Request $request, Ejercicio $ejercicio)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'puntos' => 'required|integer|min:1|max:5',
        ]);

        $ejercicio->calificaciones()->updateOrCreate(
            ['user_id' => $data['user_id']],
            ['puntos' => $data['puntos']]
        );

        return response()->json([
            'promedio' => $ejercicio->calificaciones()->avg('puntos')
        ]);
    }
}