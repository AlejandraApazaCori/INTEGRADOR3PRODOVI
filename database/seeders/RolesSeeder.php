<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'Super Administrador',
            'Administrador',
            'Community Manager',
            'Diseñador',
            'Cliente',
        ];

        foreach ($roles as $nombreRol) {
            $role = Role::withTrashed()->firstOrNew([
                'nombre_rol' => $nombreRol,
            ]);

            $role->deleted_at = null;
            $role->save();
        }
    }
}