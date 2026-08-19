<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne; // <-- AÑADE ESTA LÍNEA
use Carbon\Carbon;
use App\Models\PlanMarketing;

class Suscripcion extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'suscripciones';

    protected $fillable = [
        'usuario_id',
        'plan_id',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'vigencia_activada_at',
        'fecha_cancelacion',
        'metodo_pago'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'vigencia_activada_at' => 'datetime',
        'fecha_cancelacion' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($suscripcion) {
            if ($suscripcion->isDirty('fecha_fin') || $suscripcion->isDirty('estado')) {
                if ($suscripcion->vigencia_activada_at && $suscripcion->fecha_fin < now() && $suscripcion->estado == 'activa') {
                    $suscripcion->estado = 'finalizada';
                    $suscripcion->fecha_cancelacion = now();
                }
            }
        });

        static::retrieved(function ($suscripcion) {
            if ($suscripcion->vigencia_activada_at && $suscripcion->fecha_fin < now() && $suscripcion->estado == 'activa') {
                $suscripcion->estado = 'finalizada';
                $suscripcion->fecha_cancelacion = now();
                $suscripcion->saveQuietly();
            }
        });
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function empresa(): HasOne
    {
        return $this->hasOne(Empresa::class);
    }

    public function campanias()
    {
        return $this->hasMany(Campania::class);
    }

    /**
     * Obtiene el plan de marketing asociado a esta suscripción.
     */
    public function planMarketing(): HasOne // <-- USA EL TIPO CORRECTO
    {
        return $this->hasOne(PlanMarketing::class);
    }

    public function getEstaActivaAttribute()
    {
        return $this->estado == 'activa'
            && (! $this->vigencia_activada_at || $this->fecha_fin > now());
    }

    public function getEstaVencidaAttribute()
    {
        return $this->vigencia_activada_at && $this->fecha_fin < now();
    }
}
