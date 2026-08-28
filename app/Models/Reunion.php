<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reunion extends Model
{
    use HasFactory;

    protected $table = 'reuniones';

    protected $fillable = [
        'campania_id',
        'creador_id',
        'titulo',
        'descripcion',
        'plataforma',
        'enlace',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'origen',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function campania()
    {
        return $this->belongsTo(Campania::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    public function participantes()
    {
        return $this->belongsToMany(User::class, 'reunion_user', 'reunion_id', 'user_id')
            ->withTimestamps();
    }
}
