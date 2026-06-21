<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class User extends Authenticatable
{
    use SoftDeletes, HasFactory, Notifiable, Auditable;
    /** @use HasFactory<\Database\Factories\UserFactory> */
    // The original `use HasFactory, Notifiable;` line is merged into the one above.

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'google_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    /**
     * Verifica si el usuario tiene alguno de los roles especificados.
     */
    public function hasAnyRole($roles)
    {
        if (is_array($roles)) {
            return $this->roles()->whereIn('nombre_rol', $roles)->exists();
        }
        return $this->roles()->where('nombre_rol', $roles)->exists();
    }


    // app/Models/User.php

    public function suscripciones()
    {
        return $this->hasMany(Suscripcion::class, 'usuario_id');
    }
    //CAMPAÑAS
    public function campaniasCreadas()
    {
        return $this->hasMany(Campania::class, 'usuario_creador_id');
    }

    public function campaniasComoCM()
    {
        return $this->hasMany(Campania::class, 'community_manager_id');
    }

    public function campaniasCliente()
    {
        return $this->hasMany(Campania::class, 'usuario_cliente_id');
    }

    public function empresas()
    {
        return $this->hasMany(Empresa::class, 'usuario_id');
    }

    // En app/Models/User.php


public function pagos()
{
    return $this->hasMany(Pago::class);
}



}

