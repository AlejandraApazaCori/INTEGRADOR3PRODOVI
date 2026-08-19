<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suscripciones', function (Blueprint $table) {
            $table->dateTime('vigencia_activada_at')->nullable()->after('fecha_fin');
        });

        Schema::table('campanias', function (Blueprint $table) {
            $table->foreignId('suscripcion_id')
                ->nullable()
                ->after('usuario_cliente_id')
                ->constrained('suscripciones')
                ->nullOnDelete();
        });

        $userIds = DB::table('campanias')->whereNull('suscripcion_id')->distinct()->pluck('usuario_cliente_id');
        foreach ($userIds as $userId) {
            $campanias = DB::table('campanias')->where('usuario_cliente_id', $userId)->orderByDesc('created_at')->orderByDesc('id')->get();
            $suscripciones = DB::table('suscripciones')->where('usuario_id', $userId)->orderByDesc('created_at')->orderByDesc('id')->get();

            foreach ($campanias as $index => $campania) {
                $suscripcion = $suscripciones[$index] ?? $suscripciones->first();
                if (! $suscripcion) {
                    continue;
                }

                DB::table('campanias')->where('id', $campania->id)->update(['suscripcion_id' => $suscripcion->id]);
                DB::table('suscripciones')->where('id', $suscripcion->id)->whereNull('vigencia_activada_at')->update([
                    'vigencia_activada_at' => $campania->fecha_inicio,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('campanias', function (Blueprint $table) {
            $table->dropConstrainedForeignId('suscripcion_id');
        });

        Schema::table('suscripciones', function (Blueprint $table) {
            $table->dropColumn('vigencia_activada_at');
        });
    }
};
