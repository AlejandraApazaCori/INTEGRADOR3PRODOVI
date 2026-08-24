<?php

namespace App\Console\Commands;

use App\Models\DatabaseBackupSetting;
use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class RunDueDatabaseBackup extends Command
{
    protected $signature = 'database-backups:run-due {--force : Ejecutar inmediatamente aunque todavía no corresponda}';

    protected $description = 'Genera el respaldo de base de datos que esté programado';

    public function handle(DatabaseBackupService $backupService): int
    {
        $setting = DatabaseBackupSetting::firstOrCreate(
            ['id' => 1],
            ['frequency' => 'daily', 'backup_time' => '01:00:00', 'enabled' => true]
        );

        if (! $setting->next_run_at) {
            $setting->forceFill(['next_run_at' => $setting->calculateNextRun()])->save();
        }

        if (! $this->option('force') && (! $setting->enabled || ! $setting->next_run_at || $setting->next_run_at->isFuture())) {
            return self::SUCCESS;
        }

        $backup = $backupService->create('scheduled');
        $setting->forceFill([
            'last_run_at' => now(),
            'next_run_at' => $setting->calculateNextRun(now()),
        ])->save();

        if ($backup->status !== 'completed') {
            $this->error($backup->error_message ?: 'El respaldo programado falló.');
            return self::FAILURE;
        }

        $this->info('Respaldo programado creado: ' . $backup->filename);
        return self::SUCCESS;
    }
}
