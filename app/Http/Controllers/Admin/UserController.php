<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Plan;
use App\Models\GoogleDriveReport;
use App\Exports\UsuariosChartReportExport;
use App\Exports\UsuariosReportExport;
use App\Services\GoogleDriveReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Obtener todos los roles para el select
        $roles = Role::all();
        $planes = Plan::where('activo', true)->orderBy('orden')->get();
        
        // Consulta base con relaciones
        $query = $this->usersWithRegistrationNumber(['roles', 'suscripciones']);
        
        // Filtrar por nombre si se proporciona
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        
        // Filtrar por rol si se selecciona
        if ($request->has('role') && !empty($request->role)) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('roles.id', $request->role);
            });
        }

        if ($request->filled('plan')) {
            $query->whereHas('suscripciones', function ($q) use ($request) {
                $q->where('plan_id', $request->plan);
            });
        }

        if ($request->boolean('without_any_plan')) {
            $query->doesntHave('suscripciones');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('users.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('users.created_at', '<=', $request->date_to);
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
        
        // Por defecto se muestran primero los usuarios registrados recientemente.
        $direction = $request->input('order') === 'oldest' ? 'asc' : 'desc';
        $perPage = max(5, min((int) $request->input('per_page', 10), 200));
        $users = $query
            ->orderBy('users.created_at', $direction)
            ->orderBy('users.id', $direction)
            ->paginate($perPage)
            ->withQueryString();
        
        return view('administrador.usuarios.index', compact('users', 'roles', 'planes'));
    }

    public function activity(Request $request)
    {
        $loginStats = DB::table('security_logs')
            ->select('user_id')
            ->selectRaw('COUNT(*) as login_count')
            ->selectRaw('MAX(created_at) as last_login_at')
            ->where('event_type', 'login_success')
            ->whereNotNull('user_id');

        if ($request->filled('date_from')) {
            $loginStats->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $loginStats->whereDate('created_at', '<=', $request->date_to);
        }
        $loginStats->groupBy('user_id');

        $usersQuery = User::with(['roles', 'socialAccounts'])
            ->joinSub($loginStats, 'login_stats', function ($join) {
                $join->on('users.id', '=', 'login_stats.user_id');
            })
            ->select('users.*', 'login_stats.login_count', 'login_stats.last_login_at');

        if ($request->filled('search')) {
            $usersQuery->where(function ($query) use ($request) {
                $query->where('users.name', 'like', '%' . $request->search . '%')
                    ->orWhere('users.email', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('role')) {
            $usersQuery->whereHas('roles', fn ($query) => $query->where('roles.id', $request->role));
        }

        $users = $usersQuery
            ->orderByDesc('login_stats.last_login_at')
            ->paginate(10)
            ->withQueryString();

        $roles = Role::withCount('users')->orderByDesc('users_count')->get();
        $allLoginStats = DB::table('security_logs')
            ->select('user_id')
            ->selectRaw('COUNT(*) as login_count')
            ->selectRaw('MAX(created_at) as last_login_at')
            ->where('event_type', 'login_success')
            ->whereNotNull('user_id')
            ->groupBy('user_id');

        $recentUsers = User::with(['roles', 'socialAccounts'])
            ->joinSub(clone $allLoginStats, 'login_stats', fn ($join) => $join->on('users.id', '=', 'login_stats.user_id'))
            ->select('users.*', 'login_stats.login_count', 'login_stats.last_login_at')
            ->orderByDesc('login_stats.last_login_at')
            ->limit(5)
            ->get();
        $topUsers = User::with('roles')
            ->joinSub(clone $allLoginStats, 'login_stats', fn ($join) => $join->on('users.id', '=', 'login_stats.user_id'))
            ->select('users.*', 'login_stats.login_count', 'login_stats.last_login_at')
            ->orderByDesc('login_stats.login_count')
            ->orderByDesc('login_stats.last_login_at')
            ->limit(5)
            ->get();

        $loginDates = DB::table('security_logs')
            ->where('event_type', 'login_success')
            ->where('created_at', '>=', now()->copy()->subMonths(6)->startOfMonth())
            ->pluck('created_at')
            ->map(fn ($date) => Carbon::parse($date));
        $dailyLogins = collect(range(6, 0))->map(function ($offset) use ($loginDates) {
            $date = today()->subDays($offset);
            return ['label' => $date->format('d/m'), 'value' => $loginDates->filter(fn ($login) => $login->isSameDay($date))->count()];
        });
        $weeklyLogins = collect(range(7, 0))->map(function ($offset) use ($loginDates) {
            $start = now()->startOfWeek()->subWeeks($offset);
            $end = $start->copy()->endOfWeek();
            return ['label' => 'Sem ' . $start->weekOfYear, 'value' => $loginDates->filter(fn ($login) => $login->betweenIncluded($start, $end))->count()];
        });
        $monthlyLogins = collect(range(5, 0))->map(function ($offset) use ($loginDates) {
            $month = now()->startOfMonth()->subMonths($offset);
            return ['label' => $month->copy()->locale('es')->translatedFormat('M Y'), 'value' => $loginDates->filter(fn ($login) => $login->isSameMonth($month))->count()];
        });

        $totalUsers = User::count();
        $usersWithLogin = DB::table('security_logs')
            ->join('users', 'users.id', '=', 'security_logs.user_id')
            ->whereNull('users.deleted_at')
            ->where('security_logs.event_type', 'login_success')
            ->distinct()
            ->count('security_logs.user_id');
        $recentActiveUsers = DB::table('security_logs')
            ->join('users', 'users.id', '=', 'security_logs.user_id')
            ->whereNull('users.deleted_at')
            ->where('security_logs.event_type', 'login_success')
            ->where('security_logs.created_at', '>=', now()->subDays(15))
            ->distinct()
            ->count('security_logs.user_id');
        $inactiveUsers = max(0, $usersWithLogin - $recentActiveUsers);
        $neverLoggedIn = max(0, $totalUsers - $usersWithLogin);
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $unverifiedUsers = max(0, $totalUsers - $verifiedUsers);
        $recentlyDeletedUsers = User::onlyTrashed()->where('deleted_at', '>=', now()->subDays(30))->count();

        return view('administrador.usuarios.actividad', compact(
            'users', 'roles', 'recentUsers', 'topUsers', 'dailyLogins', 'weeklyLogins', 'monthlyLogins',
            'totalUsers', 'recentActiveUsers', 'inactiveUsers', 'neverLoggedIn', 'verifiedUsers',
            'unverifiedUsers', 'recentlyDeletedUsers'
        ));
    }

    public function exportReport(Request $request, string $report, string $destination)
    {
        abort_unless(in_array($report, ['filtered', 'general', 'without_plan', 'without_any_plan'], true), 404);
        abort_unless(in_array($destination, ['excel', 'drive', 'pdf'], true), 404);

        $query = $this->usersWithRegistrationNumber(['roles', 'suscripciones.plan']);
        $plan = null;

        if ($report === 'filtered') {
            $this->applyReportFilters($query, $request);
        } elseif ($report === 'without_plan') {
            $request->validate(['plan' => ['required', 'exists:plan,id']]);
            $plan = Plan::findOrFail($request->integer('plan'));
            if ($request->filled('role')) {
                $query->whereHas('roles', fn ($q) => $q->where('roles.id', $request->role));
            }
            $query->whereDoesntHave('suscripciones', fn ($q) => $q->where('plan_id', $plan->id));
        } elseif ($report === 'without_any_plan') {
            $query->doesntHave('suscripciones');
        }

        $direction = $request->input('order') === 'oldest' ? 'asc' : 'desc';
        $users = $query->orderBy('users.created_at', $direction)->orderBy('users.id', $direction)->get();
        $view = match ($report) {
            'filtered' => 'excel.usuarios-filtrados',
            'general' => 'excel.usuarios-generales',
            'without_plan' => 'excel.usuarios-sin-plan-especifico',
            default => 'excel.usuarios-sin-plan',
        };
        $label = match ($report) {
            'filtered' => 'usuarios_filtrados',
            'general' => 'usuarios_generales',
            'without_plan' => 'usuarios_sin_' . str($plan->nombre)->slug('_'),
            default => 'usuarios_sin_plan',
        };
        $filters = $request->only(['search', 'role', 'status', 'plan', 'without_any_plan', 'date_from', 'date_to', 'order']);

        if ($destination === 'pdf') {
            $reportTitle = match ($report) {
                'filtered' => 'Reporte de usuarios filtrados',
                'general' => 'Listado general de usuarios',
                'without_plan' => 'Usuarios no inscritos al plan: ' . $plan->nombre,
                default => 'Usuarios sin ningún plan',
            };
            $roleChart = null;
            $statusChart = null;

            if (in_array($report, ['filtered', 'general'], true)) {
                $chartExport = new UsuariosChartReportExport($view, $users, $plan, $filters);
                $roleChart = $this->donutChartDataUri('Distribución por tipo de rol', $chartExport->roleStats());
                $statusChart = $this->donutChartDataUri('Distribución por estado', $chartExport->statusStats());
            }

            return Pdf::loadView('pdf.usuarios-reporte', compact(
                'users', 'plan', 'report', 'reportTitle', 'roleChart', 'statusChart'
            ))
                ->setOption('isPhpEnabled', true)
                ->setPaper('a4', 'landscape')
                ->download($label . '_' . now()->format('Y_m_d_His') . '.pdf');
        }

        $fileName = $label . '_' . now()->format('Y_m_d_His') . '.xlsx';
        $export = in_array($report, ['filtered', 'general'], true)
            ? new UsuariosChartReportExport($view, $users, $plan, $filters)
            : new UsuariosReportExport($view, $users, $plan, $filters);

        if ($destination === 'excel') {
            return Excel::download($export, $fileName);
        }

        try {
            $contents = Excel::raw($export, ExcelFormat::XLSX);
            $drive = app(GoogleDriveReportService::class);
            $request->validate([
                'folder_id' => ['nullable', 'string', 'max:255'],
                'new_folder' => ['nullable', 'string', 'max:80', 'regex:~^[\p{L}\p{N} _().-]+$~u'],
            ]);
            $folderId = $drive->resolveTargetFolder($request->input('folder_id'), $request->input('new_folder'));
            $storedReport = GoogleDriveReport::where('report_key', $report)->first();
            $uploaded = $drive->saveGoogleSheet($fileName, $contents, $folderId, $storedReport?->file_id);
            if (in_array($report, ['filtered', 'general'], true)) {
                $drive->positionUserReportCharts($uploaded['id']);
            }

            GoogleDriveReport::updateOrCreate(
                ['report_key' => $report],
                [
                    'file_id' => $uploaded['id'],
                    'folder_id' => $folderId,
                    'file_name' => $uploaded['name'],
                    'web_view_link' => $uploaded['url'],
                ]
            );

            return back()->with('drive_success', [
                'message' => $storedReport
                    ? 'El reporte se actualizó y conservó el mismo enlace en Google Sheets.'
                    : 'El reporte se creó correctamente en Google Sheets.',
                'url' => $uploaded['url'],
            ]);
        } catch (\Throwable $exception) {
            Log::error('No se pudo crear el reporte de usuarios en Google Drive.', [
                'report' => $report,
                'error' => $exception->getMessage(),
            ]);

            $message = str_contains($exception->getMessage(), 'preg_match')
                ? 'El nombre de la carpeta contiene caracteres no permitidos.'
                : 'No se pudo guardar el reporte en Google Drive. Inténtalo nuevamente o revisa el registro del sistema.';

            return back()->with('drive_error', $message);
        }
    }

    public function driveFolders(Request $request)
    {
        try {
            $request->validate([
                'report' => ['nullable', 'in:filtered,general,without_plan,without_any_plan'],
            ]);

            $data = app(GoogleDriveReportService::class)->listTargetFolders();
            $storedReport = $request->filled('report')
                ? GoogleDriveReport::where('report_key', $request->report)->first()
                : null;

            $currentFolder = null;
            if ($storedReport) {
                $availableFolders = collect([$data['root'], ...$data['folders']]);
                $folder = $availableFolders->firstWhere('id', $storedReport->folder_id);
                $currentFolder = [
                    'id' => $storedReport->folder_id,
                    'name' => $folder['name'] ?? 'Carpeta no disponible',
                    'file_url' => $storedReport->web_view_link,
                ];
            }

            return response()->json([...$data, 'current_folder' => $currentFolder]);
        } catch (\Throwable $exception) {
            Log::error('No se pudieron consultar las carpetas de Google Drive.', ['error' => $exception->getMessage()]);

            return response()->json(['message' => 'No se pudieron consultar las carpetas de Google Drive.'], 500);
        }
    }

    private function applyReportFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('roles.id', $request->role));
        }
        if ($request->filled('plan')) {
            $query->whereHas('suscripciones', fn ($q) => $q->where('plan_id', $request->plan));
        }
        if ($request->boolean('without_any_plan')) {
            $query->doesntHave('suscripciones');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('users.created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('users.created_at', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            match ($request->status) {
                'admin' => $query->whereHas('roles', fn ($q) => $q->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])),
                'active' => $query->whereHas('suscripciones', fn ($q) => $q->where('estado', 'activa')->where('fecha_fin', '>', now())),
                'inactive' => $query->whereHas('suscripciones', fn ($q) => $q->where('estado', '!=', 'activa')->orWhere('fecha_fin', '<', now())),
                'no_plan' => $query->doesntHave('suscripciones'),
                default => null,
            };
        }
    }

    private function donutChartDataUri(string $title, array $stats): ?string
    {
        $total = array_sum(array_column($stats, 'count'));
        if ($total === 0) {
            return null;
        }

        $colors = ['#4f86c6', '#c5534f', '#9abb59', '#8064a2', '#4bacc6', '#f79646', '#7da533', '#117e8c'];
        $height = max(300, 75 + count($stats) * 29);
        $centerY = (int) round($height / 2);
        $radius = 76;
        $circumference = 2 * M_PI * $radius;
        $offset = 0.0;
        $escape = fn (string $value) => htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $segments = '';
        $legend = '';

        foreach ($stats as $index => $stat) {
            $fraction = $stat['count'] / $total;
            $length = $fraction * $circumference;
            $color = $colors[$index % count($colors)];
            $segments .= sprintf(
                '<circle cx="125" cy="%d" r="%d" fill="none" stroke="%s" stroke-width="38" stroke-dasharray="%.3f %.3f" stroke-dashoffset="-%.3f" transform="rotate(-90 125 %d)"/>',
                $centerY,
                $radius,
                $color,
                $length,
                $circumference - $length,
                $offset,
                $centerY,
            );
            $legendY = 54 + $index * 29;
            $legendText = sprintf('%s — %d (%.1f%%)', $stat['label'], $stat['count'], $fraction * 100);
            $legend .= '<rect x="245" y="' . ($legendY - 11) . '" width="13" height="13" rx="3" fill="' . $color . '"/>';
            $legend .= '<text x="267" y="' . $legendY . '" font-family="DejaVu Sans, sans-serif" font-size="13" fill="#374151">' . $escape($legendText) . '</text>';
            $offset += $length;
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="640" height="' . $height . '" viewBox="0 0 640 ' . $height . '">'
            . '<rect width="640" height="' . $height . '" rx="14" fill="#ffffff"/>'
            . '<text x="20" y="25" font-family="DejaVu Sans, sans-serif" font-size="16" font-weight="700" fill="#374151">' . $escape($title) . '</text>'
            . '<circle cx="125" cy="' . $centerY . '" r="' . $radius . '" fill="none" stroke="#edf0ea" stroke-width="38"/>'
            . $segments
            . '<text x="125" y="' . ($centerY - 3) . '" text-anchor="middle" font-family="DejaVu Sans, sans-serif" font-size="25" font-weight="700" fill="#31382b">' . $total . '</text>'
            . '<text x="125" y="' . ($centerY + 19) . '" text-anchor="middle" font-family="DejaVu Sans, sans-serif" font-size="11" fill="#737a70">registros</text>'
            . $legend
            . '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private function usersWithRegistrationNumber(array $relations)
    {
        return User::with($relations)
            ->select('users.*')
            ->selectSub(function ($query) {
                $query->from('users as registered_users')
                    ->selectRaw('COUNT(*)')
                    ->whereNull('registered_users.deleted_at')
                    ->where(function ($query) {
                        $query->whereColumn('registered_users.created_at', '<', 'users.created_at')
                            ->orWhere(function ($query) {
                                $query->whereColumn('registered_users.created_at', '=', 'users.created_at')
                                    ->whereColumn('registered_users.id', '<=', 'users.id');
                            });
                    });
            }, 'registration_number');
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
