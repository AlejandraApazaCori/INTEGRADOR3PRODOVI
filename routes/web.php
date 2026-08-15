<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\Cliente\PagoClienteController;
use App\Http\Controllers\Cliente\SocialAccountController;
use App\Http\Controllers\FacebookPostController;
use App\Http\Controllers\SocialSimulatorController;
use App\Http\Controllers\Admin\AdminAnaliticasController;
use App\Http\Controllers\Admin\PagoAdminController;
use App\Http\Controllers\Admin\PlanMarketingController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\CuestionarioController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ResumenController;
use App\Http\Controllers\ContactoLandingController;
use App\Http\Controllers\MantenimientoWebController;

Route::get('/chatbot', [ChatbotController::class, 'mostrarVista'])->name('chatbot.vista');
Route::view('/privacy-policy', 'legal.privacy-policy')->name('legal.privacy-policy');
Route::view('/terms', 'legal.terms')->name('legal.terms');
Route::view('/data-deletion', 'legal.data-deletion')->name('legal.data-deletion');


Route::get('/', function () {
    return view('welcome');
});

Route::post('/contacto', [ContactoLandingController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('landing.contacto.store');

Route::prefix('ejecutar-migraciones-Ma73027456Lpz')
    ->middleware('throttle:6,1')
    ->group(function () {
        Route::get('/', [MantenimientoWebController::class, 'index'])
            ->name('mantenimiento.web.index');
        Route::post('/{operation}', [MantenimientoWebController::class, 'execute'])
            ->whereIn('operation', ['migrate', 'storage-link'])
            ->name('mantenimiento.web.execute');
    });

Route::get('/api/lstm/facebook/horarios', function () {
    $candidateUrls = array_values(array_filter([
        env('LSTM_API_INTERNAL_URL', 'http://127.0.0.1:8000'),
        env('LSTM_API_URL'),
    ]));

    foreach ($candidateUrls as $baseUrl) {
        $baseUrl = rtrim($baseUrl, '/');

        try {
            $response = Http::timeout(5)
                ->acceptJson()
                ->withHeaders([
                    'ngrok-skip-browser-warning' => 'true',
                ])
                ->get($baseUrl . '/api/horarios/facebook');

            if ($response->successful()) {
                $payload = $response->json();

                if (is_array($payload)) {
                    return response()->json($payload);
                }
            }
        } catch (\Throwable $e) {
        }
    }

    $fallbackPath = resource_path('data/horarios_lstm_facebook.json');

    if (File::exists($fallbackPath)) {
        $fallbackData = json_decode(File::get($fallbackPath), true);

        if (is_array($fallbackData)) {
            return response()->json($fallbackData);
        }
    }

    return response()->json([
        'labels' => [],
        'realData' => [],
        'predData' => [],
        'picos' => '',
    ]);
});
// Rutas de autenticaciÃƒÆ’Ã‚Â³n
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// AutenticaciÃƒÆ’Ã‚Â³n con Google
Route::prefix('api')->group(function () {
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])
        ->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])
        ->name('auth.google.callback');
});

// Rutas del cliente
Route::middleware('auth')->group(function () {
    Route::get('/clientes/home', [ClienteController::class, 'home'])->name('clientes.home');
    Route::get('/clientes/dashboard', [ClienteController::class, 'dashboard'])->name('clientes.dashboard');
    Route::get('/clientes/micuenta', [ClienteController::class, 'miCuenta'])->name('clientes.micuenta');
    Route::get('/clientes/brief', [ClienteController::class, 'brief'])->name('clientes.brief');
    Route::get('/clientes/analiticas', [App\Http\Controllers\Cliente\ClienteAnaliticasController::class, 'index'])
        ->name('clientes.analiticas');
    Route::get('/clientes/analiticas/exportar-pdf', [App\Http\Controllers\Cliente\ClienteAnaliticasController::class, 'exportarPDF'])
        ->name('clientes.analiticas.exportar-pdf');
    Route::get('/clientes/analiticas/reporte-engagement', [App\Http\Controllers\Cliente\ClienteAnaliticasController::class, 'exportarReporteEngagement'])
        ->name('clientes.analiticas.reporte-engagement');
    Route::get('/clientes/analiticas/reporte-alcance', [App\Http\Controllers\Cliente\ClienteAnaliticasController::class, 'exportarReporteAlcance'])
        ->name('clientes.analiticas.reporte-alcance');
    Route::get('/clientes/analiticas/reporte-seguidores', [App\Http\Controllers\Cliente\ClienteAnaliticasController::class, 'exportarReporteSeguidores'])
        ->name('clientes.analiticas.reporte-seguidores');
    Route::get('/clientes/analiticas/reporte-ctr', [App\Http\Controllers\Cliente\ClienteAnaliticasController::class, 'exportarReporteCTR'])
        ->name('clientes.analiticas.reporte-ctr');
    Route::get('/clientes/analiticas/load-view', [App\Http\Controllers\Cliente\ClienteAnaliticasController::class, 'loadView'])
        ->name('clientes.analiticas.load-view');
    
    // Rutas de pago del cliente
    Route::get('/clientes/pago/{plan}', [PagoClienteController::class, 'show'])->name('clientes.pago');
    Route::post('/pago/procesar/{plan}', [PagoClienteController::class, 'procesarPago'])->name('pago.procesar');
    Route::post('/clientes/social/setup-social-accounts', [SocialAccountController::class, 'setupSocialAccountsTable'])->name('clientes.social.setup-social-accounts');
    Route::get('/clientes/social/{provider}/redirect', [SocialAccountController::class, 'redirect'])->name('clientes.social.redirect');
    Route::get('/clientes/social/{provider}/callback', [SocialAccountController::class, 'callback'])->name('clientes.social.callback');
});

//ESTADO PAGO
Route::get('/clientes/estado-pago', [PagoClienteController::class, 'estadoPago'])
    ->name('clientes.pago.estado')
    ->middleware('auth');

// Rutas de Facebook (sin middleware)
Route::get('/facebook/post', [FacebookPostController::class, 'showForm'])->name('facebook.post.form');
Route::post('/facebook/post', [FacebookPostController::class, 'postToPage'])->name('facebook.post');

// Rutas de administrador
Route::prefix('administrador')->middleware('auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('administrador.dashboard');
    Route::post('/comandos/crear-storage-link', function () {
        $user = auth()->user();
        $storageLinkLockKey = 'administrador.storage_link.ejecutado';

        if (! $user || ! $user->hasAnyRole(['Super Administrador', 'Administrador'])) {
            abort(403);
        }

        if (Cache::has($storageLinkLockKey)) {
            return back()->with('error', 'Esta acci?n ya fue ejecutada y qued? deshabilitada por seguridad.');
        }

        try {
            if (File::exists(public_path('storage'))) {
                Cache::forever($storageLinkLockKey, true);

                return back()->with('success', 'El enlace public/storage ya existe. La acci?n qued? deshabilitada por seguridad.');
            }

            $exitCode = Artisan::call('storage:link');

            if ($exitCode === 0 && File::exists(public_path('storage'))) {
                Cache::forever($storageLinkLockKey, true);

                return back()->with('success', 'El enlace public/storage se cre? correctamente y la acci?n qued? deshabilitada.');
            }

            $output = trim(Artisan::output());

            Log::error('No se pudo crear el enlace de storage desde el panel administrador.', [
                'user_id' => $user->id,
                'exit_code' => $exitCode,
                'output' => $output,
            ]);

            return back()->with('error', $output !== ''
                ? 'Hubo un error al ejecutar storage:link: ' . $output
                : 'Hubo un error al ejecutar storage:link.');
        } catch (\Throwable $e) {
            Log::error('Error ejecutando storage:link desde el panel administrador.', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Hubo un error al ejecutar storage:link. Revisa los logs del sistema.');
        }
    })->name('administrador.comandos.crear-storage-link');
    Route::get('/analÃƒÆ’Ã‚Â­ticas', [AdminAnaliticasController::class, 'index'])
        ->name('admin.analiticas.index');
    Route::post('/analÃƒÆ’Ã‚Â­ticas/store-campania', [AdminAnaliticasController::class, 'storeCampania'])
        ->name('admin.analiticas.storeCampania');
    Route::get('/analÃƒÆ’Ã‚Â­ticas/export-campanias', [AdminAnaliticasController::class, 'exportCampanias'])
        ->name('admin.analiticas.exportCampanias');
    Route::get('/admin/generar-reporte-campanas', [AdminAnaliticasController::class, 'generarReporteCampanas'])
        ->name('admin.generar.reporte.campanas');

    // GestiÃƒÆ’Ã‚Â³n de pagos
    Route::prefix('pagos')->group(function () {
        Route::get('/', [PagoAdminController::class, 'index'])
            ->name('administrador.pagos.index');
        Route::get('/realizados', [PagoAdminController::class, 'pagosRealizados'])
            ->name('administrador.pagos.realizados');
        Route::get('/pendientes-fisicos', [PagoAdminController::class, 'pagosPendientesFisicos'])
            ->name('administrador.pagos.pendientes-fisicos');
        Route::get('/finalizados-sin-renovacion', [PagoAdminController::class, 'pagosFinalizadosSinRenovacion'])
            ->name('administrador.pagos.finalizados');
        Route::post('/aprobar/{pago}', [PagoAdminController::class, 'aprobarPagoFisico'])
            ->name('administrador.pagos.aprobar');
        Route::put('/cancelar/{pago}', [PagoAdminController::class, 'cancelarSuscripcion'])
            ->name('administrador.pagos.cancelar');
        Route::put('/reactivar/{pago}', [PagoAdminController::class, 'reactivarSuscripcion'])
            ->name('administrador.pagos.reactivar');
        Route::get('/analÃƒÆ’Ã‚Â­ticas', [PagoAdminController::class, 'analiticas'])->name('administrador.pagos.analiticas');
        Route::get('/buscar', [PagoAdminController::class, 'buscarPagos'])->name('administrador.pagos.buscar');
        Route::post('/cancelar/{pagoId}', [PagoAdminController::class, 'cancelarSuscripcionApi'])->name('administrador.pagos.cancelar.api');
        Route::post('/reactivar/{pagoId}', [PagoAdminController::class, 'reactivarSuscripcionApi'])->name('administrador.pagos.reactivar.api');
        Route::get('/descargar-pdf', [PagoAdminController::class, 'descargarPDF'])->name('administrador.pagos.descargar.pdf');
        Route::get('/descargar-excel', [PagoAdminController::class, 'descargarExcel'])->name('administrador.pagos.descargar.excel');
        Route::get('/reporte-mensual/pdf', [PagoAdminController::class, 'descargarPDFMensual'])->name('administrador.pagos.mensual.pdf');
        Route::get('/reporte-mensual/excel', [PagoAdminController::class, 'descargarExcelMensual'])->name('administrador.pagos.mensual.excel');
        Route::get('/manual/crear', [PagoAdminController::class, 'createManual'])->name('administrador.pagos.manual.crear');
        Route::post('/manual', [PagoAdminController::class, 'storeManual'])->name('administrador.pagos.manual.store');
        Route::get('/ver-recibo/{id}', [PagoAdminController::class, 'verComprobante'])->name('administrador.pagos.ver-recibo');
        Route::get('/descargar-recibo/{id}', [PagoAdminController::class, 'descargarComprobante'])->name('administrador.pagos.descargar-recibo');
    });

    // Gestión de planes
    Route::resource('planes', 'App\Http\Controllers\Admin\PlanController')
        ->except(['show'])
        ->names([
            'index' => 'administrador.planes.index',
            'create' => 'administrador.planes.create',
            'store' => 'administrador.planes.store',
            'edit' => 'administrador.planes.edit',
            'update' => 'administrador.planes.update',
            'destroy' => 'administrador.planes.destroy'
        ]);
    
    Route::post('/planes/caracteristicas', [\App\Http\Controllers\Admin\PlanController::class, 'storeCaracteristica'])
        ->name('administrador.planes.caracteristicas.store');
    Route::put('/planes/caracteristicas/{caracteristica}', [\App\Http\Controllers\Admin\PlanController::class, 'updateCaracteristica'])
        ->name('administrador.planes.caracteristicas.update');

    // Logs
    Route::post('/logs/setup-publicaciones-programadas', function () {
        $user = auth()->user();
        $setupKey = 'administrador.publicaciones_programadas_setup.ejecutado';

        if (! $user || ! $user->hasAnyRole(['Super Administrador', 'Administrador'])) {
            abort(403);
        }

        try {
            if (! Schema::hasColumn('tareas', 'publication_message')) {
                $migrationExitCode = Artisan::call('migrate', [
                    '--path' => 'database/migrations/2026_06_25_000002_add_publication_message_to_tareas_table.php',
                    '--force' => true,
                ]);

                if ($migrationExitCode !== 0 && ! Schema::hasColumn('tareas', 'publication_message')) {
                    $output = trim(Artisan::output());

                    return back()->with('error', $output !== ''
                        ? 'No se pudo aplicar la migracion publication_message: ' . $output
                        : 'No se pudo aplicar la migracion publication_message.');
                }
            }

            $commandExitCode = Artisan::call('publicaciones:procesar-programadas');

            if ($commandExitCode !== 0) {
                $output = trim(Artisan::output());

                return back()->with('error', $output !== ''
                    ? 'No se pudo ejecutar el procesamiento de publicaciones programadas: ' . $output
                    : 'No se pudo ejecutar el procesamiento de publicaciones programadas.');
            }

            Cache::forever($setupKey, true);

            return back()->with('success', 'La carga provisional de publicaciones programadas se ejecuto correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error ejecutando la carga provisional de publicaciones programadas.', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Hubo un error al ejecutar la carga provisional de publicaciones programadas. Revisa los logs del sistema.');
        }
    })->name('administrador.logs.setup-publicaciones-programadas');
    Route::get('/logs', [\App\Http\Controllers\Admin\LogController::class, 'index'])->name('administrador.logs.index');
    Route::get('/logs/export/{type}', [\App\Http\Controllers\Admin\LogController::class, 'exportPdf'])->name('administrador.logs.export');

    // Gestión de campañas
    Route::prefix('campañas')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CampañasController::class, 'index'])
            ->name('administrador.campañas.index');
        Route::get('/analiticas', [\App\Http\Controllers\Admin\CampañasController::class, 'analiticas'])
            ->name('administrador.campañas.analiticas');
        Route::post('/guardar', [\App\Http\Controllers\Admin\CampañasController::class, 'guardar'])
            ->name('administrador.campañas.guardar');
        Route::get('/{campania}', [\App\Http\Controllers\Admin\CampañasController::class, 'show'])
            ->name('administrador.campañas.show');
        Route::get('/{campania}/editar', [\App\Http\Controllers\Admin\CampañasController::class, 'edit'])
            ->name('administrador.campañas.edit');
        Route::put('/{campania}', [\App\Http\Controllers\Admin\CampañasController::class, 'update'])
            ->name('administrador.campañas.update');
        Route::patch('/{campania}/activar', [\App\Http\Controllers\Admin\CampañasController::class, 'activar'])
            ->name('administrador.campañas.activar');
        Route::delete('/{campania}', [\App\Http\Controllers\Admin\CampañasController::class, 'destroy'])
            ->name('administrador.campañas.destroy');
        Route::get('/{campania}/calendario', [\App\Http\Controllers\Admin\TareaController::class, 'calendario'])
            ->name('administrador.campañas.calendario');
        Route::get('/obtener-plan-ia/{usuario_id}', [\App\Http\Controllers\Admin\CampañasController::class, 'obtenerPlanIA'])
            ->name('administrador.campañas.plan-ia');

    // Rutas para tareas
    Route::prefix('/{campania}/tareas')->group(function () {
        Route::get('/crear', [\App\Http\Controllers\Admin\TareaController::class, 'create'])
            ->name('administrador.tareas.create');
        Route::get('/recomendacion-ia', [\App\Http\Controllers\Admin\TareaController::class, 'obtenerRecomendacionIA'])
            ->name('administrador.tareas.recomendacion-ia');
        Route::post('/', [\App\Http\Controllers\Admin\TareaController::class, 'store'])
            ->name('administrador.tareas.store');
    });
});

    // Otras rutas de tareas
    Route::get('/tareas/{tarea}', [\App\Http\Controllers\Admin\TareaController::class, 'show'])
        ->name('administrador.tareas.show');
    Route::get('/tareas/{tarea}/edit', [\App\Http\Controllers\Admin\TareaController::class, 'edit'])
        ->name('administrador.tareas.edit');
    Route::put('/tareas/{tarea}', [\App\Http\Controllers\Admin\TareaController::class, 'update'])
        ->name('administrador.tareas.update');

    // Rutas para archivos de tareas
    Route::prefix('/tareas/{tarea}/archivos')->group(function () {
        Route::get('/subir', [\App\Http\Controllers\Admin\TareaArchivoController::class, 'create'])
            ->name('administrador.tareas.archivos.create');
        Route::post('/', [\App\Http\Controllers\Admin\TareaArchivoController::class, 'store'])
            ->name('administrador.tareas.archivos.store');
    });
    Route::put('/tareas/archivos/{archivo}/estado', [\App\Http\Controllers\Admin\TareaArchivoController::class, 'updateEstado'])
        ->name('administrador.tareas.archivos.update-estado');
    Route::get('/tareas/{tarea}/ver-subidas', [\App\Http\Controllers\Admin\TareaArchivoController::class, 'verSubidas'])
        ->name('administrador.tareas.ver-subidas');

    // Rutas para comentarios de tareas
    Route::prefix('/tareas/{tarea}/comentarios')->group(function () {
        Route::post('/', [\App\Http\Controllers\Admin\TareaComentarioController::class, 'store'])
            ->name('administrador.tareas.comentarios.store');
        Route::delete('/{comentario}', [\App\Http\Controllers\Admin\TareaComentarioController::class, 'destroy'])
            ->name('administrador.tareas.comentarios.destroy');
    });

    // GestiÃƒÆ’Ã‚Â³n de usuarios
    Route::get('/usuarios', [\App\Http\Controllers\Admin\UserController::class, 'index'])
        ->name('administrador.usuarios.index');
    Route::get('/usuarios/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])
        ->name('administrador.usuarios.create');
    Route::post('/usuarios', [\App\Http\Controllers\Admin\UserController::class, 'store'])
        ->name('administrador.usuarios.store');
    Route::get('/usuarios/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])
        ->name('administrador.usuarios.edit');
    Route::put('/usuarios/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])
        ->name('administrador.usuarios.update');
    Route::delete('/usuarios/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])
        ->name('administrador.usuarios.destroy');
    Route::get('/usuarios/eliminados', [\App\Http\Controllers\Admin\UserController::class, 'deletedUsers'])
        ->name('administrador.usuarios.eliminados');
    Route::patch('/usuarios/restore/{user}', [\App\Http\Controllers\Admin\UserController::class, 'restore'])
        ->name('administrador.usuarios.restore');
    Route::get('/usuarios/{user}/view', [\App\Http\Controllers\Admin\UserViewController::class, 'show'])
        ->name('administrador.usuarios.view');
    Route::get('/usuarios/{user}/analiticas-campania', [\App\Http\Controllers\Admin\UserViewController::class, 'campaignAnalytics'])
        ->name('administrador.usuarios.analiticas-campania');

    // Notificaciones
    Route::prefix('notificaciones')->group(function () {
        Route::get('/historial', [\App\Http\Controllers\Admin\NotificacionController::class, 'historial'])
            ->name('administrador.notificaciones.historial');
        Route::post('/marcar-vistas', [\App\Http\Controllers\Admin\NotificacionController::class, 'marcarVistas'])
            ->name('administrador.notificaciones.marcar-vistas');
        Route::get('/conteo', [\App\Http\Controllers\Admin\NotificacionController::class, 'conteo'])
            ->name('administrador.notificaciones.conteo');
    });

    // Roles y permisos
    Route::prefix('roles-permisos')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\RolePermissionController::class, 'index'])
            ->name('administrador.roles.index');
        Route::post('/roles', [\App\Http\Controllers\Admin\RolePermissionController::class, 'storeRole'])
            ->name('administrador.roles.store');
        Route::put('/roles/{role}', [\App\Http\Controllers\Admin\RolePermissionController::class, 'updateRole'])
            ->name('administrador.roles.update');
        Route::delete('/roles/{role}', [\App\Http\Controllers\Admin\RolePermissionController::class, 'destroyRole'])
            ->name('administrador.roles.destroy');
        Route::post('/permisos', [\App\Http\Controllers\Admin\RolePermissionController::class, 'storePermission'])
            ->name('administrador.permisos.store');
        Route::put('/permisos/{permission}', [\App\Http\Controllers\Admin\RolePermissionController::class, 'updatePermission'])
            ->name('administrador.permisos.update');
        Route::delete('/permisos/{permission}', [\App\Http\Controllers\Admin\RolePermissionController::class, 'destroyPermission'])
            ->name('administrador.permisos.destroy');
        Route::put('/usuarios/{user}/roles', [\App\Http\Controllers\Admin\RolePermissionController::class, 'syncUserRoles'])
            ->name('administrador.usuarios.roles.sync');
    });

    // ========================================
    // RUTAS PARA EMPRESAS Y PLANES DE MARKETING (CONSOLIDADAS)
    // ========================================
    Route::prefix('empresas')->name('administrador.empresas.')->group(function () {
      // Rutas para mostrar y gestionar la empresa
Route::get('/', [App\Http\Controllers\Admin\EmpresaAdminController::class, 'index'])->name('index');
Route::get('/crear-para-usuario/{usuario_id}', [App\Http\Controllers\Admin\EmpresaAdminController::class, 'crearParaUsuario'])->name('crear-con-cuestionario');
Route::post('/guardar-para-usuario', [App\Http\Controllers\Admin\EmpresaAdminController::class, 'guardarParaUsuario'])->name('guardar-con-cuestionario');
Route::get('/{id}', [App\Http\Controllers\Admin\EmpresaAdminController::class, 'show'])->name('show');
Route::delete('/{id}', [App\Http\Controllers\Admin\EmpresaAdminController::class, 'destroy'])->name('destroy');
        // Rutas para el cuestionario
        Route::get('/{id}/cuestionario', [App\Http\Controllers\Admin\CuestionarioAdminController::class, 'show'])->name('cuestionario.show');
        Route::put('/{id}/cuestionario', [App\Http\Controllers\Admin\CuestionarioAdminController::class, 'update'])->name('cuestionario.update'); 
        
        // Rutas para el resumen ejecutivo
        Route::get('/{id}/editar-resumen', [App\Http\Controllers\Admin\ResumenAdminController::class, 'edit'])->name('editar-resumen');
        Route::put('/{id}/editar-resumen', [App\Http\Controllers\Admin\ResumenAdminController::class, 'update'])->name('update-resumen');
        Route::delete('/{id}/eliminar-resumen', [App\Http\Controllers\Admin\ResumenAdminController::class, 'destroy'])->name('eliminar-resumen');
        
        // Rutas para el reporte
        Route::get('/{id}/reporte', [App\Http\Controllers\Admin\ReporteController::class, 'show'])->name('reporte');
        Route::get('/{id}/reporte/pdf', [App\Http\Controllers\Admin\ReporteController::class, 'downloadPdf'])->name('reporte.pdf');

        // Rutas para planes de marketing
        Route::get('/{empresa}/crear-plan', [App\Http\Controllers\Admin\PlanMarketingController::class, 'create'])->name('crear-plan');
        Route::post('/{empresa}/planes-marketing', [App\Http\Controllers\Admin\PlanMarketingController::class, 'store'])->name('planes-marketing.store');
        Route::get('/planes-marketing/{planMarketing}', [App\Http\Controllers\Admin\PlanMarketingController::class, 'show'])->name('planes-marketing.show');

// Ruta para mostrar el formulario de ediciÃƒÆ’Ã‚Â³n de un plan
Route::get('/planes-marketing/{planMarketing}/edit', [App\Http\Controllers\Admin\PlanMarketingController::class, 'edit'])->name('planes-marketing.edit');
        // Ruta para actualizar el contenido del plan
    Route::put('/planes-marketing/{planMarketing}', [App\Http\Controllers\Admin\PlanMarketingController::class, 'update'])->name('planes-marketing.update');
        // Ruta para eliminar el plan
    Route::delete('/planes-marketing/{planMarketing}', [App\Http\Controllers\Admin\PlanMarketingController::class, 'destroy'])->name('planes-marketing.destroy');

    // Ruta para descargar el plan como PDF
    Route::get('/planes-marketing/{planMarketing}/descargar-pdf', [App\Http\Controllers\Admin\PlanMarketingController::class, 'downloadPDF'])->name('planes-marketing.download-pdf');

    // Ruta para descargar el plan como Word
    Route::get('/planes-marketing/{planMarketing}/descargar-word', [App\Http\Controllers\Admin\PlanMarketingController::class, 'downloadWord'])->name('planes-marketing.download-word');

    });

    // Rutas para publicaciones
    Route::get('/publicaciones/publicar', [\App\Http\Controllers\Admin\PublicacionController::class, 'index'])
        ->name('administrador.publicaciones.publicar');
    Route::post('/publicaciones/publicar', [\App\Http\Controllers\Admin\PublicacionController::class, 'store'])
        ->name('administrador.publicaciones.publicar.store');
    Route::post('/publicaciones/generar-copy', [\App\Http\Controllers\Admin\PublicacionController::class, 'generateCopy'])
        ->name('publicaciones.generate.copy');
        
});

// Rutas para empresas (solo para clientes)
Route::prefix('empresas')->middleware('auth')->name('empresas.')->group(function () {
    Route::get('/', [EmpresaController::class, 'index'])->name('index');
    Route::get('/create', [EmpresaController::class, 'create'])->name('create');
    Route::post('/', [EmpresaController::class, 'store'])->name('store');
    Route::get('/{id}', [EmpresaController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [EmpresaController::class, 'edit'])->name('edit');
    Route::put('/{id}', [EmpresaController::class, 'update'])->name('update');
    Route::delete('/{id}', [EmpresaController::class, 'destroy'])->name('destroy');
    
    // Rutas para cuestionario
    Route::get('/{id}/cuestionario', [CuestionarioController::class, 'show'])->name('cuestionario');
    Route::post('/{id}/cuestionario', [CuestionarioController::class, 'store'])->name('cuestionario.store');
});

// Rutas para clientes (historial de pagos)
Route::get('/clientes/historial-pagos', [PagoClienteController::class, 'historialPagos'])
    ->name('clientes.historial.pagos');
Route::get('/clientes/pagos/comprobante/{id}', [PagoClienteController::class, 'verComprobante'])
    ->name('clientes.pagos.comprobante');
Route::get('/clientes/pagos/descargar/{id}', [PagoClienteController::class, 'descargarComprobante'])
    ->name('clientes.pagos.descargar');

// Ruta para generar el resumen de una empresa especÃƒÆ’Ã‚Â­fica
Route::post('/empresas/{empresa}/generar-resumen', [ResumenController::class, 'generate'])->name('empresas.generarResumen');

// Ruta para obtener el plan contratado por el usuario
Route::get('/cliente/plan-contratado', [\App\Http\Controllers\Cliente\PlanController::class, 'getPlanContratado'])
    ->name('cliente.plan-contratado');
// Rutas para gestionar la ESTRUCTURA del cuestionario (solo admin)
Route::prefix('administrador/cuestionario/estructura')->name('administrador.cuestionario.estructura.')->middleware('auth')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\CuestionarioEstructuraController::class, 'index'])->name('index');
    Route::get('/crear', [App\Http\Controllers\Admin\CuestionarioEstructuraController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Admin\CuestionarioEstructuraController::class, 'store'])->name('store');
    Route::get('/{tema}/editar', [App\Http\Controllers\Admin\CuestionarioEstructuraController::class, 'edit'])->name('edit');
    Route::put('/{tema}', [App\Http\Controllers\Admin\CuestionarioEstructuraController::class, 'update'])->name('update');
    Route::delete('/{tema}', [App\Http\Controllers\Admin\CuestionarioEstructuraController::class, 'destroy'])->name('destroy');
    Route::post('/reorder', [App\Http\Controllers\Admin\CuestionarioEstructuraController::class, 'reorder'])->name('reorder');
});


























