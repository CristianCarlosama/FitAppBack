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
        $userId = auth()->id();

        $rutinas = Rutina::with('ejercicios')
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhere('es_publica', true)
                    ->orWhereHas('accesos', function($q) use ($userId) {
                        $q->where('user_id', $userId);
                    });
            })
            ->get()
            ->map(function ($rutina) use ($userId) {
                // Agregamos la propiedad sobre la marcha
                $rutina->es_mia = ($rutina->user_id === $userId);
                return $rutina;
            });

        return $rutinas;
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

        $rutina->ejercicios()->sync($data['ejercicios']);

        // sincronizar accesos
        if (!empty($data['accesos'])) {
            $rutina->accesos()->sync($data['accesos']);
        }
        return response()->json($rutina->load('ejercicios'), 201);
    }

    public function show(Rutina $rutina)
    {
        $userId = auth()->id();

        // si no es pública, y no soy el dueño, y no tengo acceso
        if (!$rutina->es_publica && $rutina->user_id !== $userId 
            && !$rutina->accesos()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'No tienes permiso'], 403);
        }

        $rutina->load('ejercicios');
        $rutina->es_mia = ($rutina->user_id === auth()->id()); // Inyectar propiedad
        $rutina->promedio_calificacion = $rutina->calificaciones()->avg('puntos');

        return response()->json($rutina);
    }

    // PUT /api/rutinas/{id}
    public function update(Request $request, Rutina $rutina)
    {
        $userId = auth()->id();

        // 🚨 Solo el dueño puede editar
        if ($rutina->user_id !== $userId) {
            return response()->json([
                'message' => 'No tienes permiso para editar esta rutina.'
            ], 403);
        }

        // --- resto de la actualización ---
        if ($request->has('ejercicios')) {
            $ejerciciosLimpios = collect($request->input('ejercicios'))->map(function($ej) {
                return is_array($ej) ? ($ej['id'] ?? null) : $ej;
            })->filter()->values()->toArray();

            $request->merge(['ejercicios' => $ejerciciosLimpios]);
        }

        $data = $request->validate([
            'nombre' => 'sometimes|string',
            'descripcion' => 'nullable|string',
            'dificultad' => 'nullable|in:baja,media,alta',
            'duracion' => 'nullable|integer',
            'es_publica' => 'boolean',
            'ejercicios' => 'sometimes|array|min:1',
            'ejercicios.*' => 'exists:ejercicios,id', 
            'accesos' => 'sometimes|array', // si agregaste acceso
            'accesos.*' => 'exists:users,id',
        ]);

        $rutina->update($request->only(['nombre', 'descripcion', 'dificultad', 'duracion', 'es_publica']));

        if ($request->has('ejercicios')) {
            $rutina->ejercicios()->sync($request->input('ejercicios'));
        }

        if ($request->has('accesos')) {
            $rutina->accesos()->sync($request->input('accesos'));
        }

        return response()->json($rutina->load('ejercicios'));
    }

    // DELETE /api/rutinas/{id}
    public function destroy(Rutina $rutina)
    {
        if ($rutina->user_id !== auth()->id()) {
            return response()->json(['message' => 'No puedes borrar lo que no es tuyo'], 403);
        }

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