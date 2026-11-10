<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            $table->boolean('revision_cliente_habilitada')->default(false)->after('visible_cliente');
        });

        Schema::table('tarea_archivos', function (Blueprint $table) {
            $table->foreignId('revisado_equipo_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revisado_equipo_at')->nullable();
            $table->string('revision_cliente_estado', 20)->default('pendiente');
            $table->foreignId('revisado_cliente_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revisado_cliente_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tarea_archivos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('revisado_equipo_por');
            $table->dropColumn('revisado_equipo_at');
            $table->dropConstrainedForeignId('revisado_cliente_por');
            $table->dropColumn(['revision_cliente_estado', 'revisado_cliente_at']);
        });
        Schema::table('tareas', fn (Blueprint $table) => $table->dropColumn('revision_cliente_habilitada'));
    }
};
