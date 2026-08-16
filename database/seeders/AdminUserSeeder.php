<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::withTrashed()->firstOrNew([
            'email' => 'administrador_prodovi@gmail.com',
        ]);

        $user->name = 'Administrador Prodovi';
        $user->phone = '79561365';
        $user->password = 'adminstradorProdovi123456789';
        $user->email_verified_at = now();
        $user->deleted_at = null;
        $user->save();

        $adminRoles = collect(['Super Administrador', 'Administrador'])
            ->map(function (string $roleName) {
                $role = Role::withTrashed()->firstOrNew(['nombre_rol' => $roleName]);
                $role->deleted_at = null;
                $role->save();

                return $role->id;
            });

        $user->roles()->sync($adminRoles->all());
    }
}
