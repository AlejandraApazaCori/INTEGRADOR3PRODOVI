<?php

namespace App\Http\Controllers;

use Database\Seeders\StaffUsersSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Throwable;

class MantenimientoWebController extends Controller
{
    private const FORMAT_CONFIRMATION = 'FORMATEAR PRODOVI';

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
                'formatPending' => File::exists($this->formatMarkerPath()),
                'formatConfirmation' => self::FORMAT_CONFIRMATION,
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

    public function formatDatabase(Request $request): RedirectResponse
    {
        if (! hash_equals(self::FORMAT_CONFIRMATION, (string) $request->input('confirmation'))) {
            return redirect()->route('mantenimiento.web.index')->with('format_result', [
                'success' => false,
                'message' => 'La frase de confirmación no coincide. No se modificó la base de datos.',
            ]);
        }

        $lockDirectory = storage_path('framework/cache');
        File::ensureDirectoryExists($lockDirectory);
        $lockHandle = fopen($lockDirectory.DIRECTORY_SEPARATOR.'mantenimiento-web-format.lock', 'c');

        if ($lockHandle === false || ! flock($lockHandle, LOCK_EX | LOCK_NB)) {
            if (is_resource($lockHandle)) {
                fclose($lockHandle);
            }

            return redirect()->route('mantenimiento.web.index')->with('format_result', [
                'success' => false,
                'message' => 'El formateo ya se está ejecutando. Espera a que termine.',
            ]);
        }

        try {
            $exitCode = Artisan::call('migrate:fresh', ['--force' => true]);
            $output = trim(Artisan::output());

            if ($exitCode !== 0) {
                return redirect()->route('mantenimiento.web.index')->with('format_result', [
                    'success' => false,
                    'message' => 'No se pudo completar el formateo.',
                    'output' => $output,
                ]);
            }

            File::ensureDirectoryExists(dirname($this->formatMarkerPath()));
            File::put($this->formatMarkerPath(), now()->toIso8601String());

            Log::critical('La base de datos fue formateada desde mantenimiento web.', [
                'ip' => $request->ip(),
            ]);

            return redirect()->route('mantenimiento.web.index')->with('format_result', [
                'success' => true,
                'message' => 'La base de datos quedó vacía, las tablas fueron recreadas y los IDs volverán a comenzar desde 1. Ejecuta ahora el paso 2.',
                'output' => $output,
            ]);
        } catch (Throwable $exception) {
            Log::critical('Falló el formateo de la base de datos desde mantenimiento web.', [
                'ip' => $request->ip(),
                'message' => $exception->getMessage(),
            ]);

            return redirect()->route('mantenimiento.web.index')->with('format_result', [
                'success' => false,
                'message' => 'Ocurrió un error durante el formateo: '.$exception->getMessage(),
            ]);
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    public function seedInitialAdmin(Request $request): RedirectResponse
    {
        if (! File::exists($this->formatMarkerPath())) {
            return redirect()->route('mantenimiento.web.index')->with('seed_result', [
                'success' => false,
                'message' => 'Primero debes completar el paso 1: Formatear página.',
            ]);
        }

        try {
            $exitCode = Artisan::call('db:seed', ['--force' => true]);
            $output = trim(Artisan::output());

            if ($exitCode !== 0) {
                return redirect()->route('mantenimiento.web.index')->with('seed_result', [
                    'success' => false,
                    'message' => 'El seeder no terminó correctamente.',
                    'output' => $output,
                ]);
            }

            File::delete($this->formatMarkerPath());

            Log::notice('Seeder inicial ejecutado después del formateo web.', [
                'ip' => $request->ip(),
            ]);

            return redirect()->route('mantenimiento.web.index')
                ->with('seed_result', [
                    'success' => true,
                    'message' => 'Roles, permisos, planes, cuestionarios y administrador inicial creados correctamente.',
                    'output' => $output,
                ])
                ->with('initial_admin_credentials', [
                    'email' => 'administrador_prodovi@gmail.com',
                    'password' => 'adminstradorProdovi123456789',
                ]);
        } catch (Throwable $exception) {
            Log::error('Falló el seeder inicial desde mantenimiento web.', [
                'ip' => $request->ip(),
                'message' => $exception->getMessage(),
            ]);

            return redirect()->route('mantenimiento.web.index')->with('seed_result', [
                'success' => false,
                'message' => 'Ocurrió un error al crear los datos iniciales: '.$exception->getMessage(),
            ]);
        }
    }

    public function seedStaffUsers(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'staff_password' => ['required', 'string', 'min:12', 'max:255', 'confirmed'],
        ]);

        $lockDirectory = storage_path('framework/cache');
        File::ensureDirectoryExists($lockDirectory);
        $lockHandle = fopen($lockDirectory.DIRECTORY_SEPARATOR.'mantenimiento-web-staff-seeder.lock', 'c');

        if ($lockHandle === false || ! flock($lockHandle, LOCK_EX | LOCK_NB)) {
            if (is_resource($lockHandle)) {
                fclose($lockHandle);
            }

            return redirect()->route('mantenimiento.web.index')->with('staff_seed_result', [
                'success' => false,
                'message' => 'El seeder del equipo ya se está ejecutando. Espera a que termine.',
            ]);
        }

        try {
            config(['seeding.staff_password' => $validated['staff_password']]);

            $exitCode = Artisan::call('db:seed', [
                '--class' => StaffUsersSeeder::class,
                '--force' => true,
            ]);
            $output = trim(Artisan::output());

            Log::notice('Seeder del equipo ejecutado desde mantenimiento web.', [
                'exit_code' => $exitCode,
                'ip' => $request->ip(),
            ]);

            if ($exitCode !== 0) {
                return redirect()->route('mantenimiento.web.index')->with('staff_seed_result', [
                    'success' => false,
                    'message' => 'El seeder del equipo no terminó correctamente.',
                    'output' => $output,
                ]);
            }

            return redirect()->route('mantenimiento.web.index')
                ->with('staff_seed_result', [
                    'success' => true,
                    'message' => 'Se crearon o actualizaron 5 Community Managers, 12 Diseñadores y 1 Administrador.',
                    'output' => $output,
                ])
                ->with('staff_credentials', [
                    'groups' => StaffUsersSeeder::accountGroups(),
                    'password' => $validated['staff_password'],
                ]);
        } catch (Throwable $exception) {
            Log::error('Falló el seeder del equipo desde mantenimiento web.', [
                'ip' => $request->ip(),
                'message' => $exception->getMessage(),
            ]);

            return redirect()->route('mantenimiento.web.index')->with('staff_seed_result', [
                'success' => false,
                'message' => 'Ocurrió un error al crear el equipo: '.$exception->getMessage(),
            ]);
        } finally {
            config(['seeding.staff_password' => null]);
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    private function formatMarkerPath(): string
    {
        return storage_path('app/private/mantenimiento-format-pending');
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
            if ($operation === 'migrate') {
                $this->prepareSocialAccountsMigration();
            }

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

    /**
     * Evita el error 1553 de MySQL cuando el indice unico anterior tambien
     * esta siendo utilizado como soporte de la clave foranea de user_id.
     */
    private function prepareSocialAccountsMigration(): void
    {
        if (! Schema::hasTable('social_accounts')
            || ! Schema::hasColumn('social_accounts', 'user_id')
            || Schema::hasColumn('social_accounts', 'empresa_id')
            || ! Schema::hasIndex('social_accounts', 'social_accounts_user_id_provider_unique')
            || Schema::hasIndex('social_accounts', 'social_accounts_user_id_lookup_index')) {
            return;
        }

        Schema::table('social_accounts', function (Blueprint $table) {
            $table->index('user_id', 'social_accounts_user_id_lookup_index');
        });

        Log::notice('Se preparo el indice auxiliar requerido para migrar social_accounts.');
    }
}
