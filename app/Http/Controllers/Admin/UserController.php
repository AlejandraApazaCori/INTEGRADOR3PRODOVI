<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\RoleUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Obtener todos los roles para el select
        $roles = Role::all();
        
        // Consulta base con relaciones
        $query = User::with(['roles', 'suscripciones']);
        
        // Filtrar por nombre si se proporciona
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Filtrar por rol si se selecciona
        if ($request->has('role') && !empty($request->role)) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('roles.id', $request->role);
            });
        }
        
        // Filtrar por estado
        if ($request->has('status') && !empty($request->status)) {
            switch ($request->status) {
                case 'admin':
                    $query->whereHas('roles', function($q) {
                        $q->whereIn('nombre_rol', ['Super Administrador', 'Administrador']);
                    });
                    break;
                    
                case 'active':
                    $query->whereHas('suscripciones', function($q) {
                        $q->where('estado', 'activa')
                          ->where('fecha_fin', '>', now());
                    })->whereDoesntHave('roles', function($q) {
                        $q->whereIn('nombre_rol', ['Super Administrador', 'Administrador']);
                    });
                    break;
                    
                case 'inactive':
                    $query->whereHas('suscripciones', function($q) {
                        $q->where('estado', '!=', 'activa')
                          ->orWhere('fecha_fin', '<', now());
                    })->whereDoesntHave('roles', function($q) {
                        $q->whereIn('nombre_rol', ['Super Administrador', 'Administrador']);
                    });
                    break;
                    
                case 'no_plan':
                    $query->doesntHave('suscripciones')
                          ->whereDoesntHave('roles', function($q) {
                              $q->whereIn('nombre_rol', ['Super Administrador', 'Administrador']);
                          });
                    break;
            }
        }
        
        // Ordenar y paginar
        $users = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('administrador.usuarios.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('administrador.usuarios.create', compact('roles'));
    }

    public function store(Request $request)
    {
        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ], [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'Debe ingresar un correo electrónico válido',
            'email.unique' => 'Este correo electrónico ya está en uso',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'roles.required' => 'Debe seleccionar al menos un rol',
            'roles.*.exists' => 'Uno de los roles seleccionados no es válido',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Crear el usuario
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);

            // Asignar roles al usuario
            $user->roles()->attach($request->roles);

            return redirect()->route('administrador.usuarios.index')
                ->with('success', 'Usuario creado correctamente');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear el usuario: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        $user = User::with('roles')->findOrFail($id);
        $roles = Role::all();
        
        // Obtener IDs de los roles del usuario
        $userRoles = $user->roles->pluck('id')->toArray();
        
        return view('administrador.usuarios.edit', compact('user', 'roles', 'userRoles'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ], [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'Debe ingresar un correo electrónico válido',
            'email.unique' => 'Este correo electrónico ya está en uso',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'roles.required' => 'Debe seleccionar al menos un rol',
            'roles.*.exists' => 'Uno de los roles seleccionados no es válido',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Actualizar datos del usuario
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            
            // Actualizar contraseÃ±a si se proporciona
            if (!empty($request->password)) {
                $user->password = Hash::make($request->password);
            }
            
            $user->save();
            
            // Actualizar roles del usuario
            $user->roles()->sync($request->roles);

            return redirect()->route('administrador.usuarios.index')
                ->with('success', 'Usuario actualizado correctamente');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar el usuario: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::with(['roles', 'suscripciones'])->findOrFail($id);
            
            // Eliminar suscripciones relacionadas primero
            if ($user->suscripciones->isNotEmpty()) {
                $user->suscripciones()->delete();
            }
            
            // Eliminar relaciones de roles
            if ($user->roles->isNotEmpty()) {
                $user->roles()->detach();
            }
            
            // Eliminar el usuario
            $user->delete();
            
            return redirect()->route('administrador.usuarios.index')
                ->with('success', 'Usuario eliminado correctamente');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar el usuario: ' . $e->getMessage());
        }
    }

    public function deletedUsers()
    {
        $users = User::onlyTrashed()->with(['roles', 'suscripciones' => function($query) {
            $query->withTrashed();
        }])->orderBy('deleted_at', 'desc')->paginate(10);
        
        return view('administrador.usuarios.eliminados', compact('users'));
    }

    public function restore($id)
    {
        try {
            $user = User::onlyTrashed()->findOrFail($id);
            
            // Restaurar suscripciones relacionadas si existen
            if ($user->suscripciones()->withTrashed()->exists()) {
                $user->suscripciones()->withTrashed()->restore();
            }
            
            // Restaurar el usuario
            $user->restore();
            
            return redirect()->route('administrador.usuarios.eliminados')
                ->with('success', 'Usuario restaurado correctamente');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al restaurar el usuario: ' . $e->getMessage());
        }
    }
}