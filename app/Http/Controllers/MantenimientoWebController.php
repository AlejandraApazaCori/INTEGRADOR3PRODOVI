<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
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
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
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
