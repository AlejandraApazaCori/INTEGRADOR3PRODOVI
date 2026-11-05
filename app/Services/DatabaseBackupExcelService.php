<?php

namespace App\Services;

use App\Models\DatabaseBackup;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

class DatabaseBackupExcelService
{
    public function export(DatabaseBackup $backup): string
    {
        $disk = Storage::disk('local');
        $path = preg_replace('/\.sql$/i', '.xlsx', $backup->path);

        if ($path === $backup->path) {
            throw new RuntimeException('El respaldo no tiene un nombre SQL válido.');
        }

        if ($disk->exists($path)) {
            return $path;
        }

        $temporaryPath = $path . '.' . uniqid('', true) . '.tmp';

        try {
            $this->convert($disk->path($backup->path), $disk->path($temporaryPath));
            if (! $disk->move($temporaryPath, $path)) {
                throw new RuntimeException('No se pudo guardar el archivo Excel.');
            }
        } finally {
            $disk->delete($temporaryPath);
        }

        return $path;
    }

    private function convert(string $sqlPath, string $excelPath): void
    {
        $input = fopen($sqlPath, 'rb');
        if (! $input) {
            throw new RuntimeException('No se pudo abrir el respaldo SQL.');
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $sheets = [];
        $columns = [];
        $rows = [];
        $currentTable = null;

        try {
            while (($line = fgets($input)) !== false) {
                if (preg_match('/^CREATE TABLE `([^`]+)` \(/', $line, $match)) {
                    $currentTable = $match[1];
                    $title = $this->sheetTitle($currentTable, $sheets);
                    $sheets[$currentTable] = $spreadsheet->createSheet();
                    $sheets[$currentTable]->setTitle($title);
                    $columns[$currentTable] = [];
                    $rows[$currentTable] = 1;
                    continue;
                }

                if ($currentTable !== null) {
                    if (preg_match('/^  `([^`]+)` /', $line, $match)) {
                        $columns[$currentTable][] = $match[1];
                        $this->writeCell($sheets[$currentTable], count($columns[$currentTable]), 1, $match[1]);
                    } elseif (preg_match('/^\)\s/', $line)) {
                        $currentTable = null;
                    }
                    continue;
                }

                if (! preg_match('/^INSERT INTO `([^`]+)` VALUES (.*);\s*$/s', $line, $match)) {
                    continue;
                }

                $table = $match[1];
                if (! isset($sheets[$table])) {
                    throw new RuntimeException("La tabla {$table} no tiene definición en el respaldo SQL.");
                }

                foreach ($this->parseRows($match[2]) as $values) {
                    if (count($values) !== count($columns[$table])) {
                        throw new RuntimeException("La tabla {$table} tiene una fila con columnas incompletas.");
                    }
                    if (++$rows[$table] > 1048576) {
                        throw new RuntimeException("La tabla {$table} excede el límite de filas de Excel.");
                    }
                    foreach ($values as $index => $value) {
                        if ($value !== null) {
                            $this->writeCell($sheets[$table], $index + 1, $rows[$table], $value);
                        }
                    }
                }
            }

            if (! feof($input) || $sheets === []) {
                throw new RuntimeException('El respaldo SQL no se pudo leer o no contiene tablas.');
            }

            $spreadsheet->setActiveSheetIndex(0);
            (new Xlsx($spreadsheet))->save($excelPath);
        } finally {
            fclose($input);
            $spreadsheet->disconnectWorksheets();
        }
    }

    private function sheetTitle(string $table, array $sheets): string
    {
        $base = mb_substr(preg_replace('/[\\\\\/*?:\[\]]/', '_', $table), 0, 31);
        $title = $base;
        $suffix = 2;
        $used = array_map(fn ($sheet) => mb_strtolower($sheet->getTitle()), $sheets);

        while (in_array(mb_strtolower($title), $used, true)) {
            $ending = '_' . $suffix++;
            $title = mb_substr($base, 0, 31 - strlen($ending)) . $ending;
        }

        return $title;
    }

    private function writeCell($sheet, int $column, int $row, string $value): void
    {
        $value = mb_scrub($value, 'UTF-8');
        if (mb_strlen($value) > 32767) {
            throw new RuntimeException('Un valor excede el límite de caracteres de una celda Excel.');
        }

        $sheet->setCellValueExplicit(Coordinate::stringFromColumnIndex($column) . $row, $value, DataType::TYPE_STRING);
    }

    private function parseRows(string $sql): iterable
    {
        $length = strlen($sql);
        $index = 0;

        while ($index < $length) {
            if ($sql[$index] !== '(') {
                throw new RuntimeException('El respaldo SQL contiene una fila no reconocida.');
            }
            $index++;
            $values = [];

            while (true) {
                if ($index >= $length) {
                    throw new RuntimeException('El respaldo SQL contiene una fila incompleta.');
                }

                if ($sql[$index] === "'") {
                    $index++;
                    $value = '';
                    $closed = false;
                    while ($index < $length) {
                        $character = $sql[$index++];
                        if ($character === '\\') {
                            $escaped = $sql[$index++] ?? '';
                            $value .= match ($escaped) {
                                '0' => "\0", 'n' => "\n", 'r' => "\r", 't' => "\t", 'b' => "\x08", 'Z' => "\x1a",
                                default => $escaped,
                            };
                        } elseif ($character === "'") {
                            $closed = true;
                            break;
                        } else {
                            $value .= $character;
                        }
                    }
                    if (! $closed) {
                        throw new RuntimeException('El respaldo SQL contiene una cadena incompleta.');
                    }
                    $values[] = $value;
                } else {
                    $start = $index;
                    while ($index < $length && $sql[$index] !== ',' && $sql[$index] !== ')') {
                        $index++;
                    }
                    $value = trim(substr($sql, $start, $index - $start));
                    $values[] = strtoupper($value) === 'NULL' ? null : $value;
                }

                $separator = $sql[$index++] ?? null;
                if ($separator === ')') {
                    yield $values;
                    break;
                }
                if ($separator !== ',') {
                    throw new RuntimeException('El respaldo SQL contiene un separador no reconocido.');
                }
            }

            if ($index < $length) {
                if ($sql[$index++] !== ',') {
                    throw new RuntimeException('El respaldo SQL contiene una separación de filas no reconocida.');
                }
            }
        }
    }
}
