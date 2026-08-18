<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Suscripcion;
use App\Models\ComprobantePago;
use App\Models\Plan;
use App\Models\User;
use App\Models\Role;
use App\Exports\PagosExport;
use App\Services\PaymentConfirmationNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\PDF;

class PagoAdminController extends Controller
{
    public function pagosRealizados(Request $request)
    {
        $search = $request->input('search');
        $planId = $request->input('plan');
        $planes = Plan::all();

        $query = Pago::has('usuario')
            ->has('plan')
            ->has('suscripcion')
            ->with(['usuario', 'plan', 'suscripcion'])
            ->where('estado', 'completado')
            ->orderBy('fecha_pago', 'desc');

        if ($search) {
            $query->whereHas('usuario', function ($userQuery) use ($search) {
                $userQuery->where('name', 'like', "%{$search}%");
            });
        }

        if ($planId) {
            $query->whereHas('plan', function ($planQuery) use ($planId) {
                $planQuery->where('id', $planId);
            });
        }
        $pagos = $query->paginate(10)->through(function ($pago) {
            return [
                'id' => $pago->id,
                'usuario' => optional($pago->usuario)->name ?? 'N/A',
                'tipo_pago' => $pago->metodo,
                'plan' => optional($pago->plan)->nombre ?? 'N/A',
                'monto' => $pago->monto . ' ' . $pago->moneda,
                'fecha_inicio' => optional($pago->suscripcion)->fecha_inicio ? $pago->suscripcion->fecha_inicio->format('d/m/Y') : 'N/A',
                'fecha_fin' => optional($pago->suscripcion)->fecha_fin ? $pago->suscripcion->fecha_fin->format('d/m/Y') : 'N/A',
                'estado' => optional($pago->suscripcion)->estado ?? 'N/A',
            ];
        });

        if ($request->ajax()) {
            return view('administrador.pagos._results', compact('pagos'))->render();
        }

        return view('administrador.pagos.realizados', compact('pagos', 'planes'));
    }

    public function pagosPendientesFisicos(Request $request)
    {
        $search = $request->input('search');

        $query = Pago::with(['usuario', 'plan', 'codigoPago'])
            ->where('estado', 'pendiente')
            ->where('metodo', 'fisico')
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('usuario', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%");
                })->orWhereHas('codigoPago', function ($codigoQuery) use ($search) {
                    $codigoQuery->where('codigo', 'like', "%{$search}%");
                });
            });
        }

        $pagos = $query->paginate(10);

        foreach ($pagos as $pago) {
            $pago->update(['visto' => true]);
        }

        return view('administrador.pagos.pendientes-fisicos', compact('pagos'));
    }

    public function pagosFinalizadosSinRenovacion(Request $request)
    {
        Suscripcion::where('estado', 'activa')
            ->where('fecha_fin', '<', now())
            ->update([
                'estado' => 'finalizada',
                'fecha_cancelacion' => now(),
            ]);

        $search = $request->input('search');

        $query = Pago::has('usuario')
            ->has('plan')
            ->has('suscripcion')
            ->with(['usuario', 'plan', 'suscripcion'])
            ->whereHas('suscripcion', function ($query) {
                $query->whereIn('estado', ['finalizada', 'cancelada']);
            })
            ->where('estado', 'completado')
            ->orderBy('fecha_pago', 'desc');

        if ($search) {
            $query->whereHas('usuario', function ($userQuery) use ($search) {
                $userQuery->where('name', 'like', "%{$search}%");
            });
        }

        $pagos = $query->get()->map(function ($pago) {
            return [
                'id' => $pago->id,
                'usuario' => optional($pago->usuario)->name ?? 'N/A',
                'tipo_pago' => $pago->metodo,
                'plan' => optional($pago->plan)->nombre ?? 'N/A',
                'monto' => $pago->monto . ' ' . $pago->moneda,
                'fecha_inicio' => optional($pago->suscripcion)->fecha_inicio ? $pago->suscripcion->fecha_inicio->format('d/m/Y') : 'N/A',
                'fecha_fin' => optional($pago->suscripcion)->fecha_fin ? $pago->suscripcion->fecha_fin->format('d/m/Y') : 'N/A',
                'fecha_cancelacion' => optional($pago->suscripcion)->fecha_cancelacion ? $pago->suscripcion->fecha_cancelacion->format('d/m/Y H:i') : null,
                'estado' => optional($pago->suscripcion)->estado ?? 'N/A',
            ];
        });

        if ($request->ajax()) {
            return view('administrador.pagos._finalizados_results', compact('pagos'))->render();
        }

        return view('administrador.pagos.finalizados-sin-renovacion', compact('pagos'));
    }

    public function aprobarPagoFisico($pagoId, PaymentConfirmationNotifier $paymentNotifier)
    {
        DB::beginTransaction();
        try {
            $pago = Pago::findOrFail($pagoId);

            $pago->update([
                'estado' => 'completado',
                'aprobado_por' => optional(Auth::user())->id,
                'fecha_aprobacion' => now(),
                'fecha_pago' => now(),
            ]);

            $pago->suscripcion->update(['estado' => 'activa']);

            if ($pago->codigoPago) {
                $pago->codigoPago->update([
                    'utilizado' => true,
                    'fecha_utilizacion' => now(),
                ]);
            }

            ComprobantePago::create([
                'pago_id' => $pago->id,
            ]);

            DB::commit();

            $paymentNotifier->send($pago->fresh());

            return back()->with('success', 'Pago aprobado y comprobante generado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al aprobar pago físico: ' . $e->getMessage());

            return back()->with('error', 'Ocurrió un error al aprobar el pago. Por favor, inténtelo de nuevo.');
        }
    }

    public function reenviarCorreoConfirmacion(Pago $pago, PaymentConfirmationNotifier $paymentNotifier)
    {
        if ($pago->estado !== 'completado') {
            return back()->with('error', 'Solo se puede reenviar el correo de pagos completados.');
        }

        if (! filled($pago->usuario?->email)) {
            return back()->with('error', 'El cliente no tiene un correo electrónico registrado.');
        }

        if (! $paymentNotifier->resend($pago)) {
            return back()->with('error', 'No se pudo reenviar el correo. Revisa la configuración de correo o los registros del sistema.');
        }

        return back()->with('success', 'Correo de confirmación reenviado a '.$pago->usuario->email.'.');
    }

    public function eliminarPagoFisicoPendiente(Pago $pago)
    {
        DB::beginTransaction();

        try {
            $pago = Pago::with(['codigoPago', 'suscripcion'])
                ->lockForUpdate()
                ->findOrFail($pago->id);

            if ($pago->metodo !== 'fisico' || $pago->estado !== 'pendiente') {
                DB::rollBack();

                return back()->with('error', 'Solo se pueden eliminar pagos fisicos pendientes.');
            }

            $suscripcion = $pago->suscripcion;

            $pago->codigoPago?->delete();
            $pago->delete();

            if ($suscripcion && $suscripcion->estado === 'pendiente') {
                $suscripcion->delete();
            }

            DB::commit();

            return back()->with('success', 'El pago fisico pendiente y su codigo fueron eliminados correctamente.');
        } catch (\Throwable $exception) {
            DB::rollBack();
            \Log::error('Error al eliminar un pago fisico pendiente.', [
                'pago_id' => $pago->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with('error', 'No se pudo eliminar el pago fisico pendiente.');
        }
    }

    public function index(Request $request)
    {
        $countActivos = Suscripcion::where('estado', 'activa')
            ->where('fecha_fin', '>', now())
            ->count();

        $countPendientes = Pago::where('estado', 'pendiente')
            ->where('metodo', 'fisico')
            ->count();

        $countFinalizados = Suscripcion::whereIn('estado', ['finalizada', 'cancelada'])
            ->count();

        $planes = Plan::where('activo', true)->get();
        $perPage = (int) $request->input('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        $pagos = Pago::with(['usuario', 'plan', 'suscripcion', 'comprobantePago'])
            ->orderByDesc('id')
            ->paginate($perPage)
            ->appends($request->query());

        return view('administrador.pagos.index', compact(
            'countActivos',
            'countPendientes',
            'countFinalizados',
            'planes',
            'pagos',
            'perPage'
        ));
    }

    public function analiticas()
    {
        $planes = Plan::where('activo', true)->get();
        $defaultStartDate = Carbon::now()->startOfMonth()->toDateString();
        $defaultEndDate = Carbon::now()->endOfMonth()->toDateString();

        return view('administrador.pagos.analiticas', compact('planes', 'defaultStartDate', 'defaultEndDate'));
    }

    public function createManual()
    {
        $planes = Plan::where('activo', true)->get();
        $usuarios = User::whereHas('roles', function ($q) {
            $q->where('nombre_rol', 'Cliente');
        })->get();

        return view('administrador.pagos.create_manual', compact('planes', 'usuarios'));
    }

    public function storeManual(Request $request)
    {
        $rules = [
            'create_new_user' => 'required|in:0,1',
            'plan_id' => 'required|exists:plan,id',
            'metodo' => 'required|in:fisico,qr',
            'monto' => 'required|numeric|min:0',
            'fecha_inicio' => 'required|date',
        ];

        if ($request->create_new_user == '1') {
            $rules['name'] = 'required|string|max:255';
            $rules['email'] = 'required|string|email|max:255|unique:users';
            $rules['password'] = 'required|string|min:8|confirmed';
            $rules['phone'] = 'nullable|string|max:20';
        } else {
            $rules['usuario_id'] = 'required|exists:users,id';
        }

        $request->validate($rules);

        DB::beginTransaction();
        try {
            $usuarioId = $request->usuario_id;

            if ($request->create_new_user == '1') {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => \Hash::make($request->password),
                    'phone' => $request->phone,
                ]);

                $rolCliente = Role::where('nombre_rol', 'Cliente')->first();
                if ($rolCliente) {
                    $user->roles()->attach($rolCliente->id);
                }

                $usuarioId = $user->id;
            }

            $plan = Plan::findOrFail($request->plan_id);
            $fechaInicio = Carbon::parse($request->fecha_inicio);
            $fechaFin = $fechaInicio->copy()->addMonth();

            if ($plan->periodo_facturacion === 'anual') {
                $fechaFin = $fechaInicio->copy()->addYear();
            } elseif ($plan->periodo_facturacion === 'trimestral') {
                $fechaFin = $fechaInicio->copy()->addMonths(3);
            } elseif ($plan->periodo_facturacion === 'semestral') {
                $fechaFin = $fechaInicio->copy()->addMonths(6);
            }

            $suscripcion = Suscripcion::create([
                'usuario_id' => $usuarioId,
                'plan_id' => $request->plan_id,
                'estado' => 'activa',
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'metodo_pago' => $request->metodo,
            ]);

            $pago = Pago::create([
                'usuario_id' => $usuarioId,
                'suscripcion_id' => $suscripcion->id,
                'plan_id' => $request->plan_id,
                'monto' => $request->monto,
                'moneda' => $plan->moneda ?? 'BS',
                'metodo' => $request->metodo,
                'estado' => 'completado',
                'aprobado_por' => Auth::id(),
                'fecha_aprobacion' => now(),
                'fecha_pago' => now(),
            ]);

            ComprobantePago::create([
                'pago_id' => $pago->id,
            ]);

            DB::commit();

            return redirect()->route('administrador.pagos.index')->with('success', 'Pago manual registrado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al registrar pago manual: ' . $e->getMessage());
            return back()->with('error', 'Error al registrar el pago: ' . $e->getMessage())->withInput();
        }
    }

    public function cancelarSuscripcion($pagoId)
    {
        $pago = Pago::findOrFail($pagoId);

        $pago->suscripcion->update([
            'estado' => 'cancelada',
            'fecha_fin' => now(),
            'fecha_cancelacion' => now(),
        ]);

        return back()->with('success', 'Suscripción cancelada correctamente');
    }

    public function reactivarSuscripcion($pagoId)
    {
        $pago = Pago::findOrFail($pagoId);

        $pago->suscripcion->update([
            'estado' => 'activa',
            'fecha_inicio' => now(),
            'fecha_fin' => now()->addMonth(),
            'fecha_cancelacion' => null,
        ]);

        return back()->with('success', 'Suscripción reactivada correctamente');
    }

    private function getFilteredPaymentsForReport(Request $request)
    {
        $filters = [
            'clientName' => $request->input('clientName'),
            'plan' => $request->input('plan'),
            'subscriptionStatus' => $request->input('subscriptionStatus'),
            'startDate' => $request->input('startDate'),
            'endDate' => $request->input('endDate'),
        ];

        return $this->buildAnalyticsQuery($filters)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function buscarPagos(Request $request)
    {
        try {
            $filters = [
                'clientName' => $request->input('clientName'),
                'plan' => $request->input('plan'),
                'subscriptionStatus' => $request->input('subscriptionStatus'),
                'startDate' => $request->input('startDate'),
                'endDate' => $request->input('endDate'),
            ];
            $page = (int) $request->input('page', 1);
            $perPage = 10;

            $allPagos = $this->buildAnalyticsQuery($filters)->get();

            $totalIncome = $allPagos->where('estado', 'completado')->sum('monto');
            $moneda = $allPagos->first()->moneda ?? 'BS';
            $planCounts = $allPagos->map(function ($pago) {
                return $pago->plan ? $pago->plan->nombre : 'N/A';
            })->countBy()->sortDesc();

            $mostHiredPlan = $planCounts->isNotEmpty() ? $planCounts->keys()->first() : 'N/A';
            $mostHiredPlanCount = $planCounts->isNotEmpty() ? $planCounts->first() : 0;

            $subscriptionStatusDistribution = $allPagos->map(function ($pago) {
                return $pago->suscripcion ? $pago->suscripcion->estado : 'N/A';
            })->countBy()->all();

            $planDistribution = $allPagos->map(function ($pago) {
                return $pago->plan ? $pago->plan->nombre : 'N/A';
            })->countBy()->all();

            $analytics = $this->buildAnalyticsCharts($allPagos, $filters);

            $total = $allPagos->count();
            $totalPages = max(1, (int) ceil(max($total, 1) / $perPage));
            $offset = ($page - 1) * $perPage;

            $results = $allPagos->slice($offset, $perPage)->values()->map(function ($pago) {
                return [
                    'id' => $pago->id,
                    'usuario' => $pago->usuario ? $pago->usuario->name : 'N/A',
                    'plan' => $pago->plan ? $pago->plan->nombre : 'N/A',
                    'monto' => $pago->monto . ' ' . $pago->moneda,
                    'fecha_inicio' => $pago->suscripcion ? $pago->suscripcion->fecha_inicio->format('d/m/Y') : 'N/A',
                    'fecha_fin' => $pago->suscripcion ? $pago->suscripcion->fecha_fin->format('d/m/Y') : 'N/A',
                    'estado' => $pago->suscripcion ? $pago->suscripcion->estado : 'N/A',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $results,
                'summary' => [
                    'total_income' => number_format($totalIncome, 2, ',', '.') . ' ' . $moneda,
                    'most_hired_plan' => $mostHiredPlan,
                    'most_hired_plan_count' => $mostHiredPlanCount,
                    'total_records' => $total,
                ],
                'charts' => [
                    'status_distribution' => $subscriptionStatusDistribution,
                    'plan_distribution' => $planDistribution,
                    'payment_status_distribution' => $analytics['payment_status_distribution'],
                    'monthly_income' => $analytics['monthly_income'],
                    'income_by_plan' => $analytics['income_by_plan'],
                    'active_subscriptions_evolution' => $analytics['active_subscriptions_evolution'],
                    'top_clients' => $analytics['top_clients'],
                    'income_comparison' => $analytics['income_comparison'],
                    'payment_method_distribution' => $analytics['payment_method_distribution'],
                    'income_by_day' => $analytics['income_by_day'],
                ],
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total' => $total,
                    'per_page' => $perPage,
                ],
                'filters' => $filters,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en buscarPagos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la búsqueda: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function buildAnalyticsCharts($pagos, array $filters): array
    {
        $completedPayments = $pagos->where('estado', 'completado')->values();
        [$monthKeys, $monthLabels] = $this->resolveAnalyticsMonths($pagos, $filters);

        $monthlyIncome = array_fill_keys($monthKeys, 0.0);
        foreach ($completedPayments as $pago) {
            $paymentDate = $this->resolvePaymentDate($pago);
            if (!$paymentDate) {
                continue;
            }

            $monthKey = $paymentDate->format('Y-m');
            if (array_key_exists($monthKey, $monthlyIncome)) {
                $monthlyIncome[$monthKey] += (float) $pago->monto;
            }
        }

        $incomeByPlan = $completedPayments
            ->groupBy(function ($pago) {
                return optional($pago->plan)->nombre ?? 'N/A';
            })
            ->map(function ($planPayments) {
                return round((float) $planPayments->sum('monto'), 2);
            })
            ->sortDesc()
            ->all();

        $paymentStatuses = collect([
            'pendiente' => 0,
            'aprobado' => 0,
            'rechazado' => 0,
            'cancelado' => 0,
        ]);

        foreach ($pagos as $pago) {
            $normalizedStatus = match ($pago->estado) {
                'completado' => 'aprobado',
                'cancelado' => 'cancelado',
                'rechazado' => 'rechazado',
                default => 'pendiente',
            };
            $paymentStatuses[$normalizedStatus] = $paymentStatuses[$normalizedStatus] + 1;
        }

        $activeSubscriptionsEvolution = [];
        foreach ($monthKeys as $monthKey) {
            $monthEnd = Carbon::createFromFormat('Y-m', $monthKey)->endOfMonth();

            $activeSubscriptionsEvolution[$monthKey] = $pagos->filter(function ($pago) use ($monthEnd) {
                $suscripcion = $pago->suscripcion;
                if (!$suscripcion || !$suscripcion->fecha_inicio || !$suscripcion->fecha_fin) {
                    return false;
                }

                $started = $suscripcion->fecha_inicio->lte($monthEnd);
                $notFinished = $suscripcion->fecha_fin->gte($monthEnd);
                $notCancelledBeforeMonthEnd = !$suscripcion->fecha_cancelacion || $suscripcion->fecha_cancelacion->gt($monthEnd);

                return $started && $notFinished && $notCancelledBeforeMonthEnd;
            })->pluck('suscripcion_id')->filter()->unique()->count();
        }

        $topClients = $completedPayments
            ->groupBy(function ($pago) {
                return optional($pago->usuario)->name ?? 'N/A';
            })
            ->map(function ($clientPayments) {
                return round((float) $clientPayments->sum('monto'), 2);
            })
            ->sortDesc()
            ->take(5)
            ->all();

        $comparisonAnchor = !empty($filters['endDate'])
            ? Carbon::parse($filters['endDate'])
            : Carbon::now();
        $currentMonthKey = $comparisonAnchor->format('Y-m');
        $previousMonthKey = $comparisonAnchor->copy()->subMonth()->format('Y-m');

        $paymentMethodDistribution = $pagos
            ->groupBy(function ($pago) {
                return $this->normalizePaymentMethodLabel($pago->metodo);
            })
            ->map(function ($methodPayments) {
                return $methodPayments->count();
            })
            ->sortDesc()
            ->all();

        $incomeByDay = collect(range(1, 31))->mapWithKeys(function ($day) {
            return [$day => 0.0];
        })->all();

        foreach ($completedPayments as $pago) {
            $paymentDate = $this->resolvePaymentDate($pago);
            if (!$paymentDate) {
                continue;
            }

            $day = (int) $paymentDate->format('j');
            $incomeByDay[$day] += (float) $pago->monto;
        }

        return [
            'monthly_income' => [
                'labels' => array_values($monthLabels),
                'values' => array_map(fn ($value) => round($value, 2), array_values($monthlyIncome)),
            ],
            'income_by_plan' => $incomeByPlan,
            'payment_status_distribution' => $paymentStatuses->all(),
            'active_subscriptions_evolution' => [
                'labels' => array_values($monthLabels),
                'values' => array_values($activeSubscriptionsEvolution),
            ],
            'top_clients' => $topClients,
            'income_comparison' => [
                'labels' => [
                    $this->formatMonthLabel($previousMonthKey),
                    $this->formatMonthLabel($currentMonthKey),
                ],
                'values' => [
                    round($monthlyIncome[$previousMonthKey] ?? 0, 2),
                    round($monthlyIncome[$currentMonthKey] ?? 0, 2),
                ],
            ],
            'payment_method_distribution' => $paymentMethodDistribution,
            'income_by_day' => [
                'labels' => array_map(fn ($day) => (string) $day, array_keys($incomeByDay)),
                'values' => array_map(fn ($value) => round($value, 2), array_values($incomeByDay)),
            ],
        ];
    }

    private function resolveAnalyticsMonths($pagos, array $filters): array
    {
        $start = !empty($filters['startDate']) ? Carbon::parse($filters['startDate'])->startOfMonth() : null;
        $end = !empty($filters['endDate']) ? Carbon::parse($filters['endDate'])->startOfMonth() : null;

        if (!$start || !$end) {
            $dates = $pagos->map(fn ($pago) => $this->resolvePaymentDate($pago))->filter()->sort()->values();
            $firstDate = $dates->first();
            $lastDate = $dates->last();

            if (!$start) {
                $start = $firstDate ? $firstDate->copy()->startOfMonth() : Carbon::now()->startOfMonth();
            }

            if (!$end) {
                $end = $lastDate ? $lastDate->copy()->startOfMonth() : $start->copy();
            }
        }

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy(), $start->copy()];
        }

        $monthKeys = [];
        $monthLabels = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m');
            $monthKeys[] = $key;
            $monthLabels[] = $this->formatMonthLabel($key);
            $cursor->addMonth();
        }

        return [$monthKeys, $monthLabels];
    }

    private function resolvePaymentDate(Pago $pago): ?Carbon
    {
        if ($pago->fecha_pago) {
            return $pago->fecha_pago->copy();
        }

        return $pago->created_at ? $pago->created_at->copy() : null;
    }

    private function formatMonthLabel(string $monthKey): string
    {
        return Carbon::createFromFormat('Y-m', $monthKey)
            ->locale('es')
            ->translatedFormat('M Y');
    }

    private function normalizePaymentMethodLabel(?string $method): string
    {
        return match (strtolower((string) $method)) {
            'qr' => 'QR',
            'fisico' => 'Efectivo',
            'transferencia' => 'Transferencia',
            'tarjeta' => 'Tarjeta',
            '', 'null' => 'Sin definir',
            default => ucfirst((string) $method),
        };
    }

    public function descargarPDF(Request $request)
    {
        try {
            $pagos = $this->getFilteredPaymentsForReport($request);
            $filters = $request->all();

            $totalIncome = $pagos->where('estado', 'completado')->sum('monto');
            $moneda = $pagos->first()->moneda ?? 'BS';
            $planCounts = $pagos->map(function ($pago) {
                return $pago->plan ? $pago->plan->nombre : 'N/A';
            })->countBy()->sortDesc();

            $summary = [
                'total_income' => number_format($totalIncome, 2, ',', '.') . ' ' . $moneda,
                'most_hired_plan' => $planCounts->isNotEmpty() ? $planCounts->keys()->first() : 'N/A',
                'most_hired_plan_count' => $planCounts->isNotEmpty() ? $planCounts->first() : 0,
                'total_records' => $pagos->count(),
            ];

            $pdf = PDF::loadView('administrador.pagos.pdf', compact('pagos', 'filters', 'summary'));
            return $pdf->download('reporte_pagos_' . date('d_m_Y_H_i_s') . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Error al generar PDF: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }

    public function descargarExcel(Request $request)
    {
        try {
            $pagos = $this->getFilteredPaymentsForReport($request);
            $filters = $request->all();

            $totalIncome = $pagos->where('estado', 'completado')->sum('monto');
            $moneda = $pagos->first()->moneda ?? 'BS';
            $planCounts = $pagos->map(function ($pago) {
                return $pago->plan ? $pago->plan->nombre : 'N/A';
            })->countBy()->sortDesc();

            $summary = [
                'total_income' => number_format($totalIncome, 2, ',', '.') . ' ' . $moneda,
                'most_hired_plan' => $planCounts->isNotEmpty() ? $planCounts->keys()->first() : 'N/A',
                'most_hired_plan_count' => $planCounts->isNotEmpty() ? $planCounts->first() : 0,
                'total_records' => $pagos->count(),
            ];

            return Excel::download(new PagosExport($pagos, $filters, $summary), 'reporte_pagos_' . date('d_m_Y_H_i_s') . '.xlsx');
        } catch (\Exception $e) {
            \Log::error('Error al generar Excel: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el Excel: ' . $e->getMessage());
        }
    }

    private function getMonthlyPaymentsForReport()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        return Pago::with(['usuario', 'plan', 'suscripcion'])
            ->where(function ($query) use ($currentMonth, $currentYear) {
                $query->where(function ($dateQuery) use ($currentMonth, $currentYear) {
                    $dateQuery->whereNotNull('fecha_pago')
                        ->whereMonth('fecha_pago', $currentMonth)
                        ->whereYear('fecha_pago', $currentYear);
                })->orWhere(function ($dateQuery) use ($currentMonth, $currentYear) {
                    $dateQuery->whereNull('fecha_pago')
                        ->whereMonth('created_at', $currentMonth)
                        ->whereYear('created_at', $currentYear);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function descargarPDFMensual()
    {
        try {
            $pagos = $this->getMonthlyPaymentsForReport();
            $filters = ['monthly_report' => true];

            $totalIncome = $pagos->where('estado', 'completado')->sum('monto');
            $moneda = $pagos->first()->moneda ?? 'BS';
            $planCounts = $pagos->map(function ($pago) {
                return $pago->plan ? $pago->plan->nombre : 'N/A';
            })->countBy()->sortDesc();

            $summary = [
                'total_income' => number_format($totalIncome, 2, ',', '.') . ' ' . $moneda,
                'most_hired_plan' => $planCounts->isNotEmpty() ? $planCounts->keys()->first() : 'N/A',
                'most_hired_plan_count' => $planCounts->isNotEmpty() ? $planCounts->first() : 0,
                'total_records' => $pagos->count(),
                'monthly_report' => Carbon::now()->format('F Y'),
            ];

            $pdf = PDF::loadView('administrador.pagos.pdf', compact('pagos', 'filters', 'summary'));
            return $pdf->download('reporte_mensual_pagos_' . date('d_m_Y_H_i_s') . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Error al generar PDF mensual: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el PDF mensual: ' . $e->getMessage());
        }
    }

    public function descargarExcelMensual()
    {
        try {
            $pagos = $this->getMonthlyPaymentsForReport();
            $filters = ['monthly_report' => true];

            $totalIncome = $pagos->where('estado', 'completado')->sum('monto');
            $moneda = $pagos->first()->moneda ?? 'BS';
            $planCounts = $pagos->map(function ($pago) {
                return $pago->plan ? $pago->plan->nombre : 'N/A';
            })->countBy()->sortDesc();

            $summary = [
                'total_income' => number_format($totalIncome, 2, ',', '.') . ' ' . $moneda,
                'most_hired_plan' => $planCounts->isNotEmpty() ? $planCounts->keys()->first() : 'N/A',
                'most_hired_plan_count' => $planCounts->isNotEmpty() ? $planCounts->first() : 0,
                'total_records' => $pagos->count(),
                'monthly_report' => Carbon::now()->format('F Y'),
            ];

            return Excel::download(new PagosExport($pagos, $filters, $summary), 'reporte_mensual_pagos_' . date('d_m_Y_H_i_s') . '.xlsx');
        } catch (\Exception $e) {
            \Log::error('Error al generar Excel mensual: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el Excel mensual: ' . $e->getMessage());
        }
    }

    private function buildAnalyticsQuery(array $filters)
    {
        $query = Pago::with(['usuario', 'plan', 'suscripcion']);

        if (!empty($filters['clientName'])) {
            $query->whereHas('usuario', function ($userQuery) use ($filters) {
                $userQuery->where('name', 'like', "%{$filters['clientName']}%");
            });
        }

        if (!empty($filters['plan'])) {
            $query->where('plan_id', $filters['plan']);
        }

        if (!empty($filters['subscriptionStatus']) && $filters['subscriptionStatus'] !== 'all') {
            switch ($filters['subscriptionStatus']) {
                case 'active':
                    $query->whereHas('suscripcion', function ($susQuery) {
                        $susQuery->where('estado', 'activa');
                    });
                    break;
                case 'completed':
                    $query->whereHas('suscripcion', function ($susQuery) {
                        $susQuery->where('estado', 'finalizada');
                    });
                    break;
                case 'cancelled':
                    $query->whereHas('suscripcion', function ($susQuery) {
                        $susQuery->where('estado', 'cancelada');
                    });
                    break;
            }
        }

        if (!empty($filters['startDate'])) {
            $query->where(function ($dateQuery) use ($filters) {
                $dateQuery->whereDate('fecha_pago', '>=', $filters['startDate'])
                    ->orWhere(function ($fallbackQuery) use ($filters) {
                        $fallbackQuery->whereNull('fecha_pago')
                            ->whereDate('created_at', '>=', $filters['startDate']);
                    });
            });
        }

        if (!empty($filters['endDate'])) {
            $query->where(function ($dateQuery) use ($filters) {
                $dateQuery->whereDate('fecha_pago', '<=', $filters['endDate'])
                    ->orWhere(function ($fallbackQuery) use ($filters) {
                        $fallbackQuery->whereNull('fecha_pago')
                            ->whereDate('created_at', '<=', $filters['endDate']);
                    });
            });
        }

        return $query;
    }

    public function verComprobante($id)
    {
        $pago = Pago::with(['plan', 'suscripcion', 'usuario', 'comprobantePago'])
            ->where('id', $id)
            ->firstOrFail();

        $html = view('clientes.comprobante-pago', compact('pago'))->render();

        return response()->json(['html' => $html]);
    }

    public function descargarComprobante($id)
    {
        $pago = Pago::with(['plan', 'suscripcion', 'usuario', 'comprobantePago'])
            ->where('id', $id)
            ->firstOrFail();

        $comprobante = $pago->comprobantePago;
        if (!$comprobante) {
            $comprobante = ComprobantePago::create([
                'pago_id' => $pago->id,
            ]);
        }

        $rutaRelativa = 'comprobantes_pago/comprobante-' . $comprobante->numero_formateado . '.pdf';

        if (!\Storage::disk('public')->exists($rutaRelativa)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('clientes.comprobante-pago-pdf', compact('pago', 'comprobante'));
            \Storage::disk('public')->put($rutaRelativa, $pdf->output());
        }

        $rutaCompletaParaDescarga = \Storage::disk('public')->path($rutaRelativa);
        $nombreDescarga = 'comprobante-' . $comprobante->numero_formateado . '.pdf';

        return response()->download($rutaCompletaParaDescarga, $nombreDescarga);
    }
}
