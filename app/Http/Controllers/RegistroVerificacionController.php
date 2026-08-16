<?php

namespace App\Http\Controllers;

use App\Mail\VerificarRegistroManual;
use App\Models\RegistroPendiente;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class RegistroVerificacionController extends Controller
{
    public function notice(Request $request): View|RedirectResponse
    {
        $email = $request->session()->get('pending_registration_email');

        if (! $email || ! RegistroPendiente::where('email', $email)->exists()) {
            return redirect(url('/login').'#register');
        }

        return view('clientes.verificacion.index', [
            'email' => $email,
        ]);
    }

    public function verify(Request $request, string $token): RedirectResponse
    {
        $tokenHash = hash('sha256', $token);

        try {
            $user = DB::transaction(function () use ($tokenHash) {
                $registro = RegistroPendiente::where('verification_token_hash', $tokenHash)
                    ->lockForUpdate()
                    ->first();

                if (! $registro) {
                    return null;
                }

                if ($registro->verification_expires_at->isPast()) {
                    return false;
                }

                if (User::withTrashed()->where('email', $registro->email)->exists()) {
                    $registro->delete();

                    return null;
                }

                $user = User::create([
                    'name' => $registro->name,
                    'email' => $registro->email,
                    'phone' => $registro->phone,
                    'password' => $registro->password,
                ]);

                $user->forceFill(['email_verified_at' => now()])->save();

                $rolCliente = Role::where('nombre_rol', 'Cliente')->first();
                if ($rolCliente) {
                    RoleUser::firstOrCreate([
                        'role_id' => $rolCliente->id,
                        'user_id' => $user->id,
                    ]);
                }

                $registro->delete();

                return $user;
            });
        } catch (Throwable $exception) {
            Log::error('No se pudo completar la verificación del registro manual.', [
                'message' => $exception->getMessage(),
            ]);

            return redirect()->route('registro.verificacion.aviso')
                ->with('error', 'No pudimos activar tu cuenta. Inténtalo nuevamente.');
        }

        if ($user === false) {
            return redirect()->route('registro.verificacion.aviso')
                ->with('error', 'El enlace de verificación venció. Solicita uno nuevo.');
        }

        if (! $user instanceof User) {
            return redirect(url('/login').'#register')
                ->with('error', 'Este enlace de verificación ya no es válido.');
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        $request->session()->forget('pending_registration_email');

        return redirect()->route('clientes.home')
            ->with('success', 'Correo verificado. Tu cuenta ya está activa y puedes elegir un plan.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $email = $request->session()->get('pending_registration_email');
        $registro = $email ? RegistroPendiente::where('email', $email)->first() : null;

        if (! $registro) {
            return redirect(url('/login').'#register');
        }

        $token = Str::random(64);
        $registro->forceFill([
            'verification_token_hash' => hash('sha256', $token),
            'verification_expires_at' => now()->addHour(),
        ])->save();

        try {
            Mail::to($registro->email)->send(new VerificarRegistroManual(
                $registro,
                route('registro.verificacion.confirmar', ['token' => $token]),
            ));
        } catch (Throwable $exception) {
            Log::error('No se pudo reenviar el correo de verificación.', [
                'registro_pendiente_id' => $registro->id,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->back()->with('error', 'No pudimos reenviar el correo en este momento.');
        }

        return redirect()->back()->with('success', 'Enviamos un nuevo enlace de verificación.');
    }
}
