<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validación
        $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'usuario' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        // Crear usuario
        $user = User::create([
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'usuario' => $request->usuario,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => 'usuario', // opcional
        ]);

        // 🔹 Guardar medidas opcionales solo si vienen
        if (!empty($request->medidas)) {
            foreach ($request->medidas as $key => $value) {
                if ($value) {
                    $user->medidasCorporales()->create([
                        $key => $value
                    ]);
                }
            }
        }

        // Crear token JWT
        $token = Auth::guard('api')->login($user);

        return response()
            ->json(['message' => 'Login exitoso'])
            ->cookie('token', $token, 60, null, null, false, true); // HttpOnly = true
            }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'usuario';
        $credentials = [$field => $request->login, 'password' => $request->password];

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'Credenciales incorrectas'], 401);
        }

        // Usuario autenticado
        $user = Auth::guard('api')->user();

        // Token con rol
        $customClaims = ['role' => $user->rol];
        $tokenWithRole = \Tymon\JWTAuth\Facades\JWTAuth::claims($customClaims)->fromUser($user);

        return response()->json([
            'token' => $tokenWithRole,
            'user' => $user
        ]);
    }

    public function me()
    {
        return response()->json(Auth::guard('api')->user());
    }

    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json([
            'message' => 'Sesión cerrada'
        ]);
    }
}