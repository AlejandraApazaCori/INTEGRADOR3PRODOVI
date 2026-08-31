<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampaniaMensaje extends Model
{
    use SoftDeletes;

    protected $table = 'campania_mensajes';

    protected $fillable = [
        'campania_id',
        'remitente_id',
        'tarea_id',
        'contexto_id',
        'mensaje_padre_id',
        'audiencia',
        'contenido',
    ];

    public function campania()
    {
        return $this->belongsTo(Campania::class);
    }

    public function remitente()
    {
        return $this->belongsTo(User::class, 'remitente_id');
    }

    public function tarea()
    {
        return $this->belongsTo(Tarea::class);
    }

    public function contexto()
    {
        return $this->belongsTo(CampaniaMensajeContexto::class, 'contexto_id');
    }

    public function padre()
    {
        return $this->belongsTo(CampaniaMensaje::class, 'mensaje_padre_id');
    }

    public function respuestas()
    {
        return $this->hasMany(CampaniaMensaje::class, 'mensaje_padre_id');
    }

    public function destinatarios()
    {
        return $this->belongsToMany(User::class, 'campania_mensaje_destinatarios', 'mensaje_id', 'user_id')
            ->withPivot('leido_at')
            ->withTimestamps();
    }

    public function imagenes()
    {
        return $this->hasMany(CampaniaMensajeImagen::class, 'mensaje_id');
    }

    public function scopeVisiblePara(Builder $query, User $user, bool $esCliente): Builder
    {
        return $query->where(function (Builder $visible) use ($user, $esCliente) {
            $audiencias = $esCliente ? ['cliente_equipo'] : ['equipo', 'cliente_equipo'];

            $visible->whereIn('audiencia', $audiencias)
                ->orWhere(function (Builder $directos) use ($user) {
                    $directos->where('audiencia', 'directo')
                        ->where(function (Builder $participa) use ($user) {
                            $participa->where('remitente_id', $user->id)
                                ->orWhereHas('destinatarios', fn (Builder $destinatarios) => $destinatarios->whereKey($user->id));
                        });
                });
        });
    }
}
