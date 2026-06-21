<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Suscripcion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            \App\Models\SecurityLog::create([
                'user_id' => $user->id,
                'event_type' => 'login_success',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => ['method' => 'manual_login', 'email' => $request->email],
            ]);

            $userWithRoles = User::with('roles')->find($user->id);

            if ($userWithRoles->roles->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->isNotEmpty()) {
                return redirect()->route('administrador.dashboard');
            }

            $tieneSuscripcionActiva = Suscripcion::where('usuario_id', $user->id)
                ->where('estado', 'activa')
                ->where('fecha_fin', '>', now())
                ->exists();

            if ($tieneSuscripcionActiva) {
                return redirect()->route('clientes.dashboard');
            }

            return redirect()->route('clientes.home');
        }

        $failedUser = User::where('email', $request->email)->first();
        \App\Models\SecurityLog::create([
            'user_id' => $failedUser ? $failedUser->id : null,
            'event_type' => 'login_failed',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'details' => ['method' => 'manual_login', 'email' => $request->email],
        ]);

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        $rolCliente = Role::where('nombre_rol', 'Cliente')->first();
        if ($rolCliente) {
            RoleUser::firstOrCreate([
                'role_id' => $rolCliente->id,
                'user_id' => $user->id,
            ]);
        }

        return redirect()->route('clientes.home');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}