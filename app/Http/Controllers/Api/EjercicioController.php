<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; 
use App\Models\Ejercicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EjercicioController extends Controller
{
    private function checkAdminOrDev()
    {
        $user = auth('api')->user();

        if (!$user) {
            abort(403, 'No autenticado');
        }

        if (!in_array($user->rol, ['admin', 'dev', 'Dev'])) { 
            abort(403, 'Rol detectado: "' . $user->rol . '". Necesitas admin o dev.');
        }
    }

    public function index()
    {
        return Ejercicio::with('musculos')->get();
    }

    public function store(Request $request)
    {
        $this->checkAdminOrDev(); 

        // Decodificamos secundarios porque viajan como string JSON en FormData
        if ($request->has('secundarios')) {
            $request->merge(['secundarios' => json_decode($request->secundarios, true)]);
        }

        $data = $request->validate([
            'rutina_id' => 'nullable|exists:rutinas,id',
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
            'clase' => 'required|string',
            'series' => 'nullable|integer',
            'repeticiones' => 'nullable|integer',
            'descanso' => 'nullable|integer',
            'video_url' => 'nullable|string', // Quitamos |url por si mandas embeds
            'foto_1' => 'nullable|image|max:2048', // Cambiado de url a image
            'foto_2' => 'nullable|image|max:2048',
            'foto_3' => 'nullable|image|max:2048',
            'musculo_principal_id' => 'required|exists:musculos,id',
            'secundarios'          => 'nullable|array',
            'secundarios.*.id'     => 'exists:musculos,id',
            'secundarios.*.intensidad' => 'in:Alto,Medio,Bajo',
        ]);

        return DB::transaction(function () use ($request, $data) {
            // Procesamos archivos
            foreach (['foto_1', 'foto_2', 'foto_3'] as $foto) {
                if ($request->hasFile($foto)) {
                    $data[$foto] = $request->file($foto)->store('ejercicios', 'public');
                }
            }

            $ejercicio = Ejercicio::create($data);

            $ejercicio->musculos()->attach($data['musculo_principal_id'], [
                'es_principal' => true,
                'intensidad'   => 'Alto'
            ]);

            if (!empty($data['secundarios'])) {
                foreach ($data['secundarios'] as $sec) {
                    $ejercicio->musculos()->attach($sec['id'], [
                        'es_principal' => false,
                        'intensidad'   => $sec['intensidad'] ?? 'Medio'
                    ]);
                }
            }

            return response()->json($ejercicio->load('musculos'), 201);
        });
    }

    public function show(Ejercicio $ejercicio)
    {
        $ejercicio->promedio_calificacion = $ejercicio->calificaciones()->avg('puntos');
        return response()->json($ejercicio->load('musculos'));
    }

    public function update(Request $request, Ejercicio $ejercicio)
    {
        $this->checkAdminOrDev(); 

        // Decodificamos secundarios para el update
        if ($request->has('secundarios')) {
            $request->merge(['secundarios' => json_decode($request->secundarios, true)]);
        }

        $data = $request->validate([
            'nombre' => 'sometimes|string',
            'descripcion' => 'nullable|string',
            'clase' => 'sometimes|string',
            'series' => 'nullable|integer',
            'repeticiones' => 'nullable|integer',
            'descanso' => 'nullable|integer',
            'video_url' => 'nullable|string',
            'foto_1' => 'nullable|image|max:2048',
            'foto_2' => 'nullable|image|max:2048',
            'foto_3' => 'nullable|image|max:2048',
            'musculo_principal_id' => 'sometimes|exists:musculos,id',
            'secundarios'          => 'nullable|array',
        ]);

        return DB::transaction(function () use ($request, $data, $ejercicio) {
            // Procesamos archivos nuevos y borramos los viejos si prefieres (opcional)
            foreach (['foto_1', 'foto_2', 'foto_3'] as $foto) {
                if ($request->hasFile($foto)) {
                    // Si quieres borrar la imagen anterior descomenta esto:
                    // if($ejercicio->$foto) Storage::disk('public')->delete($ejercicio->$foto);
                    $data[$foto] = $request->file($foto)->store('ejercicios', 'public');
                }
            }

            $ejercicio->update($data);

            if (isset($data['musculo_principal_id']) || isset($data['secundarios'])) {
                $syncData = [];
                $pId = $data['musculo_principal_id'] ?? $ejercicio->musculos()->where('es_principal', true)->first()?->id;
                
                if ($pId) {
                    $syncData[$pId] = ['es_principal' => true, 'intensidad' => 'Alto'];
                }

                if (isset($data['secundarios'])) {
                    foreach ($data['secundarios'] as $sec) {
                        $syncData[$sec['id']] = [
                            'es_principal' => false, 
                            'intensidad' => $sec['intensidad']
                        ];
                    }
                }

                $ejercicio->musculos()->sync($syncData);
            }

            return response()->json($ejercicio->load('musculos'));
        });
    }

    public function destroy(Ejercicio $ejercicio)
    {
        $this->checkAdminOrDev(); 
        // Borrar archivos del disco al eliminar
        foreach(['foto_1', 'foto_2', 'foto_3'] as $f) {
            if($ejercicio->$f) Storage::disk('public')->delete($ejercicio->$f);
        }
        $ejercicio->delete();
        return response()->noContent();
    }

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