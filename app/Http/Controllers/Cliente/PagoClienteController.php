<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\CodigoPago;
use App\Models\ComprobantePago;
use App\Models\LibelulaEvent;
use App\Models\LibelulaTransaction;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\Suscripcion;
use App\Services\Libelula\LibelulaClient;
use App\Services\Libelula\LibelulaPaymentReconciler;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class PagoClienteController extends Controller
{
    public function show(Request $request, $plan)
    {
        $planSlug = strtolower($plan);
        $planModel = Plan::where('nombre', str_replace('-', ' ', $planSlug))->firstOrFail();
        $pendingTransaction = LibelulaTransaction::query()
            ->where('usuario_id', $request->user()->id)
            ->where('plan_id', $planModel->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();
        $pendingPhysicalPayment = Pago::with('codigoPago')
            ->where('usuario_id', $request->user()->id)
            ->where('plan_id', $planModel->id)
            ->where('metodo', 'fisico')
            ->where('estado', 'pendiente')
            ->latest('id')
            ->first();

        return view('clientes.pago', [
            'plan' => $planSlug,
            'planPrecio' => $planModel->precio,
            'planMoneda' => $planModel->moneda,
            'planPeriodo' => $planModel->periodo_facturacion,
            'planNombre' => $planModel->nombre,
            'libelulaTransaction' => $pendingTransaction
                ? $this->safeTransactionData($pendingTransaction)
                : null,
            'physicalPayment' => $pendingPhysicalPayment?->codigoPago
                ? $this->safePhysicalPaymentData($pendingPhysicalPayment)
                : null,
        ]);
    }

    public function procesarPago(Request $request, $plan)
    {
        $request->validate(['metodo_pago' => 'required|in:fisico']);
        $planModel = Plan::where('nombre', str_replace('-', ' ', strtolower($plan)))->firstOrFail();
        $usuario = $request->user();

        DB::beginTransaction();

        try {
            DB::table('users')->where('id', $usuario->id)->lockForUpdate()->first();

            $pendingPayments = Pago::with(['codigoPago', 'suscripcion'])
                ->where('usuario_id', $usuario->id)
                ->where('metodo', 'fisico')
                ->where('estado', 'pendiente')
                ->latest('id')
                ->get();
            $pendingPayment = $pendingPayments->first();

            if ($pendingPayment?->codigoPago && (int) $pendingPayment->plan_id === (int) $planModel->id) {
                $pendingPayments->skip(1)->each(fn (Pago $payment) => $this->deletePendingPhysicalPayment($payment));
                DB::commit();

                return response()->json([
                    'success' => true,
                    'metodo' => 'fisico',
                    'existing' => true,
                    'message' => 'Ya tienes un codigo pendiente para este plan.',
                    ...$this->safePhysicalPaymentData($pendingPayment),
                ]);
            }

            $pendingPayments->each(fn (Pago $payment) => $this->deletePendingPhysicalPayment($payment));

            $fechaInicio = Carbon::now();
            $suscripcion = Suscripcion::create([
                'usuario_id' => $usuario->id,
                'plan_id' => $planModel->id,
                'estado' => 'pendiente',
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaInicio->copy()->addMonth(),
                'metodo_pago' => 'fisico',
            ]);
            $codigo = CodigoPago::generarCodigoUnico();
            $pago = Pago::create([
                'usuario_id' => $usuario->id,
                'suscripcion_id' => $suscripcion->id,
                'plan_id' => $planModel->id,
                'codigo_pago' => $codigo,
                'monto' => $planModel->precio,
                'moneda' => $planModel->moneda,
                'metodo' => 'fisico',
                'estado' => 'pendiente',
            ]);

            CodigoPago::create([
                'codigo' => $codigo,
                'usuario_id' => $usuario->id,
                'pago_id' => $pago->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'metodo' => 'fisico',
                'codigo' => $codigo,
                'payment_id' => $pago->id,
                'download_url' => route('pago.fisico.codigo.pdf', $pago, false),
                'downloaded' => false,
                'message' => 'El codigo fue generado correctamente.',
            ]);
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Error al procesar pago fisico.', [
                'error' => $exception->getMessage(),
                'usuario' => $usuario->id,
                'plan' => $plan,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrio un error al procesar el pago.',
            ], 500);
        }
    }

    public function crearPagoQr(Request $request, $plan, LibelulaClient $client)
    {
        $validated = $request->validate([
            'document_type_code' => ['required', 'string', Rule::in(['1', '2', '3', '4', '5'])],
            'document_number' => ['required', 'string', 'max:50', 'regex:/^[0-9]+$/'],
            'document_complement' => ['nullable', 'string', 'max:20'],
            'document_extension' => ['nullable', 'string', 'max:20'],
            'business_name' => ['required', 'string', 'max:255'],
        ], ['document_number.regex' => 'El numero de documento solo puede contener numeros.']);

        $planModel = Plan::where('nombre', str_replace('-', ' ', strtolower($plan)))->firstOrFail();
        $user = $request->user();

        if ($planModel->moneda !== 'BS') {
            return response()->json(['message' => 'El pago QR esta disponible para planes en bolivianos.'], 422);
        }

        LibelulaTransaction::query()
            ->where('usuario_id', $user->id)
            ->where('plan_id', $planModel->id)
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired', 'expired_at' => now()]);

        $pending = LibelulaTransaction::query()
            ->where('usuario_id', $user->id)
            ->where('plan_id', $planModel->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if ($pending) {
            return response()->json($this->safeTransactionData($pending));
        }

        $expiresAt = now()->addDay()->endOfMinute();
        $identifier = sprintf('PRODOVI-PLAN-%d-%d-%s', $user->id, $planModel->id, Str::uuid());
        $transaction = LibelulaTransaction::create([
            'usuario_id' => $user->id,
            'plan_id' => $planModel->id,
            'identifier' => $identifier,
            'customer_email' => $user->email,
            'customer_name' => $user->name,
            'document_type_code' => trim($validated['document_type_code']),
            'document_number' => trim($validated['document_number']),
            'document_complement' => $this->optionalUppercase($validated['document_complement'] ?? null),
            'document_extension' => $this->optionalUppercase($validated['document_extension'] ?? null),
            'business_name' => Str::upper(trim($validated['business_name'])),
            'description' => 'Suscripcion al plan '.$planModel->nombre,
            'currency' => 'BOB',
            'expected_amount' => $planModel->precio,
            'status' => 'creating',
            'expires_at' => $expiresAt,
        ]);

        $callbackUrl = (string) config('services.libelula.callback_url');
        $returnUrl = (string) config('services.libelula.return_url');
        $callbackUrl = $callbackUrl !== '' ? $callbackUrl : route('pago.libelula.callback');
        $returnUrl = $returnUrl !== '' ? $returnUrl : route('pago.libelula.retorno');
        $payload = [
            'email_cliente' => $user->email,
            'identificador' => $identifier,
            'fecha_vencimiento' => $expiresAt->format('Y-m-d H:i'),
            'descripcion' => $transaction->description,
            'callback_url' => $callbackUrl,
            'url_retorno' => $this->returnUrl($returnUrl, $transaction->id),
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
                'costo_unitario' => (float) $planModel->precio,
                'descuento_unitario' => 0,
                'codigo_producto' => (string) config('services.libelula.product_code', '1'),
            ]],
        ];
        $complement = collect([$transaction->document_complement, $transaction->document_extension])
            ->filter()->implode(' ');

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
                throw new RuntimeException('Libelula no devolvio los datos necesarios para pagar.');
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
            $transaction->update([
                'status' => 'failed',
                'last_error' => Str::limit($exception->getMessage(), 2000),
            ]);

            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($this->safeTransactionData($transaction->fresh()));
    }

    public function estadoPagoQr(Request $request, LibelulaTransaction $transaction, LibelulaPaymentReconciler $reconciler)
    {
        abort_unless((int) $transaction->usuario_id === (int) $request->user()->id, 403);

        if (in_array($transaction->status, ['pending', 'expired'], true)) {
            try {
                $reconciler->reconcile($transaction);
            } catch (Throwable $exception) {
                Log::warning('No se pudo conciliar el pago de Libelula.', [
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

        return response()->json($this->safeTransactionData($transaction->fresh()));
    }

    public function callbackLibelula(Request $request, LibelulaPaymentReconciler $reconciler)
    {
        $providerId = trim((string) $request->input('transaction_id', ''));
        $transaction = $providerId !== ''
            ? LibelulaTransaction::where('libelula_transaction_id', $providerId)->first()
            : null;
        $event = LibelulaEvent::create([
            'libelula_transaction_record_id' => $transaction?->id,
            'libelula_transaction_id' => $providerId ?: null,
            'identifier' => $transaction?->identifier,
            'event_type' => 'payment_success',
            'source' => 'callback',
            'payload' => $request->all(),
            'processing_status' => $transaction ? 'received' : 'unmatched',
            'received_at' => now(),
        ]);

        if (! $transaction) {
            $event->update(['processed_at' => now()]);
            return response()->json(['received' => true]);
        }

        if ($transaction->status === 'paid') {
            $event->update(['processing_status' => 'duplicate', 'processed_at' => now()]);
            return response()->json(['received' => true]);
        }

        try {
            $paid = $reconciler->reconcile($transaction);
            $event->update([
                'processing_status' => $paid ? 'processed' : 'pending_verification',
                'processed_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $event->update([
                'processing_status' => 'failed',
                'error_message' => Str::limit($exception->getMessage(), 2000),
                'processed_at' => now(),
            ]);
        }

        return response()->json(['received' => true]);
    }

    public function retornoLibelula(Request $request)
    {
        $transaction = LibelulaTransaction::query()
            ->whereKey($request->integer('transaction'))
            ->where('usuario_id', $request->user()->id)
            ->firstOrFail();

        return redirect()->route('clientes.pago', [
            'plan' => Str::slug($transaction->plan->nombre),
            'transaction' => $transaction->id,
        ]);
    }

    public function descargarCodigoFisico(Request $request, Pago $pago)
    {
        abort_unless((int) $pago->usuario_id === (int) $request->user()->id, 403);
        abort_unless($pago->metodo === 'fisico', 404);

        $pago->load(['codigoPago', 'plan', 'usuario']);
        abort_unless($pago->codigoPago, 404, 'Codigo de pago no encontrado.');

        $pdf = PDF::loadView('clientes.codigo-pago-pdf', [
            'pago' => $pago,
            'codigoPago' => $pago->codigoPago,
        ]);

        $pago->codigoPago->update(['descargado_at' => now()]);

        return $pdf->download('codigo-pago-'.$pago->codigoPago->codigo.'.pdf');
    }

    public function estadoPago()
    {
        $pagoPendiente = Pago::with(['codigoPago', 'plan', 'suscripcion'])
            ->where('usuario_id', Auth::id())
            ->where('estado', 'pendiente')
            ->whereHas('suscripcion', fn ($query) => $query->where('estado', 'pendiente'))
            ->latest('id')
            ->first();

        if (! $pagoPendiente) {
            return redirect()->route('clientes.home')->with('error', 'No tienes pagos pendientes');
        }

        return view('clientes.estado-pago', [
            'suscripcion' => $pagoPendiente->suscripcion,
            'pagoPendiente' => $pagoPendiente,
            'codigoPago' => $pagoPendiente->codigoPago,
        ]);
    }

    public function historialPagos()
    {
        $pagos = Pago::with(['plan', 'suscripcion', 'comprobantePago'])
            ->where('usuario_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('clientes.historialpagos', compact('pagos'));
    }

    public function verComprobante($id)
    {
        $pago = Pago::with(['plan', 'suscripcion', 'usuario', 'comprobantePago'])
            ->where('id', $id)
            ->where('usuario_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'html' => view('clientes.comprobante-pago', compact('pago'))->render(),
        ]);
    }

    public function descargarComprobante($id)
    {
        $pago = Pago::with(['plan', 'suscripcion', 'usuario', 'comprobantePago'])
            ->where('id', $id)
            ->where('usuario_id', Auth::id())
            ->firstOrFail();
        $comprobante = $pago->comprobantePago;

        abort_unless($comprobante, 404, 'Comprobante no encontrado para este pago.');

        $rutaRelativa = 'comprobantes_pago/comprobante-'.$comprobante->numero_formateado.'.pdf';
        $templateUpdatedAt = filemtime(resource_path('views/clientes/comprobante-pago-pdf.blade.php'));
        $storedPdfIsOutdated = Storage::disk('public')->exists($rutaRelativa)
            && Storage::disk('public')->lastModified($rutaRelativa) < $templateUpdatedAt;

        if (! Storage::disk('public')->exists($rutaRelativa) || $storedPdfIsOutdated) {
            $pdf = PDF::loadView('clientes.comprobante-pago-pdf', compact('pago', 'comprobante'))
                ->setPaper('letter', 'portrait');
            Storage::disk('public')->put($rutaRelativa, $pdf->output());
        }

        return response()->download(
            Storage::disk('public')->path($rutaRelativa),
            'comprobante-'.$comprobante->numero_formateado.'.pdf'
        );
    }

    private function safeTransactionData(LibelulaTransaction $transaction): array
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
            'status_url' => route('pago.libelula.estado', $transaction, false),
        ];
    }

    private function safePhysicalPaymentData(Pago $payment): array
    {
        return [
            'payment_id' => $payment->id,
            'codigo' => $payment->codigoPago->codigo,
            'download_url' => route('pago.fisico.codigo.pdf', $payment, false),
            'downloaded' => $payment->codigoPago->descargado_at !== null,
        ];
    }

    private function deletePendingPhysicalPayment(Pago $payment): void
    {
        $subscription = $payment->suscripcion;

        $payment->codigoPago?->delete();
        $payment->delete();

        if ($subscription && $subscription->estado === 'pendiente') {
            $subscription->delete();
        }
    }

    private function optionalUppercase(mixed $value): ?string
    {
        $value = Str::upper(trim((string) $value));

        return $value !== '' ? $value : null;
    }

    private function returnUrl(string $baseUrl, int $transactionId): string
    {
        return $baseUrl.(str_contains($baseUrl, '?') ? '&' : '?')
            .http_build_query(['transaction' => $transactionId]);
    }
}
