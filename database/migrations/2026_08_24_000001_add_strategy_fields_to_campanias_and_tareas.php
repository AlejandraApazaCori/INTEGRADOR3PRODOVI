<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campanias', function (Blueprint $table) {
            $table->text('objetivo_general')->nullable()->after('descripcion');
            $table->text('publico_objetivo')->nullable()->after('objetivo_general');
            $table->text('mensaje_principal')->nullable()->after('publico_objetivo');
            $table->string('tono_comunicacion', 120)->nullable()->after('mensaje_principal');
            $table->json('canales')->nullable()->after('tono_comunicacion');
            $table->json('indicadores')->nullable()->after('canales');
            $table->string('modo_creacion', 20)->default('manual')->after('indicadores');
            $table->boolean('es_borrador')->default(false)->after('modo_creacion');
            $table->json('ai_generation_metadata')->nullable()->after('es_borrador');
            $table->foreignId('disenador_id')->nullable()->after('community_manager_id')
                ->constrained('users')->nullOnDelete();
        });

        Schema::table('tareas', function (Blueprint $table) {
            $table->text('entregable')->nullable()->after('descripcion');
            $table->boolean('requiere_aprobacion')->default(false)->after('prioridad');
        });
    }

    public function down(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            $table->dropColumn(['entregable', 'requiere_aprobacion']);
        });

        Schema::table('campanias', function (Blueprint $table) {
            $table->dropForeign(['disenador_id']);
            $table->dropColumn([
                'objetivo_general',
                'publico_objetivo',
                'mensaje_principal',
                'tono_comunicacion',
                'canales',
                'indicadores',
                'modo_creacion',
                'es_borrador',
                'ai_generation_metadata',
                'disenador_id',
            ]);
        });
    }
};
