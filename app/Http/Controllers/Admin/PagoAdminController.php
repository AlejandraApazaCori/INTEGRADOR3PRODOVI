<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Suscripcion;
use App\Models\ComprobantePago;
use App\Models\Plan;
use App\Models\User;
use App\Models\Role;
use App\Models\LibelulaTransaction;
use App\Models\GoogleDriveReport;
use App\Exports\PagosExport;
use App\Exports\PagosChartReportExport;
use App\Services\GoogleDriveReportService;
use App\Services\PaymentConfirmationNotifier;
use App\Services\Libelula\LibelulaClient;
use App\Services\Libelula\LibelulaPaymentReconciler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Barryvdh\DomPDF\Facade\PDF;
use RuntimeException;
use Throwable;

class PagoAdminController extends Controller
{
    public function pagosRealizados(Request $request)
    {
        return redirect()->route('administrador.pagos.index', array_filter([
            'search' => $request->input('search'),
            'plan' => $request->input('plan'),
            'payment_status' => 'completado',
        ], static fn ($value) => $value !== null && $value !== ''));
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
        $planes = Plan::orderBy('nombre')->get();
        $perPage = (int) $request->input('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        $search = trim((string) $request->input('search', ''));
        $planId = $request->input('plan');
        $paymentStatus = $request->input('payment_status');
        $subscriptionStatus = $request->input('subscription_status');
        $method = $request->input('method');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $order = $request->input('order') === 'oldest' ? 'oldest' : 'newest';

        $paymentStatuses = ['pendiente', 'completado', 'rechazado', 'cancelado'];
        $subscriptionStatuses = ['activa', 'pendiente', 'finalizada', 'cancelada'];
        $methods = ['qr', 'fisico'];

        $summaryPayments = $this->buildAnalyticsQuery([
            'startDate' => Carbon::now()->startOfMonth()->toDateString(),
            'endDate' => Carbon::now()->endOfMonth()->toDateString(),
        ])->get();
        $summaryPlanCounts = $summaryPayments
            ->map(fn ($payment) => optional($payment->plan)->nombre ?? 'N/A')
            ->countBy()
            ->sortDesc();
        $paymentSummary = [
            'total_income' => number_format((float) $summaryPayments->where('estado', 'completado')->sum('monto'), 2, ',', '.').' '.(optional($summaryPayments->first())->moneda ?? 'BS'),
            'most_hired_plan' => $summaryPlanCounts->keys()->first() ?? 'N/A',
            'total_records' => $summaryPayments->count(),
        ];

        $pagosQuery = Pago::with(['usuario', 'plan', 'suscripcion', 'comprobantePago', 'libelulaTransaction'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('codigo_pago', 'like', "%{$search}%")
                        ->orWhere('provider_transaction_id', 'like', "%{$search}%")
                        ->orWhereHas('libelulaTransaction', fn ($transactionQuery) => $transactionQuery
                            ->where('identifier', 'like', "%{$search}%"))
                        ->orWhereHas('usuario', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });

                    if (ctype_digit($search)) {
                        $searchQuery->orWhere('id', (int) $search);
                    }
                });
            })
            ->when($planId, fn ($query) => $query->where('plan_id', $planId))
            ->when(in_array($paymentStatus, $paymentStatuses, true), fn ($query) => $query->where('estado', $paymentStatus))
            ->when(in_array($subscriptionStatus, $subscriptionStatuses, true), function ($query) use ($subscriptionStatus) {
                $query->whereHas('suscripcion', fn ($subscriptionQuery) => $subscriptionQuery->where('estado', $subscriptionStatus));
            })
            ->when(in_array($method, $methods, true), fn ($query) => $query->where('metodo', $method))
            ->when($dateFrom, fn ($query) => $query->whereDate('fecha_pago', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('fecha_pago', '<=', $dateTo))
            ->orderBy('id', $order === 'oldest' ? 'asc' : 'desc');

        $pagos = $pagosQuery
            ->paginate($perPage)
            ->appends($request->query());

        return view('administrador.pagos.index', compact(
            'planes',
            'pagos',
            'perPage',
            'paymentSummary'
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

    public function crearPagoQrManual(Request $request, LibelulaClient $client)
    {
        $validated = $request->validate([
            'usuario_id' => ['required', 'exists:users,id'],
            'plan_id' => ['required', 'exists:plan,id'],
            'document_type_code' => ['required', 'string', Rule::in(['1', '2', '3', '4', '5'])],
            'document_number' => ['required', 'string', 'max:50', 'regex:/^[0-9]+$/'],
            'document_complement' => ['nullable', 'string', 'max:20'],
            'document_extension' => ['nullable', 'string', 'max:20'],
            'business_name' => ['required', 'string', 'max:255'],
        ], [
            'document_number.regex' => 'El número de documento solo puede contener números.',
        ]);

        $user = User::findOrFail($validated['usuario_id']);
        $plan = Plan::where('activo', true)->findOrFail($validated['plan_id']);

        if (Str::upper((string) $plan->moneda) !== 'BS') {
            return response()->json(['message' => 'El pago QR está disponible únicamente para planes en bolivianos.'], 422);
        }

        LibelulaTransaction::query()
            ->where('usuario_id', $user->id)
            ->where('plan_id', $plan->id)
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired', 'expired_at' => now()]);

        $pending = LibelulaTransaction::query()
            ->where('usuario_id', $user->id)
            ->where('plan_id', $plan->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if ($pending) {
            return response()->json($this->manualQrTransactionData($pending));
        }

        $expiresAt = now()->addDay()->endOfMinute();
        $identifier = sprintf('PRODOVI-ADMIN-PLAN-%d-%d-%s', $user->id, $plan->id, Str::uuid());
        $transaction = LibelulaTransaction::create([
            'usuario_id' => $user->id,
            'plan_id' => $plan->id,
            'identifier' => $identifier,
            'customer_email' => $user->email,
            'customer_name' => $user->name,
            'document_type_code' => trim($validated['document_type_code']),
            'document_number' => trim($validated['document_number']),
            'document_complement' => $this->manualQrOptionalUppercase($validated['document_complement'] ?? null),
            'document_extension' => $this->manualQrOptionalUppercase($validated['document_extension'] ?? null),
            'business_name' => Str::upper(trim($validated['business_name'])),
            'description' => 'Suscripción al plan '.$plan->nombre,
            'currency' => 'BOB',
            'expected_amount' => $plan->precio,
            'status' => 'creating',
            'expires_at' => $expiresAt,
        ]);

        $callbackUrl = (string) config('services.libelula.callback_url');
        $callbackUrl = $callbackUrl !== '' ? $callbackUrl : route('pago.libelula.callback');
        $payload = [
            'email_cliente' => $user->email,
            'identificador' => $identifier,
            'fecha_vencimiento' => $expiresAt->format('Y-m-d H:i'),
            'descripcion' => $transaction->description,
            'callback_url' => $callbackUrl,
            'url_retorno' => route('administrador.pagos.index'),
            'numero_documento' => $transaction->document_number,
            'codigo_tipo_documento' => $transaction->document_type_code,
            'nombre_cliente' => trim((string) $user->name),
            'apellido_cliente' => '',
            'codigo_cliente' => (string) $user->id,
            'razon_social' => $transaction->business_name,
            'emite_factura' => true,
            'tipo_factura' => 'Servicios',
            'moneda' => 'BOB',
            'lineas_detalle_deuda' => [[
                'cantidad' => 1,
                'concepto' => $transaction->description,
                'costo_unitario' => (float) $plan->precio,
                'descuento_unitario' => 0,
                'codigo_producto' => (string) config('services.libelula.product_code', '1'),
            ]],
        ];

        $complement = collect([$transaction->document_complement, $transaction->document_extension])->filter()->implode(' ');
        if ($complement !== '') {
            $payload['complemento_documento'] = $complement;
        }

        $transaction->update(['request_payload' => $payload]);

        try {
            $response = $client->registerDebt($payload);
            $providerId = trim((string) ($response['id_transaccion'] ?? ''));
            $paymentUrl = trim((string) ($response['url_pasarela_pagos'] ?? ''));
            $qrUrl = trim((string) ($response['qr_simple_url'] ?? ''));

            if ($providerId === '' || ($paymentUrl === '' && $qrUrl === '')) {
                throw new RuntimeException('Libélula no devolvió los datos necesarios para pagar.');
            }

            $transaction->update([
                'libelula_transaction_id' => $providerId,
                'collection_code' => $response['codigo_recaudacion'] ?? null,
                'payment_url' => $paymentUrl ?: null,
                'qr_url' => $qrUrl ?: null,
                'response_payload' => $response,
                'status' => 'pending',
                'generated_at' => now(),
                'last_error' => null,
            ]);
        } catch (Throwable $exception) {
            $transaction->update(['status' => 'failed', 'last_error' => Str::limit($exception->getMessage(), 2000)]);
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($this->manualQrTransactionData($transaction->fresh()));
    }

    public function estadoPagoQrManual(LibelulaTransaction $transaction, LibelulaPaymentReconciler $reconciler)
    {
        if (in_array($transaction->status, ['pending', 'expired'], true)) {
            try {
                $reconciler->reconcile($transaction);
            } catch (Throwable $exception) {
                Log::warning('No se pudo conciliar el pago administrativo de Libélula.', [
                    'transaction_id' => $transaction->id,
                    'error' => $exception->getMessage(),
                ]);
                $transaction->update(['last_error' => Str::limit($exception->getMessage(), 2000)]);
            }
        }

        $transaction->refresh();
        if ($transaction->status === 'pending' && $transaction->expires_at?->isPast()) {
            $transaction->update(['status' => 'expired', 'expired_at' => now()]);
        }

        return response()->json($this->manualQrTransactionData($transaction->fresh()));
    }

    private function manualQrTransactionData(LibelulaTransaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'status' => $transaction->status,
            'identifier' => $transaction->identifier,
            'payment_url' => $transaction->payment_url,
            'qr_url' => $transaction->qr_url,
            'amount' => $transaction->expected_amount,
            'currency' => $transaction->currency,
            'expires_at' => $transaction->expires_at?->toIso8601String(),
            'status_url' => route('administrador.pagos.manual.libelula.estado', $transaction, false),
        ];
    }

    private function manualQrOptionalUppercase(mixed $value): ?string
    {
        $value = Str::upper(trim((string) $value));
        return $value !== '' ? $value : null;
    }

    public function storeManual(Request $request, PaymentConfirmationNotifier $paymentNotifier)
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

        if ($request->metodo === 'qr') {
            return back()->with('error', 'Los pagos QR deben confirmarse mediante Libélula antes de activar la suscripción.')->withInput();
        }

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

            $emailSent = $paymentNotifier->send($pago->fresh());
            $redirect = redirect()->route('administrador.pagos.index')
                ->with('success', $emailSent
                    ? 'Pago manual registrado y correo de confirmación enviado correctamente.'
                    : 'Pago manual registrado correctamente.');

            if (! $emailSent) {
                $redirect->with('error', 'El pago fue registrado, pero no se pudo enviar el correo de confirmación. Revisa la configuración de correo.');
            }

            return $redirect;
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
        $pago = Pago::with(['plan', 'suscripcion', 'usuario', 'comprobantePago', 'libelulaTransaction'])
            ->where('id', $id)
            ->firstOrFail();

        if (! $pago->comprobantePago) {
            $pago->setRelation('comprobantePago', ComprobantePago::firstOrCreate([
                'pago_id' => $pago->id,
            ]));
        }

        $html = view('clientes.comprobante-pago', compact('pago'))->render();

        return response()->json([
            'html' => $html,
            'view_url' => route('administrador.pagos.ver-recibo-pdf', $pago),
            'download_url' => route('administrador.pagos.descargar-recibo', $pago),
        ]);
    }

    public function exportReport(Request $request, string $report, string $destination)
    {
        abort_unless(in_array($report, ['filtered', 'general'], true), 404);
        abort_unless(in_array($destination, ['excel', 'pdf', 'drive'], true), 404);

        $pagos = $this->paymentReportQuery($request, $report === 'filtered')->get();
        $filters = $report === 'filtered'
            ? $request->only(['search', 'plan', 'payment_status', 'subscription_status', 'method', 'date_from', 'date_to', 'order'])
            : [];
        $summary = $this->paymentReportSummary($pagos);
        $reportTitle = $report === 'filtered' ? 'Reporte de pagos filtrados' : 'Listado general de pagos';
        $label = $report === 'filtered' ? 'pagos_filtrados' : 'pagos_generales';
        $fileName = $label.'_'.now()->format('Y_m_d_His').'.xlsx';
        $export = new PagosChartReportExport($pagos, $filters, $summary, $reportTitle);

        if ($destination === 'pdf') {
            $statusChart = $this->paymentDonutChartDataUri('Distribución por estado del pago', $export->statusStats());
            $methodChart = $this->paymentDonutChartDataUri('Distribución por método de pago', $export->methodStats());

            return PDF::loadView('pdf.pagos-reporte', compact(
                'pagos', 'filters', 'summary', 'reportTitle', 'statusChart', 'methodChart'
            ))
                ->setOption('isPhpEnabled', true)
                ->setPaper('a4', 'landscape')
                ->download($label.'_'.now()->format('Y_m_d_His').'.pdf');
        }

        if ($destination === 'excel') {
            return Excel::download($export, $fileName);
        }

        try {
            $request->validate([
                'folder_id' => ['nullable', 'string', 'max:255'],
                'new_folder' => ['nullable', 'string', 'max:80', 'regex:~^[\p{L}\p{N} _().-]+$~u'],
            ]);

            $drive = app(GoogleDriveReportService::class);
            $folderId = $drive->resolveTargetFolder($request->input('folder_id'), $request->input('new_folder'));
            $reportKey = 'payments_'.$report;
            $storedReport = GoogleDriveReport::where('report_key', $reportKey)->first();
            $contents = Excel::raw($export, ExcelFormat::XLSX);
            $uploaded = $drive->saveGoogleSheet($fileName, $contents, $folderId, $storedReport?->file_id);
            $drive->positionPaymentReportCharts($uploaded['id']);

            GoogleDriveReport::updateOrCreate(
                ['report_key' => $reportKey],
                [
                    'file_id' => $uploaded['id'],
                    'folder_id' => $folderId,
                    'file_name' => $uploaded['name'],
                    'web_view_link' => $uploaded['url'],
                ]
            );

            return back()->with('drive_success', [
                'message' => $storedReport
                    ? 'El reporte de pagos se actualizó y conservó el mismo enlace en Google Sheets.'
                    : 'El reporte de pagos se creó correctamente en Google Sheets.',
                'url' => $uploaded['url'],
            ]);
        } catch (Throwable $exception) {
            Log::error('No se pudo crear el reporte de pagos en Google Drive.', [
                'report' => $report,
                'error' => $exception->getMessage(),
            ]);

            return back()->with('drive_error', 'No se pudo guardar el reporte de pagos en Google Drive. Inténtalo nuevamente.');
        }
    }

    public function driveReportFolders(Request $request)
    {
        try {
            $request->validate(['report' => ['nullable', 'in:filtered,general']]);
            $data = app(GoogleDriveReportService::class)->listTargetFolders();
            $storedReport = $request->filled('report')
                ? GoogleDriveReport::where('report_key', 'payments_'.$request->report)->first()
                : null;
            $currentFolder = null;

            if ($storedReport) {
                $folder = collect([$data['root'], ...$data['folders']])->firstWhere('id', $storedReport->folder_id);
                $currentFolder = [
                    'id' => $storedReport->folder_id,
                    'name' => $folder['name'] ?? 'Carpeta no disponible',
                    'file_url' => $storedReport->web_view_link,
                ];
            }

            return response()->json([...$data, 'current_folder' => $currentFolder]);
        } catch (Throwable $exception) {
            Log::error('No se pudieron consultar las carpetas de reportes de pagos.', [
                'error' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'No se pudieron consultar las carpetas de Google Drive.'], 500);
        }
    }

    private function paymentReportQuery(Request $request, bool $applyFilters)
    {
        $query = Pago::with(['usuario', 'plan', 'suscripcion', 'comprobantePago', 'libelulaTransaction']);

        if ($applyFilters) {
            $search = trim((string) $request->input('search', ''));
            $paymentStatuses = ['pendiente', 'completado', 'rechazado', 'cancelado'];
            $subscriptionStatuses = ['activa', 'pendiente', 'finalizada', 'cancelada'];
            $methods = ['qr', 'fisico'];
            $query
                ->when($search !== '', function ($paymentQuery) use ($search) {
                    $paymentQuery->where(function ($searchQuery) use ($search) {
                        $searchQuery->where('codigo_pago', 'like', "%{$search}%")
                            ->orWhere('provider_transaction_id', 'like', "%{$search}%")
                            ->orWhereHas('libelulaTransaction', fn ($transactionQuery) => $transactionQuery
                                ->where('identifier', 'like', "%{$search}%"))
                            ->orWhereHas('usuario', fn ($userQuery) => $userQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%"));

                        if (ctype_digit($search)) {
                            $searchQuery->orWhere('id', (int) $search);
                        }
                    });
                })
                ->when($request->filled('plan'), fn ($paymentQuery) => $paymentQuery->where('plan_id', $request->plan))
                ->when(in_array($request->payment_status, $paymentStatuses, true), fn ($paymentQuery) => $paymentQuery->where('estado', $request->payment_status))
                ->when(in_array($request->subscription_status, $subscriptionStatuses, true), fn ($paymentQuery) => $paymentQuery
                    ->whereHas('suscripcion', fn ($subscriptionQuery) => $subscriptionQuery->where('estado', $request->subscription_status)))
                ->when(in_array($request->method, $methods, true), fn ($paymentQuery) => $paymentQuery->where('metodo', $request->method))
                ->when($request->filled('date_from'), fn ($paymentQuery) => $paymentQuery->whereDate('fecha_pago', '>=', $request->date_from))
                ->when($request->filled('date_to'), fn ($paymentQuery) => $paymentQuery->whereDate('fecha_pago', '<=', $request->date_to));
        }

        $direction = $applyFilters && $request->input('order') === 'oldest' ? 'asc' : 'desc';

        return $query->orderBy('id', $direction);
    }

    private function paymentReportSummary($pagos): array
    {
        $planCounts = $pagos->map(fn ($pago) => optional($pago->plan)->nombre ?? 'N/A')->countBy()->sortDesc();

        return [
            'total_income' => number_format((float) $pagos->where('estado', 'completado')->sum('monto'), 2, ',', '.').' '.(optional($pagos->first())->moneda ?? 'BS'),
            'most_hired_plan' => $planCounts->keys()->first() ?? 'N/A',
            'most_hired_plan_count' => $planCounts->first() ?? 0,
            'total_records' => $pagos->count(),
        ];
    }

    private function paymentDonutChartDataUri(string $title, array $stats): ?string
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
            $legend .= '<rect x="245" y="'.($legendY - 11).'" width="13" height="13" rx="3" fill="'.$color.'"/>';
            $legend .= '<text x="267" y="'.$legendY.'" font-family="DejaVu Sans, sans-serif" font-size="13" fill="#374151">'.$escape($legendText).'</text>';
            $offset += $length;
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="640" height="'.$height.'" viewBox="0 0 640 '.$height.'">'
            .'<rect width="640" height="'.$height.'" rx="14" fill="#ffffff"/>'
            .'<text x="20" y="25" font-family="DejaVu Sans, sans-serif" font-size="16" font-weight="700" fill="#374151">'.$escape($title).'</text>'
            .'<circle cx="125" cy="'.$centerY.'" r="'.$radius.'" fill="none" stroke="#edf0ea" stroke-width="38"/>'
            .$segments
            .'<text x="125" y="'.($centerY - 3).'" text-anchor="middle" font-family="DejaVu Sans, sans-serif" font-size="25" font-weight="700" fill="#31382b">'.$total.'</text>'
            .'<text x="125" y="'.($centerY + 19).'" text-anchor="middle" font-family="DejaVu Sans, sans-serif" font-size="11" fill="#737a70">registros</text>'
            .$legend
            .'</svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    public function descargarComprobante($id)
    {
        [$rutaCompletaParaDescarga, $nombreDescarga] = $this->obtenerArchivoComprobante($id);

        return response()->download($rutaCompletaParaDescarga, $nombreDescarga);
    }

    public function visualizarComprobantePdf($id)
    {
        [$rutaCompleta, $nombreArchivo] = $this->obtenerArchivoComprobante($id);

        return response()->file($rutaCompleta, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$nombreArchivo.'"',
        ]);
    }

    private function obtenerArchivoComprobante($id): array
    {
        $pago = Pago::with(['plan', 'suscripcion', 'usuario', 'comprobantePago'])
            ->where('id', $id)
            ->firstOrFail();

        $comprobante = $pago->comprobantePago ?: ComprobantePago::firstOrCreate([
            'pago_id' => $pago->id,
        ]);
        $rutaRelativa = 'comprobantes_pago/comprobante-'.$comprobante->numero_formateado.'.pdf';
        $templateUpdatedAt = filemtime(resource_path('views/clientes/comprobante-pago-pdf.blade.php'));
        $storedPdfIsOutdated = \Storage::disk('public')->exists($rutaRelativa)
            && \Storage::disk('public')->lastModified($rutaRelativa) < $templateUpdatedAt;

        if (! \Storage::disk('public')->exists($rutaRelativa) || $storedPdfIsOutdated) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
                'clientes.comprobante-pago-pdf',
                compact('pago', 'comprobante')
            )->setPaper('letter', 'portrait');
            \Storage::disk('public')->put($rutaRelativa, $pdf->output());
        }

        return [
            \Storage::disk('public')->path($rutaRelativa),
            'comprobante-'.$comprobante->numero_formateado.'.pdf',
        ];
    }
}
