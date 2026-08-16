<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\BienvenidaRegistroGoogle;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\RegistroPendiente;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
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
            $googleUser = $this->getGoogleUser();

            $googleEmail = mb_strtolower($googleUser->getEmail());
            $user = User::where('email', $googleEmail)->first();
            $isNewGoogleRegistration = $user === null;

            if ($isNewGoogleRegistration) {
                $registroPendiente = RegistroPendiente::where('email', $googleEmail)->first();

                $user = User::create([
                    'email' => $googleEmail,
                    'name' => $registroPendiente?->name ?: $googleUser->getName(),
                    'phone' => $registroPendiente?->phone,
                    'password' => $registroPendiente?->password ?: bcrypt(Str::random(16)),
                    'google_id' => $googleUser->getId(),
                ]);

                $user->forceFill([
                    'email_verified_at' => now(),
                ])->save();

                $registroPendiente?->delete();
            } else {
                $user->forceFill([
                    'email_verified_at' => $user->email_verified_at ?: now(),
                    'google_id' => $googleUser->getId(),
                ])->save();
            }

            if (! $user->roles()->exists()) {
                $rolCliente = Role::where('nombre_rol', 'Cliente')->first();
                if ($rolCliente) {
                    RoleUser::updateOrCreate(
                        ['user_id' => $user->id, 'role_id' => $rolCliente->id],
                        []
                    );
                }
            }

            if ($isNewGoogleRegistration) {
                try {
                    Mail::to($user->email)->send(new BienvenidaRegistroGoogle($user));
                } catch (Throwable $mailException) {
                    Log::error('No se pudo enviar el correo de bienvenida del registro con Google.', [
                        'user_id' => $user->id,
                        'message' => $mailException->getMessage(),
                    ]);
                }
            }

            Auth::login($user, true);
            request()->session()->regenerate();
            request()->session()->forget('pending_registration_email');

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
            Log::error('Google authentication failed.', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'url' => request()->fullUrl(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('login')->with('error', 'No se pudo completar el ingreso con Google.');
        }
    }

    private function getGoogleUser()
    {
        $guzzleClient = new \GuzzleHttp\Client([
            'curl' => [
                CURLOPT_SSL_VERIFYPEER => false,
            ],
        ]);

        $driver = Socialite::driver('google')->setHttpClient($guzzleClient);

        try {
            return $driver->user();
        } catch (InvalidStateException $e) {
            Log::warning('Google OAuth state mismatch. Retrying stateless.', [
                'message' => $e->getMessage(),
                'url' => request()->fullUrl(),
                'ip' => request()->ip(),
            ]);

            return Socialite::driver('google')
                ->setHttpClient($guzzleClient)
                ->stateless()
                ->user();
        }
    }
}
