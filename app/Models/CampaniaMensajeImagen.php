<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaniaMensajeImagen extends Model
{
    protected $table = 'campania_mensaje_imagenes';

    protected $fillable = [
        'mensaje_id',
        'nombre_original',
        'ruta_archivo',
        'mime_type',
        'tamanio',
    ];

    public function mensaje()
    {
        return $this->belongsTo(CampaniaMensaje::class, 'mensaje_id');
    }
}
