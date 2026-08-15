<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MantenimientoWebController extends Controller
{
    private const OPERATIONS = [
        'migrate' => [
            'command' => 'migrate',
            'parameters' => ['--force' => true],
            'label' => 'php artisan migrate',
        ],
        'storage-link' => [
            'command' => 'storage:link',
            'parameters' => [],
            'label' => 'php artisan storage:link',
        ],
    ];

    public function index(): Response
    {
        return response()
            ->view('maintenance.ejecutar-comandos', [
                'storageLinkExists' => File::exists(public_path('storage')),
                'mailConfiguration' => [
                    'mailer' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'scheme' => config('mail.mailers.smtp.scheme') ?: 'automático (STARTTLS)',
                    'usernameConfigured' => filled(config('mail.mailers.smtp.username')),
                    'passwordConfigured' => filled(config('mail.mailers.smtp.password')),
                    'from' => config('mail.from.address'),
                    'configurationCached' => app()->configurationIsCached(),
                ],
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function testMail(): RedirectResponse
    {
        $recipient = config('mail.from.address');

        if (! is_string($recipient) || ! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return redirect()
                ->route('mantenimiento.web.index')
                ->with('mail_test_result', [
                    'success' => false,
                    'message' => 'MAIL_FROM_ADDRESS no contiene un correo válido.',
                ]);
        }

        try {
            Mail::raw(
                'La conexión SMTP de PRODOVI funciona correctamente.',
                fn ($message) => $message
                    ->to($recipient)
                    ->subject('Prueba de correo SMTP | PRODOVI')
            );

            Log::notice('Prueba SMTP completada desde mantenimiento web.', [
                'recipient' => $recipient,
                'ip' => request()->ip(),
            ]);

            return redirect()
                ->route('mantenimiento.web.index')
                ->with('mail_test_result', [
                    'success' => true,
                    'message' => "SMTP aceptó el correo de prueba enviado a {$recipient}. Revisa también la carpeta de spam.",
                ]);
        } catch (Throwable $exception) {
            Log::error('Falló la prueba SMTP desde mantenimiento web.', [
                'recipient' => $recipient,
                'ip' => request()->ip(),
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('mantenimiento.web.index')
                ->with('mail_test_result', [
                    'success' => false,
                    'message' => $this->mailFailureExplanation($exception),
                    'technical' => $exception->getMessage(),
                ]);
        }
    }

    private function mailFailureExplanation(Throwable $exception): string
    {
        $message = strtolower($exception->getMessage());

        if (str_contains($message, '535') || str_contains($message, 'authentication') || str_contains($message, 'authenticate')) {
            return 'Gmail rechazó las credenciales. MAIL_PASSWORD debe ser una contraseña de aplicación de Google, no la contraseña normal de la cuenta.';
        }

        if (str_contains($message, 'timed out') || str_contains($message, 'connection refused') || str_contains($message, 'could not connect')) {
            return 'El servidor no pudo conectarse con Gmail. Confirma con el hosting que permita conexiones SMTP salientes a smtp.gmail.com por el puerto 587.';
        }

        if (str_contains($message, 'certificate') || str_contains($message, 'crypto') || str_contains($message, 'tls')) {
            return 'Falló la conexión segura TLS con Gmail. El hosting debe tener OpenSSL y certificados raíz actualizados.';
        }

        if (str_contains($message, 'sender') || str_contains($message, 'from address')) {
            return 'Gmail rechazó el remitente. MAIL_FROM_ADDRESS debe coincidir con la cuenta configurada en MAIL_USERNAME.';
        }

        return 'SMTP devolvió un error. El detalle técnico aparece debajo para identificar la causa exacta.';
    }

    public function execute(string $operation): RedirectResponse
    {
        abort_unless(array_key_exists($operation, self::OPERATIONS), 404);

        $definition = self::OPERATIONS[$operation];
        $lockDirectory = storage_path('framework/cache');
        File::ensureDirectoryExists($lockDirectory);
        $lockHandle = fopen($lockDirectory.DIRECTORY_SEPARATOR."mantenimiento-web-{$operation}.lock", 'c');

        if ($lockHandle === false || ! flock($lockHandle, LOCK_EX | LOCK_NB)) {
            if (is_resource($lockHandle)) {
                fclose($lockHandle);
            }

            return redirect()
                ->route('mantenimiento.web.index')
                ->with('maintenance_result', [
                    'success' => false,
                    'command' => $definition['label'],
                    'output' => 'Este comando ya se está ejecutando. Espera a que termine antes de intentarlo nuevamente.',
                    'executed_at' => now()->format('d/m/Y H:i:s'),
                ]);
        }

        try {
            $exitCode = Artisan::call($definition['command'], $definition['parameters']);
            $output = trim(Artisan::output());

            Log::notice('Comando de mantenimiento ejecutado desde la ruta web protegida.', [
                'operation' => $operation,
                'exit_code' => $exitCode,
                'ip' => request()->ip(),
            ]);

            return redirect()
                ->route('mantenimiento.web.index')
                ->with('maintenance_result', [
                    'success' => $exitCode === 0,
                    'command' => $definition['label'],
                    'exit_code' => $exitCode,
                    'output' => $output !== '' ? $output : 'El comando terminó sin mensajes adicionales.',
                    'executed_at' => now()->format('d/m/Y H:i:s'),
                ]);
        } catch (Throwable $exception) {
            Log::error('Falló un comando de mantenimiento ejecutado desde la web.', [
                'operation' => $operation,
                'ip' => request()->ip(),
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('mantenimiento.web.index')
                ->with('maintenance_result', [
                    'success' => false,
                    'command' => $definition['label'],
                    'output' => 'Ocurrió un error: '.$exception->getMessage(),
                    'executed_at' => now()->format('d/m/Y H:i:s'),
                ]);
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }
}
