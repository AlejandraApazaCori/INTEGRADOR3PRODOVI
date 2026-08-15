<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudContacto extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_contacto';

    protected $fillable = [
        'nombre',
        'correo',
        'telefono',
        'servicio',
        'mensaje',
        'correo_enviado_at',
    ];

    protected function casts(): array
    {
        return [
            'correo_enviado_at' => 'datetime',
        ];
    }

    public function getServicioNombreAttribute(): string
    {
        return match ($this->servicio) {
            'publicidad' => 'Publicidad y marketing',
            'social' => 'Redes sociales',
            'audiovisual' => 'Producción audiovisual',
            'eventos' => 'Planificación de eventos',
            'bodas' => 'Planificación de bodas',
            'influencers' => 'Manejo de influencers',
            default => 'Otro',
        };
    }
}
