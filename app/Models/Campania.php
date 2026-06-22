<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campania extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'campanias';
    protected $fillable = [
        'nombre',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'visto',
        'usuario_creador_id',
        'community_manager_id',
        'usuario_cliente_id'
    ];

    protected $dates = ['fecha_inicio', 'fecha_fin', 'deleted_at'];

    // Relación con el admin que creó la campaña
    public function creador()
    {
        return $this->belongsTo(User::class, 'usuario_creador_id');
    }

    // Relación con el community manager asignado
    public function communityManager()
    {
        return $this->belongsTo(User::class, 'community_manager_id');
    }

    // Relación con el usuario cliente (dueño de la suscripción)
    public function cliente()
    {
        return $this->belongsTo(User::class, 'usuario_cliente_id');
    }
    public function tareas()
    {
        return $this->hasMany(Tarea::class);
    }
    public function empresas()
    {
        return $this->hasMany(Empresa::class, 'usuario_id');
    }
}