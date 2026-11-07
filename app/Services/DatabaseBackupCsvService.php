<?php

namespace App\Services;

use App\Models\DatabaseBackup;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class DatabaseBackupCsvService
{
    public function export(DatabaseBackup $backup): string
    {
        $disk = Storage::disk('local');
        $archivePath = preg_replace('/\.sql$/i', '-csv.zip', $backup->path);
        if ($archivePath === $backup->path) {
            throw new RuntimeException('El respaldo no tiene un nombre SQL válido.');
        }
        if ($disk->exists($archivePath)) {
            return $archivePath;
        }

        $temporaryDirectory = 'database-backups/csv-' . uniqid('', true);
        $temporaryArchive = $temporaryDirectory . '.zip.tmp';
        $disk->makeDirectory($temporaryDirectory);

        try {
            $this->convert($disk->path($backup->path), $disk->path($temporaryArchive), $temporaryDirectory);
            if (! $disk->move($temporaryArchive, $archivePath)) {
                throw new RuntimeException('No se pudo guardar el archivo CSV.');
            }
        } finally {
            $disk->delete($temporaryArchive);
            $disk->deleteDirectory($temporaryDirectory);
        }

        return $archivePath;
    }

    private function convert(string $sqlPath, string $zipPath, string $temporaryDirectory): void
    {
        $disk = Storage::disk('local');
        $input = fopen($sqlPath, 'rb');
        if (! $input) {
            throw new RuntimeException('No se pudo abrir el respaldo SQL.');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            fclose($input);
            throw new RuntimeException('No se pudo crear el archivo ZIP de CSV.');
        }
        $zipOpen = true;

        $currentTable = null;
        $activeTable = null;
        $columns = [];
        $files = [];
        $output = null;

        try {
            while (($line = fgets($input)) !== false) {
                if (preg_match('/^CREATE TABLE `([^`]+)` \(/', $line, $match)) {
                    if ($output) fclose($output);
                    $output = null;
                    $currentTable = $match[1];
                    $activeTable = null;
                    $columns = [];
                    continue;
                }

                if ($currentTable !== null) {
                    if (preg_match('/^  `([^`]+)` /', $line, $match)) {
                        $columns[] = $match[1];
                    } elseif (preg_match('/^\)\s/', $line)) {
                        if ($columns === []) {
                            throw new RuntimeException("La tabla {$currentTable} no contiene columnas.");
                        }
                        $filename = sprintf('%03d-%s.csv', count($files) + 1, preg_replace('/[^A-Za-z0-9_-]+/', '_', $currentTable));
                        $filePath = $temporaryDirectory . '/' . $filename;
                        $output = fopen($disk->path($filePath), 'wb');
                        if (! $output) {
                            throw new RuntimeException('No se pudo crear un CSV de tabla.');
                        }
                        fwrite($output, "\xEF\xBB\xBF");
                        fputcsv($output, array_map(fn ($value) => $this->csvValue($value), $columns), ',', '"', '');
                        $files[] = [$filePath, $filename];
                        $activeTable = $currentTable;
                        $currentTable = null;
                    }
                    continue;
                }

                if (! preg_match('/^INSERT INTO `([^`]+)` VALUES (.*);\s*$/s', $line, $match)) {
                    continue;
                }
                if ($match[1] !== $activeTable || ! $output) {
                    throw new RuntimeException('El respaldo SQL contiene datos fuera de su tabla.');
                }
                foreach (app(DatabaseBackupSqlRows::class)->parse($match[2]) as $values) {
                    if (count($values) !== count($columns)) {
                        throw new RuntimeException("La tabla {$activeTable} tiene una fila con columnas incompletas.");
                    }
                    $values = array_map(fn ($value) => $this->csvValue($value), $values);
                    if (fputcsv($output, $values, ',', '"', '') === false) {
                        throw new RuntimeException('No se pudo escribir una fila CSV.');
                    }
                }
            }

            if (! feof($input) || $files === []) {
                throw new RuntimeException('El respaldo SQL no se pudo leer o no contiene tablas.');
            }
            if ($output) fclose($output);
            $output = null;

            foreach ($files as [$filePath, $filename]) {
                if (! $zip->addFile($disk->path($filePath), $filename)) {
                    throw new RuntimeException('No se pudo incluir una tabla en el ZIP.');
                }
            }
            $closed = $zip->close();
            $zipOpen = false;
            if (! $closed) {
                throw new RuntimeException('No se pudo finalizar el ZIP de CSV.');
            }
        } finally {
            if ($output) fclose($output);
            fclose($input);
            if ($zipOpen) $zip->close();
        }
    }

    private function csvValue(?string $value): string
    {
        if ($value === null) return '';
        $value = mb_scrub($value, 'UTF-8');

        // Evita que una hoja de cálculo ejecute fórmulas al abrir el CSV.
        if (! is_numeric($value) && preg_match('/^[\x00-\x20]*[=+@-]/', $value)) {
            return "'" . $value;
        }

        return $value;
    }
}
