<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'Ver dashboard' => 'Acceso al panel principal del administrador.',
            'Gestionar usuarios' => 'Crear, editar y eliminar usuarios.',
            'Gestionar roles' => 'Crear, editar y eliminar roles.',
            'Gestionar permisos' => 'Crear, editar y eliminar permisos.',
            'Gestionar pagos' => 'Administrar pagos y suscripciones.',
            'Gestionar empresas' => 'Administrar empresas registradas.',
            'Gestionar campañas' => 'Administrar campañas de marketing.',
            'Gestionar cuestionarios' => 'Editar la estructura de cuestionarios.',
            'Ver logs' => 'Consultar bitácoras del sistema.',
        ];

        foreach ($permissions as $name => $description) {
            $permission = Permission::withTrashed()->firstOrNew([
                'nombre_permiso' => $name,
            ]);

            $permission->slug = Str::slug($name);
            $permission->descripcion = $description;
            $permission->deleted_at = null;
            $permission->save();
        }

        $allPermissionIds = Permission::pluck('id')->all();

        $rolePermissions = [
            'Super Administrador' => $allPermissionIds,
            'Administrador' => Permission::whereIn('nombre_permiso', [
                'Ver dashboard',
                'Gestionar usuarios',
                'Gestionar roles',
                'Gestionar permisos',
                'Gestionar pagos',
                'Gestionar empresas',
                'Gestionar campañas',
                'Gestionar cuestionarios',
                'Ver logs',
            ])->pluck('id')->all(),
            'Community Manager' => Permission::whereIn('nombre_permiso', [
                'Ver dashboard',
                'Gestionar campañas',
            ])->pluck('id')->all(),
            'Diseñador' => Permission::whereIn('nombre_permiso', [
                'Ver dashboard',
            ])->pluck('id')->all(),
            'Cliente' => [],
        ];

        foreach ($rolePermissions as $roleName => $permissionIds) {
            $role = Role::where('nombre_rol', $roleName)->first();

            if ($role) {
                $role->permissions()->sync($permissionIds);
            }
        }
    }
}
