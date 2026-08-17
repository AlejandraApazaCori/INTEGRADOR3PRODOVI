<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $payments = DB::table('pagos')
            ->where('metodo', 'fisico')
            ->where('estado', 'pendiente')
            ->whereNull('deleted_at')
            ->orderBy('usuario_id')
            ->orderByDesc('id')
            ->get(['id', 'usuario_id', 'suscripcion_id']);

        $latestByUser = [];
        $deletedAt = now();

        foreach ($payments as $payment) {
            if (! isset($latestByUser[$payment->usuario_id])) {
                $latestByUser[$payment->usuario_id] = $payment->id;
                continue;
            }

            DB::table('codigos_pagos')->where('pago_id', $payment->id)->delete();
            DB::table('pagos')->where('id', $payment->id)->update([
                'deleted_at' => $deletedAt,
                'updated_at' => $deletedAt,
            ]);

            $hasOtherPayments = DB::table('pagos')
                ->where('suscripcion_id', $payment->suscripcion_id)
                ->whereNull('deleted_at')
                ->exists();

            if (! $hasOtherPayments) {
                DB::table('suscripciones')
                    ->where('id', $payment->suscripcion_id)
                    ->where('estado', 'pendiente')
                    ->whereNull('deleted_at')
                    ->update([
                        'deleted_at' => $deletedAt,
                        'updated_at' => $deletedAt,
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Los codigos obsoletos eliminados no deben restaurarse.
    }
};
