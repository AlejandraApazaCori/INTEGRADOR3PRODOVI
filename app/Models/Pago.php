<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ComprobantePago;
class Pago extends Model
{
    use HasFactory, SoftDeletes;

    // Agrega esta propiedad con los campos que pueden ser asignados masivamente
    protected $fillable = [
        'usuario_id',
        'suscripcion_id',
        'plan_id',
        'codigo_pago',
        'monto',
        'moneda',
        'metodo',
        'estado',
        'aprobado_por',
        'fecha_aprobacion',
        'fecha_pago',
        'visto',
        'provider',
        'provider_transaction_id',
        'provider_reference',
    ];

    protected $casts = [
        'fecha_aprobacion' => 'datetime',
        'fecha_pago' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    public function suscripcion()
    {
        return $this->belongsTo(Suscripcion::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function codigoPago()
    {
        return $this->hasOne(CodigoPago::class);
    }
      public function comprobantePago()
    {
        return $this->hasOne(ComprobantePago::class);
    }
}
