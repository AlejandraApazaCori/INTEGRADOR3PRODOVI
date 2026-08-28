<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campanias', function (Blueprint $table) {
            $table->unsignedSmallInteger('reuniones_cliente_por_mes')->default(0)->after('usuario_cliente_id');
        });

        Schema::create('reuniones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campania_id')->constrained('campanias')->cascadeOnDelete();
            $table->foreignId('creador_id')->constrained('users')->cascadeOnDelete();
            $table->string('titulo', 150);
            $table->text('descripcion')->nullable();
            $table->string('plataforma', 30)->default('meet');
            $table->string('enlace', 2048);
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->string('estado', 20)->default('agendada');
            $table->string('origen', 20)->default('administrador');
            $table->timestamps();
            $table->index(['campania_id', 'fecha_inicio']);
        });

        Schema::create('reunion_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reunion_id')->constrained('reuniones')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['reunion_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reunion_user');
        Schema::dropIfExists('reuniones');

        Schema::table('campanias', function (Blueprint $table) {
            $table->dropColumn('reuniones_cliente_por_mes');
        });
    }
};
