<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rutina;
use Illuminate\Http\Request;

class RutinaController extends Controller
{
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
                $rutina->es_mia = ($rutina->user_id === $userId);
                return $rutina;
            });
        return $rutinas;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
            'dificultad' => 'nullable|in:baja,media,alta',
            'duracion' => 'nullable|integer',
            'es_publica' => 'boolean',
            'ejercicios' => 'required|array|min:1',
            'ejercicios.*.id' => 'required|exists:ejercicios,id', // Validamos el ID dentro del objeto
            'ejercicios.*.series' => 'nullable|integer',
            'ejercicios.*.repeticiones' => 'nullable|integer',
            'ejercicios.*.descanso' => 'nullable|integer',
            'accesos' => 'sometimes|array',
            'accesos.*' => 'exists:users,id',
        ]);

        $rutina = Rutina::create([
            'user_id' => auth()->id(),
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'dificultad' => $data['dificultad'] ?? null,
            'duracion' => $data['duracion'] ?? null,
            'es_publica' => $data['es_publica'] ?? false,
        ]);

        $formatoPivote = [];
        foreach ($data['ejercicios'] as $ej) {
            $formatoPivote[$ej['id']] = [
                'series' => $ej['series'] ?? 0,
                'repeticiones' => $ej['repeticiones'] ?? 0,
                'descanso' => $ej['descanso'] ?? 60,
            ];
        }

        $rutina->ejercicios()->sync($formatoPivote);

        if (!empty($data['accesos'])) {
            $rutina->accesos()->sync($data['accesos']);
        }
        return response()->json($rutina->load('ejercicios'), 201);
    }

    // PUT /api/rutinas/{id}
    public function update(Request $request, Rutina $rutina)
    {
        $userId = auth()->id();

        if ($rutina->user_id !== $userId) {
            return response()->json(['message' => 'No tienes permiso'], 403);
        }
        $data = $request->validate([
            'nombre' => 'sometimes|string',
            'descripcion' => 'nullable|string',
            'dificultad' => 'nullable|in:baja,media,alta',
            'duracion' => 'nullable|integer',
            'es_publica' => 'boolean',
            'ejercicios' => 'sometimes|array|min:1',
            'ejercicios.*.id' => 'required_with:ejercicios|exists:ejercicios,id',
            'ejercicios.*.series' => 'nullable|integer',
            'ejercicios.*.repeticiones' => 'nullable|integer',
            'ejercicios.*.descanso' => 'nullable|integer',
            'accesos' => 'sometimes|array',
            'accesos.*' => 'exists:users,id',
        ]);

        $rutina->update($request->only(['nombre', 'descripcion', 'dificultad', 'duracion', 'es_publica']));

        if ($request->has('ejercicios')) {
            $formatoPivote = [];
            foreach ($data['ejercicios'] as $ej) {
                $formatoPivote[$ej['id']] = [
                    'series' => $ej['series'] ?? 0,
                    'repeticiones' => $ej['repeticiones'] ?? 0,
                    'descanso' => $ej['descanso'] ?? 60,
                ];
            }
            $rutina->ejercicios()->sync($formatoPivote);
        }
        if ($request->has('accesos')) {
            $rutina->accesos()->sync($data['accesos']);
        }
        return response()->json($rutina->load('ejercicios'));
    }

    public function destroy(Rutina $rutina)
    {
        if ($rutina->user_id !== auth()->id()) {
            return response()->json(['message' => 'No puedes borrar lo que no es tuyo'], 403);
        }
        $rutina->delete();
        return response()->noContent();
    }

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