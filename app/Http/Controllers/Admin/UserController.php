<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\RoleUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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

    public function forceDestroy($id)
    {
        try {
            $user = User::onlyTrashed()->findOrFail($id);
            $userName = $user->name;

            DB::transaction(function () use ($user) {
                $userId = $user->id;

                $campaignIds = collect();
                if (Schema::hasTable('campanias')) {
                    $campaignIds = DB::table('campanias')
                        ->where('usuario_creador_id', $userId)
                        ->orWhere('community_manager_id', $userId)
                        ->orWhere('usuario_cliente_id', $userId)
                        ->pluck('id');
                }

                $taskIds = collect();
                if (Schema::hasTable('tareas')) {
                    $taskIds = DB::table('tareas')
                        ->where(function ($query) use ($userId, $campaignIds) {
                            $query->where('creador_id', $userId)
                                ->orWhere('asignado_id', $userId);

                            if ($campaignIds->isNotEmpty()) {
                                $query->orWhereIn('campania_id', $campaignIds);
                            }
                        })
                        ->pluck('id');
                }

                $taskFileIds = collect();
                if (Schema::hasTable('tarea_archivos')) {
                    $taskFileIds = DB::table('tarea_archivos')
                        ->where(function ($query) use ($userId, $taskIds) {
                            $query->where('user_id', $userId);

                            if ($taskIds->isNotEmpty()) {
                                $query->orWhereIn('tarea_id', $taskIds);
                            }
                        })
                        ->pluck('id');
                }

                $commentIds = collect();
                if (Schema::hasTable('tarea_comentarios')) {
                    $commentIds = DB::table('tarea_comentarios')
                        ->where(function ($query) use ($userId, $taskIds, $taskFileIds) {
                            $query->where('user_id', $userId);

                            if ($taskIds->isNotEmpty()) {
                                $query->orWhere(function ($subquery) use ($taskIds) {
                                    $subquery->where('comentable_type', 'App\\Models\\Tarea')
                                        ->whereIn('comentable_id', $taskIds);
                                });
                            }

                            if ($taskFileIds->isNotEmpty()) {
                                $query->orWhere(function ($subquery) use ($taskFileIds) {
                                    $subquery->where('comentable_type', 'App\\Models\\TareaArchivo')
                                        ->whereIn('comentable_id', $taskFileIds);
                                });
                            }
                        })
                        ->pluck('id');
                }

                if (Schema::hasTable('comentario_archivos') && $commentIds->isNotEmpty()) {
                    DB::table('comentario_archivos')->whereIn('comentario_id', $commentIds)->delete();
                }
                if (Schema::hasTable('tarea_comentarios')) {
                    DB::table('tarea_comentarios')->whereIn('id', $commentIds)->delete();
                }
                if (Schema::hasTable('tarea_archivos')) {
                    DB::table('tarea_archivos')->whereIn('id', $taskFileIds)->delete();
                }
                if (Schema::hasTable('tareas')) {
                    DB::table('tareas')->whereIn('id', $taskIds)->delete();
                }
                if (Schema::hasTable('empresa_campania') && $campaignIds->isNotEmpty()) {
                    DB::table('empresa_campania')->whereIn('campania_id', $campaignIds)->delete();
                }
                if (Schema::hasTable('campanias')) {
                    DB::table('campanias')->whereIn('id', $campaignIds)->delete();
                }

                $paymentIds = collect();
                if (Schema::hasTable('pagos')) {
                    $paymentIds = DB::table('pagos')->where('usuario_id', $userId)->pluck('id');
                    DB::table('pagos')->where('aprobado_por', $userId)->update(['aprobado_por' => null]);
                }

                if (Schema::hasTable('comprobantes_pago') && $paymentIds->isNotEmpty()) {
                    DB::table('comprobantes_pago')->whereIn('pago_id', $paymentIds)->delete();
                }
                if (Schema::hasTable('codigos_pagos')) {
                    DB::table('codigos_pagos')
                        ->where('usuario_id', $userId)
                        ->when($paymentIds->isNotEmpty(), fn ($query) => $query->orWhereIn('pago_id', $paymentIds))
                        ->delete();
                }
                if (Schema::hasTable('pagos')) {
                    DB::table('pagos')->whereIn('id', $paymentIds)->delete();
                }

                $subscriptionIds = collect();
                if (Schema::hasTable('suscripciones')) {
                    $subscriptionIds = DB::table('suscripciones')->where('usuario_id', $userId)->pluck('id');
                }
                if (Schema::hasTable('planes_marketing') && $subscriptionIds->isNotEmpty()) {
                    DB::table('planes_marketing')->whereIn('suscripcion_id', $subscriptionIds)->delete();
                }
                if (Schema::hasTable('suscripciones')) {
                    DB::table('suscripciones')->whereIn('id', $subscriptionIds)->delete();
                }

                foreach (['briefs', 'social_accounts', 'role_user', 'access_logs', 'security_logs'] as $table) {
                    if (Schema::hasTable($table)) {
                        DB::table($table)->where('user_id', $userId)->delete();
                    }
                }

                if (Schema::hasTable('audit_logs')) {
                    DB::table('audit_logs')
                        ->where('user_id', $userId)
                        ->orWhere(function ($query) use ($userId) {
                            $query->where('auditable_type', User::class)
                                ->where('auditable_id', $userId);
                        })
                        ->delete();
                }
                if (Schema::hasTable('sessions')) {
                    DB::table('sessions')->where('user_id', $userId)->delete();
                }
                if (Schema::hasTable('notifications')) {
                    DB::table('notifications')
                        ->where('notifiable_type', User::class)
                        ->where('notifiable_id', $userId)
                        ->delete();
                }
                if (Schema::hasTable('personal_access_tokens')) {
                    DB::table('personal_access_tokens')
                        ->where('tokenable_type', User::class)
                        ->where('tokenable_id', $userId)
                        ->delete();
                }
                if (Schema::hasTable('password_reset_tokens')) {
                    DB::table('password_reset_tokens')->where('email', $user->email)->delete();
                }

                if (Schema::hasTable('empresas')) {
                    DB::table('empresas')->where('usuario_id', $userId)->delete();
                }

                DB::table('users')->where('id', $userId)->delete();
            });

            return redirect()->route('administrador.usuarios.eliminados')
                ->with('success', "El usuario {$userName} fue eliminado permanentemente.");
        } catch (\Illuminate\Database\QueryException $e) {
            Log::warning('No se pudo eliminar permanentemente un usuario por información relacionada.', [
                'user_id' => $id,
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->with(
                'error',
                'No se puede eliminar permanentemente este usuario porque conserva información relacionada dentro del sistema.'
            );
        } catch (\Exception $e) {
            Log::error('Error al eliminar permanentemente un usuario.', [
                'user_id' => $id,
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'No se pudo eliminar permanentemente el usuario.');
        }
    }
}
