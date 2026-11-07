<?php

namespace App\Services;

use RuntimeException;

class DatabaseBackupSqlRows
{
    public function parse(string $sql): iterable
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

            if ($index < $length && $sql[$index++] !== ',') {
                throw new RuntimeException('El respaldo SQL contiene una separación de filas no reconocida.');
            }
        }
    }
}
