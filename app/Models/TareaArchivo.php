<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TareaArchivo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tarea_id',
        'user_id',
        'nombre_original',
        'ruta_archivo',
        'extension',
        'mime_type',
        'tamanio',
        'descripcion',
        'estado',
        'visto',
    ];

    protected $casts = [
        'tamanio' => 'integer',
    ];

    public function tarea()
    {
        return $this->belongsTo(Tarea::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comentarios()
    {
        return $this->morphMany(TareaComentario::class, 'comentable');
    }
}