<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tarea extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'titulo',
        'descripcion',
        'entregable',
        'tipo_contenido',
        'fecha_inicio',
        'fecha_limite',
        'estado',
        'prioridad',
        'requiere_aprobacion',
        'visible_cliente',
        'campania_id',
        'creador_id',
        'asignado_id',
        'publication_status',
        'publication_scheduled_at',
        'published_at',
        'facebook_post_id',
        'instagram_media_id',
        'publication_error',
        'publication_message',
        'publication_platforms',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_limite' => 'date',
        'publication_scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'publication_platforms' => 'array',
        'requiere_aprobacion' => 'boolean',
        'visible_cliente' => 'boolean',
    ];

    public function campania()
    {
        return $this->belongsTo(Campania::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    public function asignado()
    {
        return $this->belongsTo(User::class, 'asignado_id');
    }

    public function responsables()
    {
        return $this->belongsToMany(User::class, 'tarea_user', 'tarea_id', 'user_id')
            ->withTimestamps();
    }

    public function scopePendientes($query)
    {
        return $query->whereIn('estado', ['no_iniciado', 'pendiente']);
    }

    public function scopeEnProgreso($query)
    {
        return $query->where('estado', 'en_curso');
    }

    public function scopeCompletadas($query)
    {
        return $query->whereIn('estado', ['entregado', 'aprobado', 'publicado']);
    }

    public function archivos()
    {
        return $this->hasMany(TareaArchivo::class);
    }

    public function comentarios()
    {
        return $this->morphMany(TareaComentario::class, 'comentable');
    }
}
