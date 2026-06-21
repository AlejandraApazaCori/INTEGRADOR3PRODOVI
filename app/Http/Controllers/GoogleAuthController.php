<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $guzzleClient = new \GuzzleHttp\Client([
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                ],
            ]);

            $socialite = Socialite::driver('google')->setHttpClient($guzzleClient);
            $googleUser = $socialite->user();

            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'password' => bcrypt(Str::random(16)),
                    'email_verified_at' => now(),
                    'google_id' => $googleUser->getId(),
                ]
            );

            if (! $user->roles()->exists()) {
                $rolCliente = Role::where('nombre_rol', 'Cliente')->first();
                if ($rolCliente) {
                    RoleUser::updateOrCreate(
                        ['user_id' => $user->id, 'role_id' => $rolCliente->id],
                        []
                    );
                }
            }

            Auth::login($user);

            \App\Models\SecurityLog::create([
                'user_id' => $user->id,
                'event_type' => 'login_success',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'details' => ['method' => 'google_oauth', 'email' => $user->email],
            ]);

            $user = User::with('roles')->find(Auth::id());
            $userRole = $user?->roles->first();

            if ($userRole && in_array($userRole->nombre_rol, ['Super Administrador', 'Administrador'], true)) {
                return redirect()->route('administrador.dashboard');
            }

            return redirect()->route('clientes.home');
        } catch (Throwable $e) {
            return redirect('/')->with('error', 'Google authentication failed: ' . $e->getMessage());
        }
    }
}