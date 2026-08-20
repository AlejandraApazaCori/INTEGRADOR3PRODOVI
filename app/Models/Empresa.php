<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- AÑADE ESTA LÍNEA
use App\Traits\Auditable;

class Empresa extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'usuario_id',
        'suscripcion_id',
        'nombre_empresa',
        'tipo_empresa',
        'direccion',
        'descripcion',
        'logo',
        'cuestionario_completado',
        'resumen_ejecutivo'
    ];

    /**
     * Obtener el usuario propietario de la empresa
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function suscripcion()
    {
        return $this->belongsTo(Suscripcion::class);
    }

    /**
     * Obtener las respuestas del cuestionario de la empresa
     */
    public function respuestasCuestionario()
    {
        return $this->hasMany(RespuestaCuestionario::class, 'empresa_id');
    }

    /**
     * Obtener las campañas asociadas a la empresa
     */
    public function campanias()
    {
        return $this->belongsToMany(Campania::class, 'empresa_campania', 'empresa_id', 'campania_id')
            ->withPivot(['created_at', 'updated_at'])
            ->withTimestamps();
    }

    /**
     * Obtiene todos los planes de marketing asociados a esta empresa.
     */
    public function planesMarketing(): HasMany // <-- USA EL TIPO CORRECTO
    {
        return $this->hasMany(PlanMarketing::class);
    }

    public function recursos(): HasMany
    {
        return $this->hasMany(RecursoEmpresa::class);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }
}
