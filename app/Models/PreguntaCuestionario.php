<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreguntaCuestionario extends Model
{
    use HasFactory, SoftDeletes;

    // Agrega esta línea
    protected $table = 'preguntas_cuestionario';

    protected $fillable = [
        'tema_id',
        'pregunta',
        'orden',
        'tipo_respuesta',
        'opciones',
        'ayuda',
        'requerido',
    ];

    protected $casts = [
        'opciones' => 'array',
    ];

    /**
     * Obtener el tema de la pregunta
     */
    public function tema()
    {
        return $this->belongsTo(TemaCuestionario::class, 'tema_id');
    }

    /**
     * Obtener las respuestas a esta pregunta
     */
    public function respuestas()
    {
        return $this->hasMany(RespuestaCuestionario::class, 'pregunta_id');
    }
}
