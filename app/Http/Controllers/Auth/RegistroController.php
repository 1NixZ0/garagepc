<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Alta de usuarios (CU-01).
 */
class RegistroController extends Controller
{
    public function create(): View
    {
        return view('auth.registro');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'name.required' => 'Necesitamos un nombre para identificarte.',
            'email.unique' => 'Ya hay una cuenta registrada con ese correo.',
            'email.lowercase' => 'Escribi el correo en minusculas.',
            'password.confirmed' => 'Las dos contrasenas no coinciden.',
        ]);

        // El cast 'hashed' del modelo User se encarga de no guardar la contrasena en claro.
        $usuario = User::create($datos);

        event(new Registered($usuario));

        Auth::login($usuario);

        $request->session()->regenerate();

        return redirect()->route('panel');
    }
}
