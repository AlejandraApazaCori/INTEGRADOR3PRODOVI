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
            'email' => 'prodovialejandra@gmail.com',
        ]);

        $user->name = 'Administrador Prodovi';
        $user->phone = '62397902';
        $user->password = 'adminprodovi@2026';
        $user->email_verified_at = now();
        $user->deleted_at = null;
        $user->save();

        $adminRole = Role::withTrashed()->firstOrNew([
            'nombre_rol' => 'Super Administrador',
        ]);

        $adminRole->deleted_at = null;
        $adminRole->save();

        $user->roles()->sync([$adminRole->id]);
    }
}