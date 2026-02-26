<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; 
use App\Models\Ejercicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EjercicioController extends Controller
{
    private function checkAdminOrDev()
    {
        $user = auth('api')->user();

        if (!$user) {
            abort(403, 'No autenticado');
        }

        // Mantenemos tu lógica de roles intacta
        if (!in_array($user->rol, ['admin', 'dev', 'Dev'])) { 
            abort(403, 'Rol detectado: "' . $user->rol . '". Necesitas admin o dev.');
        }
    }

    public function index()
    {
        // Traemos los ejercicios con sus músculos asociados
        return Ejercicio::with('musculos')->get();
    }

    public function store(Request $request)
    {
        $this->checkAdminOrDev(); 

        $data = $request->validate([
            'rutina_id' => 'nullable|exists:rutinas,id',
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
            'clase' => 'required|string', // Se mantiene para compatibilidad o backup
            'series' => 'nullable|integer',
            'repeticiones' => 'nullable|integer',
            'descanso' => 'nullable|integer',
            'video_url' => 'nullable|url',
            'foto_1' => 'nullable|url',
            'foto_2' => 'nullable|url',
            'foto_3' => 'nullable|url',

            // Nuevas validaciones para la relación robusta
            'musculo_principal_id' => 'required|exists:musculos,id',
            'secundarios'          => 'nullable|array',
            'secundarios.*.id'     => 'exists:musculos,id',
            'secundarios.*.intensidad' => 'in:Alto,Medio,Bajo',
        ]);

        return DB::transaction(function () use ($data) {
            // Creamos el ejercicio con todos tus campos originales
            $ejercicio = Ejercicio::create($data);

            // 1. Vinculamos el músculo principal
            $ejercicio->musculos()->attach($data['musculo_principal_id'], [
                'es_principal' => true,
                'intensidad'   => 'Alto'
            ]);

            // 2. Vinculamos los secundarios si vienen en el request
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
        // Cargamos los músculos para que React los vea
        return response()->json($ejercicio->load('musculos'));
    }

    public function update(Request $request, Ejercicio $ejercicio)
    {
        $this->checkAdminOrDev(); 

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
            
            // Opcionales en el update
            'musculo_principal_id' => 'sometimes|exists:musculos,id',
            'secundarios'          => 'nullable|array',
        ]);

        return DB::transaction(function () use ($data, $ejercicio) {
            $ejercicio->update($data);

            // Si se enviaron datos de músculos, actualizamos la tabla pivote
            if (isset($data['musculo_principal_id']) || isset($data['secundarios'])) {
                $syncData = [];

                // Mantenemos el principal actual o el nuevo que venga
                $pId = $data['musculo_principal_id'] ?? $ejercicio->musculos()->where('es_principal', true)->first()?->id;
                
                if ($pId) {
                    $syncData[$pId] = ['es_principal' => true, 'intensidad' => 'Alto'];
                }

                // Agregamos los secundarios
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