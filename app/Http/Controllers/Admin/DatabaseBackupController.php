<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DatabaseBackup;
use App\Models\DatabaseBackupSetting;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DatabaseBackupController extends Controller
{
    public function index()
    {
        $this->ensureAdministrator();

        $setting = DatabaseBackupSetting::firstOrCreate(
            ['id' => 1],
            [
                'frequency' => 'daily',
                'backup_time' => '01:00:00',
                'enabled' => true,
            ]
        );

        if ($setting->enabled && ! $setting->next_run_at) {
            $setting->forceFill(['next_run_at' => $setting->calculateNextRun()])->save();
        }

        $backups = DatabaseBackup::with('creator')->latest('started_at')->paginate(12);
        $latestBackup = DatabaseBackup::query()
            ->where('status', 'completed')
            ->latest('completed_at')
            ->first();

        return view('backups.index', compact('setting', 'backups', 'latestBackup'));
    }

    public function store(DatabaseBackupService $backupService)
    {
        $this->ensureAdministrator();

        try {
            $backup = $backupService->create('manual', auth()->id());
        } catch (Throwable $exception) {
            report($exception);
            return back()->with('error', 'Ya existe otro respaldo en proceso o no fue posible iniciar la operación.');
        }

        if ($backup->status !== 'completed') {
            return back()->with('error', 'No se pudo crear el respaldo: ' . ($backup->error_message ?: 'revisa la configuración del servidor.'));
        }

        return back()->with('success', 'Respaldo SQL creado correctamente.');
    }

    public function updateSchedule(Request $request)
    {
        $this->ensureAdministrator();

        $validated = $request->validate([
            'frequency' => ['required', 'in:daily,weekly,monthly'],
            'backup_time' => ['required', 'date_format:H:i'],
            'weekday' => ['nullable', 'required_if:frequency,weekly', 'integer', 'between:0,6'],
            'month_day' => ['nullable', 'required_if:frequency,monthly', 'integer', 'between:1,28'],
            'enabled' => ['nullable', 'boolean'],
        ]);

        $setting = DatabaseBackupSetting::firstOrNew(['id' => 1]);
        $setting->fill([
            'frequency' => $validated['frequency'],
            'backup_time' => $validated['backup_time'],
            'weekday' => $validated['frequency'] === 'weekly' ? $validated['weekday'] : null,
            'month_day' => $validated['frequency'] === 'monthly' ? $validated['month_day'] : null,
            'enabled' => $request->boolean('enabled'),
            'updated_by' => auth()->id(),
        ]);
        $setting->next_run_at = $setting->calculateNextRun();
        $setting->save();

        return back()->with('success', $setting->enabled
            ? 'Programación guardada. El próximo respaldo ya fue calculado.'
            : 'Los respaldos automáticos fueron desactivados.');
    }

    public function download(DatabaseBackup $backup)
    {
        $this->ensureAdministrator();

        abort_unless($backup->status === 'completed' && $backup->disk === 'local', 404);
        abort_unless($backup->path && str_starts_with($backup->path, 'database-backups/'), 404);
        abort_unless(Storage::disk('local')->exists($backup->path), 404, 'El archivo de respaldo ya no existe.');

        return Storage::disk('local')->download($backup->path, $backup->filename);
    }

    public function downloadLatest()
    {
        $this->ensureAdministrator();

        $backup = DatabaseBackup::query()
            ->where('status', 'completed')
            ->latest('completed_at')
            ->firstOrFail();

        return $this->download($backup);
    }

    private function ensureAdministrator(): void
    {
        abort_unless(
            auth()->check() && auth()->user()->hasAnyRole(['Super Administrador', 'Administrador']),
            403
        );
    }
}
