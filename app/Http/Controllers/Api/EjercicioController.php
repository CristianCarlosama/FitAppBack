<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; 
use App\Models\Ejercicio;
use Illuminate\Http\Request;

class EjercicioController extends Controller
{
    private function checkAdminOrDev()
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'dev'])) {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }
    }

    // GET /api/ejercicios
    public function index()
    {
        return Ejercicio::all();
    }

    // POST /api/ejercicios
    public function store(Request $request)
    {
        $this->checkAdminOrDev(); // 🔒 solo admin/dev

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
        $this->checkAdminOrDev(); // 🔒 solo admin/dev

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
        $this->checkAdminOrDev(); // 🔒 solo admin/dev

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