<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MedidaCorporal;
use Illuminate\Support\Facades\Auth;

class MedidaController extends Controller
{
    public function storeOrUpdate(Request $request)
    {
        $user = Auth::guard('api')->user();

        $data = $request->only(['peso','pecho','cintura','brazo','pierna']);

        // Si el usuario no manda ninguna medida, no hacemos nada
        if (empty(array_filter($data, fn($v) => $v !== null))) {
            return response()->json(['message' => 'No se enviaron medidas'], 200);
        }

        $medida = $user->medidasCorporales()->create($data);

        return response()->json([
            'message' => 'Medidas guardadas',
            'medida' => $medida
        ]);
    }
}