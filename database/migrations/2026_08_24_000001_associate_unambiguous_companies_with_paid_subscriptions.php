<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $empresas = DB::table('empresas')
            ->whereNull('suscripcion_id')
            ->whereNull('deleted_at')
            ->select('id', 'usuario_id')
            ->get();

        foreach ($empresas as $empresa) {
            $suscripciones = DB::table('suscripciones as s')
                ->where('s.usuario_id', $empresa->usuario_id)
                ->where('s.estado', 'activa')
                ->whereNull('s.deleted_at')
                ->where(function ($query) {
                    $query->whereNull('s.vigencia_activada_at')
                        ->orWhere('s.fecha_fin', '>', now());
                })
                ->whereExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('pagos as p')
                        ->whereColumn('p.suscripcion_id', 's.id')
                        ->where('p.estado', 'completado')
                        ->whereNull('p.deleted_at');
                })
                ->whereNotExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('empresas as empresa_asociada')
                        ->whereColumn('empresa_asociada.suscripcion_id', 's.id');
                })
                ->orderByDesc('s.id')
                ->limit(2)
                ->pluck('s.id');

            // Solo reparar cuando la relación es inequívoca. Si existen dos
            // compras disponibles, debe elegirlas una persona desde el formulario.
            if ($suscripciones->count() === 1) {
                DB::table('empresas')
                    ->where('id', $empresa->id)
                    ->whereNull('suscripcion_id')
                    ->update(['suscripcion_id' => $suscripciones->first()]);
            }
        }
    }

    public function down(): void
    {
        // La asociación representa datos reales y no debe eliminarse al revertir.
    }
};
