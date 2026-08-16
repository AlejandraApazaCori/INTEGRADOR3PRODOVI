<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroPendiente extends Model
{
    protected $table = 'registros_pendientes';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'verification_token_hash',
        'verification_expires_at',
    ];

    protected $hidden = [
        'password',
        'verification_token_hash',
    ];

    protected function casts(): array
    {
        return [
            'verification_expires_at' => 'datetime',
        ];
    }
}
