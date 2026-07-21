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
        'fecha_inicio',
        'fecha_limite',
        'estado',
        'prioridad',
        'campania_id',
        'creador_id',
        'asignado_id',
        'publication_status',
        'publication_scheduled_at',
        'published_at',
        'facebook_post_id',
        'publication_error',
        'publication_message',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_limite' => 'date',
        'publication_scheduled_at' => 'datetime',
        'published_at' => 'datetime',
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

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeEnProgreso($query)
    {
        return $query->where('estado', 'en_progreso');
    }

    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'completada');
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
