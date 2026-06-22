<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::with(['permissions', 'users'])->orderBy('nombre_rol')->get();
        $permissions = Permission::with('roles')->orderBy('nombre_permiso')->get();
        $users = User::with('roles')->orderBy('name')->paginate(12);

        return view('administrador.usuarios.rolespermisos', compact('roles', 'permissions', 'users'));
    }

    public function storeRole(Request $request)
    {
        $data = $request->validate([
            'nombre_rol' => ['required', 'string', 'max:45', Rule::unique('roles', 'nombre_rol')],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'nombre_rol' => $data['nombre_rol'],
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()
            ->route('administrador.roles.index')
            ->with('success', 'Rol creado correctamente.');
    }

    public function updateRole(Request $request, Role $role)
    {
        $data = $request->validate([
            'nombre_rol' => ['required', 'string', 'max:45', Rule::unique('roles', 'nombre_rol')->ignore($role->id)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->update([
            'nombre_rol' => $data['nombre_rol'],
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()
            ->route('administrador.roles.index')
            ->with('success', 'Rol actualizado correctamente.');
    }

    public function destroyRole(Role $role)
    {
        $role->permissions()->detach();
        $role->users()->detach();
        $role->delete();

        return redirect()
            ->route('administrador.roles.index')
            ->with('success', 'Rol eliminado correctamente.');
    }

    public function storePermission(Request $request)
    {
        $data = $request->validate([
            'nombre_permiso' => ['required', 'string', 'max:120', Rule::unique('permissions', 'nombre_permiso')],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        Permission::create([
            'nombre_permiso' => $data['nombre_permiso'],
            'slug' => Str::slug($data['nombre_permiso']),
            'descripcion' => $data['descripcion'] ?? null,
        ]);

        return redirect()
            ->route('administrador.roles.index')
            ->with('success', 'Permiso creado correctamente.');
    }

    public function updatePermission(Request $request, Permission $permission)
    {
        $data = $request->validate([
            'nombre_permiso' => ['required', 'string', 'max:120', Rule::unique('permissions', 'nombre_permiso')->ignore($permission->id)],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        $permission->update([
            'nombre_permiso' => $data['nombre_permiso'],
            'slug' => Str::slug($data['nombre_permiso']),
            'descripcion' => $data['descripcion'] ?? null,
        ]);

        return redirect()
            ->route('administrador.roles.index')
            ->with('success', 'Permiso actualizado correctamente.');
    }

    public function destroyPermission(Permission $permission)
    {
        $permission->roles()->detach();
        $permission->delete();

        return redirect()
            ->route('administrador.roles.index')
            ->with('success', 'Permiso eliminado correctamente.');
    }

    public function syncUserRoles(Request $request, User $user)
    {
        $data = $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user->roles()->sync($data['roles'] ?? []);

        return redirect()
            ->route('administrador.roles.index', ['page' => $request->input('page')])
            ->with('success', 'Roles del usuario actualizados correctamente.');
    }
}
