<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaniaMensajeContexto extends Model
{
    protected $table = 'campania_mensaje_contextos';

    protected $fillable = ['campania_id', 'creado_por_id', 'nombre'];

    public function campania()
    {
        return $this->belongsTo(Campania::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por_id');
    }

    public function mensajes()
    {
        return $this->hasMany(CampaniaMensaje::class, 'contexto_id');
    }
}
