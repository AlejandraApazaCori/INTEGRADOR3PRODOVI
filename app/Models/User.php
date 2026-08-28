<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    use Auditable, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'google_id',
        'social_setup_skipped',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'social_setup_skipped' => 'boolean',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    public function hasAnyRole($roles)
    {
        if (is_array($roles)) {
            return $this->roles()->whereIn('nombre_rol', $roles)->exists();
        }

        return $this->roles()->where('nombre_rol', $roles)->exists();
    }

    public function hasRole($role)
    {
        return $this->hasAnyRole($role);
    }

    public function permissions(): Collection
    {
        return $this->roles
            ->loadMissing('permissions')
            ->pluck('permissions')
            ->flatten()
            ->unique('id')
            ->values();
    }

    public function hasPermission($permission): bool
    {
        $field = str_contains($permission, ' ') ? 'nombre_permiso' : 'slug';

        return $this->permissions()->contains($field, $permission);
    }

    public function suscripciones()
    {
        return $this->hasMany(Suscripcion::class, 'usuario_id');
    }

    public function campaniasCreadas()
    {
        return $this->hasMany(Campania::class, 'usuario_creador_id');
    }

    public function campaniasComoCM()
    {
        return $this->hasMany(Campania::class, 'community_manager_id');
    }

    public function campaniasComoDisenador()
    {
        return $this->hasMany(Campania::class, 'disenador_id');
    }

    public function campaniasComoParteDiseno()
    {
        return $this->belongsToMany(Campania::class, 'campania_disenador', 'user_id', 'campania_id')
            ->withTimestamps();
    }

    public function tareasComoResponsable()
    {
        return $this->belongsToMany(Tarea::class, 'tarea_user', 'user_id', 'tarea_id')
            ->withTimestamps();
    }

    public function reuniones()
    {
        return $this->belongsToMany(Reunion::class, 'reunion_user', 'user_id', 'reunion_id')
            ->withTimestamps();
    }

    public function campaniasCliente()
    {
        return $this->hasMany(Campania::class, 'usuario_cliente_id');
    }

    public function empresas()
    {
        return $this->hasMany(Empresa::class, 'usuario_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function socialAccountsTableExists(): bool
    {
        static $exists;

        return $exists ??= Schema::hasTable('social_accounts');
    }

    public function linkedSocialAccounts(): Collection
    {
        if (! $this->socialAccountsTableExists()) {
            return collect();
        }

        return $this->socialAccounts()->whereNull('empresa_id')->get()->keyBy('provider');
    }

    public function hasLinkedSocialAccount(string $provider): bool
    {
        if (! $this->socialAccountsTableExists()) {
            return false;
        }

        return $this->socialAccounts()
            ->whereNull('empresa_id')
            ->where('provider', $provider)
            ->whereNotNull('provider_user_id')
            ->exists();
    }
}
