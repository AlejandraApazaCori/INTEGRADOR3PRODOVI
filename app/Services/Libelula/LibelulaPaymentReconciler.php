<?php

namespace App\Services\Libelula;

use App\Models\ComprobantePago;
use App\Models\LibelulaTransaction;
use App\Models\Pago;
use App\Models\Suscripcion;
use App\Services\PaymentConfirmationNotifier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class LibelulaPaymentReconciler
{
    public function __construct(
        private readonly LibelulaClient $client,
        private readonly PaymentConfirmationNotifier $paymentNotifier,
    ) {}

    public function reconcile(LibelulaTransaction $transaction): bool
    {
        if ($transaction->status === 'paid') {
            $this->notifyPayment($transaction);
            return true;
        }

        $verification = $this->client->verifyPayment($transaction);
        $debt = $this->findVerifiedDebt($verification, $transaction);

        if (! $debt || ! filter_var($debt['pagado'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        $this->assertVerifiedDebt($transaction, $debt);

        DB::transaction(function () use ($transaction, $debt) {
            $locked = LibelulaTransaction::query()->lockForUpdate()->findOrFail($transaction->id);

            if ($locked->status === 'paid') {
                return;
            }

            $existingPayment = Pago::query()
                ->where('provider', 'libelula')
                ->where('provider_transaction_id', $locked->libelula_transaction_id)
                ->first();

            if (! $existingPayment) {
                $startedAt = $this->paymentDate($debt);
                $subscription = Suscripcion::query()->create([
                    'usuario_id' => $locked->usuario_id,
                    'plan_id' => $locked->plan_id,
                    'estado' => 'activa',
                    'fecha_inicio' => $startedAt,
                    'fecha_fin' => $this->subscriptionEnd($startedAt, $locked->plan->periodo_facturacion),
                    'metodo_pago' => 'qr',
                ]);

                $payment = Pago::query()->create([
                    'usuario_id' => $locked->usuario_id,
                    'suscripcion_id' => $subscription->id,
                    'plan_id' => $locked->plan_id,
                    'monto' => $locked->expected_amount,
                    'moneda' => $locked->currency === 'BOB' ? 'BS' : $locked->currency,
                    'metodo' => 'qr',
                    'estado' => 'completado',
                    'fecha_pago' => $startedAt,
                    'provider' => 'libelula',
                    'provider_transaction_id' => $locked->libelula_transaction_id,
                    'provider_reference' => $debt['codigo_recaudacion'] ?? $locked->collection_code,
                ]);

                ComprobantePago::query()->firstOrCreate(['pago_id' => $payment->id]);
            }

            $locked->update([
                'status' => 'paid',
                'confirmed_at' => now(),
                'collection_code' => $debt['codigo_recaudacion'] ?? $locked->collection_code,
                'response_payload' => array_merge($locked->response_payload ?? [], ['verification' => $debt]),
                'last_error' => null,
            ]);
        });

        $this->notifyPayment($transaction);

        return true;
    }

    private function notifyPayment(LibelulaTransaction $transaction): void
    {
        $payment = Pago::query()
            ->where('provider', 'libelula')
            ->where('provider_transaction_id', $transaction->libelula_transaction_id)
            ->first();

        if ($payment) {
            $this->paymentNotifier->send($payment);
        }
    }

    private function findVerifiedDebt(array $verification, LibelulaTransaction $transaction): ?array
    {
        $data = $verification['datos'] ?? null;

        if (is_array($data) && ! array_is_list($data)) {
            return $data;
        }

        if (! is_array($data)) {
            return null;
        }

        return collect($data)->first(fn ($item) => is_array($item)
            && (($item['identificador'] ?? null) === $transaction->identifier
                || ($item['id_transaccion'] ?? null) === $transaction->libelula_transaction_id));
    }

    private function assertVerifiedDebt(LibelulaTransaction $transaction, array $debt): void
    {
        if ((string) ($debt['identificador'] ?? '') !== $transaction->identifier) {
            throw new RuntimeException('El identificador verificado no coincide.');
        }

        $providerId = trim((string) ($debt['id_transaccion'] ?? ''));
        if ($providerId !== '' && $providerId !== $transaction->libelula_transaction_id) {
            throw new RuntimeException('La transaccion verificada no coincide.');
        }

        $amount = (float) ($debt['monto_pagado'] ?? $debt['valor_total'] ?? -1);
        if (abs($amount - (float) $transaction->expected_amount) > 0.001) {
            throw new RuntimeException('El monto verificado no coincide.');
        }

        $currency = Str::upper(trim((string) ($debt['moneda'] ?? '')));
        if ($currency !== '' && $currency !== Str::upper((string) $transaction->currency)) {
            throw new RuntimeException('La moneda verificada no coincide.');
        }
    }

    private function paymentDate(array $debt): Carbon
    {
        try {
            return ! empty($debt['fecha_pago']) ? Carbon::parse($debt['fecha_pago']) : now();
        } catch (\Throwable) {
            return now();
        }
    }

    private function subscriptionEnd(Carbon $startedAt, ?string $period): Carbon
    {
        $normalized = Str::ascii(Str::lower(trim((string) $period)));

        return match ($normalized) {
            'trimestre' => $startedAt->copy()->addMonths(3),
            'semestre' => $startedAt->copy()->addMonths(6),
            'ano' => $startedAt->copy()->addYear(),
            default => $startedAt->copy()->addMonth(),
        };
    }
}
