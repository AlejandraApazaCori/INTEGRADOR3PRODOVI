<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ValidTurnstile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secret = config('services.turnstile.secret');
        $allowedHostnames = config('services.turnstile.hostnames', []);
        $expectedAction = config('services.turnstile.action');

        if (! is_string($secret) || $secret === '' || $allowedHostnames === []) {
            Log::error('Turnstile no está configurado correctamente.');
            $fail('No pudimos verificar que seas una persona. Inténtalo nuevamente.');

            return;
        }

        try {
            $response = Http::asForm()
                ->acceptJson()
                ->timeout(8)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secret,
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]);

            $result = $response->json();
            $hostname = strtolower((string) data_get($result, 'hostname'));
            $action = (string) data_get($result, 'action');

            $isValid = $response->successful()
                && data_get($result, 'success') === true
                && in_array($hostname, $allowedHostnames, true)
                && hash_equals((string) $expectedAction, $action);

            if (! $isValid) {
                Log::warning('Turnstile rechazó una solicitud de contacto.', [
                    'hostname' => $hostname,
                    'action' => $action,
                    'error_codes' => data_get($result, 'error-codes', []),
                ]);

                $fail('No pudimos verificar que seas una persona. Completa nuevamente la verificación.');
            }
        } catch (Throwable $exception) {
            Log::error('No se pudo consultar la validación de Turnstile.', [
                'message' => $exception->getMessage(),
            ]);

            $fail('La verificación de seguridad no está disponible. Inténtalo nuevamente en unos minutos.');
        }
    }
}
