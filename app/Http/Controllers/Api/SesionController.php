<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Autenticación de la aplicación móvil (RF-MOV-01).
 *
 * Se usan las mismas credenciales de la aplicación web. El token de Sanctum
 * no expira por sí solo, lo que permite mantener la sesión activa entre usos
 * sin pedirle la contraseña al usuario cada vez que abre la aplicación.
 */
class SesionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'dispositivo' => ['required', 'string', 'max:120'],
        ]);

        $usuario = User::where('email', $datos['email'])->first();

        // Un solo mensaje para los dos casos: distinguirlos permitiría
        // averiguar qué correos están registrados.
        if ($usuario === null || ! Hash::check($datos['password'], $usuario->password)) {
            throw ValidationException::withMessages([
                'email' => ['Esas credenciales no coinciden con ninguna cuenta.'],
            ]);
        }

        // Un token por dispositivo: así se puede revocar uno sin cerrar los demás.
        $usuario->tokens()->where('name', $datos['dispositivo'])->delete();

        return response()->json([
            'token' => $usuario->createToken($datos['dispositivo'])->plainTextToken,
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->name,
                'email' => $usuario->email,
            ],
        ], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['mensaje' => 'Sesión cerrada.']);
    }

    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'id' => $request->user()->id,
            'nombre' => $request->user()->name,
            'email' => $request->user()->email,
        ]);
    }
}
