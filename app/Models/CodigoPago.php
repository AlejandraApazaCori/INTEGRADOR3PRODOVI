<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodigoPago extends Model
{
    use HasFactory;

    // Nombre de la tabla
    protected $table = 'codigos_pagos';

    // Campos asignables masivamente
    protected $fillable = [
        'codigo',
        'usuario_id',
        'pago_id',
        'utilizado',
        'fecha_utilizacion',
        'descargado_at',
    ];

    protected $casts = [
        'fecha_utilizacion' => 'datetime',
        'descargado_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function pago()
    {
        return $this->belongsTo(Pago::class);
    }

    public static function generarCodigoUnico()
    {
        $prefix = 'FIS';
        $maxAttempts = 100;
        $attempts = 0;

        do {
            $codigo = $prefix . str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT);
            $attempts++;
            
            if ($attempts >= $maxAttempts) {
                throw new \RuntimeException('No se pudo generar un código único después de ' . $maxAttempts . ' intentos');
            }
        } while (self::where('codigo', $codigo)->exists());

        return $codigo;
    }
}
