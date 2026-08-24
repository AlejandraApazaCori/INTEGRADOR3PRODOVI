<?php

namespace App\Services;

use App\Models\DatabaseBackup;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use ZipArchive;

class DatabaseBackupService
{
    public function create(string $trigger = 'manual', ?int $userId = null): DatabaseBackup
    {
        return Cache::lock('database-backup-creation', 900)->block(5, function () use ($trigger, $userId) {
            $backup = DatabaseBackup::create([
                'status' => 'processing',
                'trigger' => $trigger,
                'started_at' => now(),
                'created_by' => $userId,
            ]);

            $targetPath = null;

            try {
                $disk = Storage::disk('local');
                $backupDirectory = trim((string) config('backup.backup.name', 'Laravel'), '/');
                $archivesBefore = collect($disk->allFiles($backupDirectory))->flip();

                $exitCode = Artisan::call('backup:run', [
                    '--only-db' => true,
                    '--disable-notifications' => true,
                ]);

                if ($exitCode !== 0) {
                    throw new RuntimeException(trim(Artisan::output()) ?: 'El comando de respaldo no pudo completarse.');
                }

                $archivePath = collect($disk->allFiles($backupDirectory))
                    ->filter(fn (string $path) => str_ends_with(strtolower($path), '.zip'))
                    ->reject(fn (string $path) => $archivesBefore->has($path))
                    ->sortByDesc(fn (string $path) => $disk->lastModified($path))
                    ->first();

                if (! $archivePath) {
                    throw new RuntimeException('No se encontró el archivo temporal generado por el respaldo.');
                }

                $databaseName = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) config('database.connections.' . config('database.default') . '.database', 'database'));
                $filename = 'respaldo-' . trim($databaseName, '-') . '-' . now()->format('Y-m-d_H-i-s-u') . '.sql';
                $targetPath = 'database-backups/' . $filename;
                $disk->makeDirectory('database-backups');

                $zip = new ZipArchive();
                if ($zip->open($disk->path($archivePath)) !== true) {
                    throw new RuntimeException('No se pudo abrir el respaldo temporal.');
                }

                $sqlEntry = null;
                for ($index = 0; $index < $zip->numFiles; $index++) {
                    $entry = $zip->getNameIndex($index);
                    if ($entry && str_ends_with(strtolower($entry), '.sql')) {
                        $sqlEntry = $entry;
                        break;
                    }
                }

                if (! $sqlEntry) {
                    $zip->close();
                    throw new RuntimeException('El respaldo generado no contiene un archivo SQL.');
                }

                $source = $zip->getStream($sqlEntry);
                $destination = fopen($disk->path($targetPath), 'wb');
                if (! is_resource($source) || ! is_resource($destination)) {
                    if (is_resource($source)) fclose($source);
                    if (is_resource($destination)) fclose($destination);
                    $zip->close();
                    throw new RuntimeException('No se pudo guardar el archivo SQL.');
                }

                stream_copy_to_stream($source, $destination);
                fclose($source);
                fclose($destination);
                $zip->close();

                $disk->delete($archivePath);

                $backup->update([
                    'filename' => $filename,
                    'disk' => 'local',
                    'path' => $targetPath,
                    'size_bytes' => $disk->size($targetPath),
                    'status' => 'completed',
                    'completed_at' => now(),
                    'error_message' => null,
                ]);
            } catch (Throwable $exception) {
                if ($targetPath) {
                    Storage::disk('local')->delete($targetPath);
                }

                $backup->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                    'error_message' => mb_substr($exception->getMessage(), 0, 2000),
                ]);

                report($exception);
            }

            return $backup->fresh();
        });
    }
}
