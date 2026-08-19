<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecursoEmpresa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'recursos_empresa';
    protected $fillable = ['empresa_id', 'tipo', 'nombre', 'archivo_path', 'url'];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
