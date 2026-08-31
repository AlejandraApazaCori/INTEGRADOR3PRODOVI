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
        'objetivo_general',
        'publico_objetivo',
        'mensaje_principal',
        'tono_comunicacion',
        'canales',
        'indicadores',
        'modo_creacion',
        'es_borrador',
        'ai_generation_metadata',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'visto',
        'usuario_creador_id',
        'community_manager_id',
        'disenador_id',
        'usuario_cliente_id',
        'reuniones_cliente_por_mes',
        'suscripcion_id',
    ];

    protected $casts = [
        'canales' => 'array',
        'indicadores' => 'array',
        'es_borrador' => 'boolean',
        'ai_generation_metadata' => 'array',
        'reuniones_cliente_por_mes' => 'integer',
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

    public function disenador()
    {
        return $this->belongsTo(User::class, 'disenador_id');
    }

    public function disenadores()
    {
        return $this->belongsToMany(User::class, 'campania_disenador', 'campania_id', 'user_id')
            ->withTimestamps();
    }

    // Relación con el usuario cliente (dueño de la suscripción)
    public function cliente()
    {
        return $this->belongsTo(User::class, 'usuario_cliente_id');
    }

    public function suscripcion()
    {
        return $this->belongsTo(Suscripcion::class);
    }

    public function tareas()
    {
        return $this->hasMany(Tarea::class);
    }

    public function reuniones()
    {
        return $this->hasMany(Reunion::class);
    }

    public function mensajes()
    {
        return $this->hasMany(CampaniaMensaje::class);
    }

    public function mensajeContextos()
    {
        return $this->hasMany(CampaniaMensajeContexto::class);
    }

    public function empresas()
    {
        return $this->belongsToMany(Empresa::class, 'empresa_campania', 'campania_id', 'empresa_id')
            ->withTimestamps();
    }
}
