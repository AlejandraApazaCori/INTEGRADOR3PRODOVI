<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->foreignId('suscripcion_id')
                ->nullable()
                ->after('usuario_id')
                ->constrained('suscripciones')
                ->nullOnDelete();
        });

        $userIds = DB::table('empresas')->whereNull('suscripcion_id')->distinct()->pluck('usuario_id');

        foreach ($userIds as $userId) {
            $empresas = DB::table('empresas')
                ->where('usuario_id', $userId)
                ->whereNull('suscripcion_id')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get();
            $suscripciones = DB::table('suscripciones')
                ->where('usuario_id', $userId)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get();

            foreach ($empresas as $index => $empresa) {
                $suscripcion = $suscripciones[$index] ?? null;
                if ($suscripcion) {
                    DB::table('empresas')->where('id', $empresa->id)->update(['suscripcion_id' => $suscripcion->id]);
                }
            }
        }

        Schema::table('empresas', function (Blueprint $table) {
            $table->unique('suscripcion_id');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropUnique(['suscripcion_id']);
            $table->dropConstrainedForeignId('suscripcion_id');
        });
    }
};
