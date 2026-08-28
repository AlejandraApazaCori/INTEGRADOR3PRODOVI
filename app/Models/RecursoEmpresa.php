<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecursoEmpresa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'recursos_empresa';
    protected $fillable = [
        'empresa_id',
        'tipo',
        'nombre',
        'archivo_path',
        'url',
        'origen',
        'visible_cliente',
        'creado_por_id',
    ];

    protected $casts = [
        'visible_cliente' => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por_id');
    }
}
