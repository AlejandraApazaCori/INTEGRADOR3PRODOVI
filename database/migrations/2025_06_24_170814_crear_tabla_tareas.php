<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tareas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 100);
            $table->text('descripcion');
            $table->date('fecha_inicio');
            $table->date('fecha_limite');
            $table->enum('estado', ['no_iniciado', 'pendiente', 'en_curso', 'entregado', 'reformular', 'aprobado', 'publicado'])->default('no_iniciado');
            $table->enum('prioridad', ['baja', 'media', 'alta', 'urgente'])->default('media');

            // Relación con la campaña
            $table->unsignedBigInteger('campania_id');
            $table->foreign('campania_id')->references('id')->on('campanias')->onDelete('cascade');

            // Usuario que crea la tarea
            $table->unsignedBigInteger('creador_id');
            $table->foreign('creador_id')->references('id')->on('users');

            // Usuario asignado a la tarea
            $table->unsignedBigInteger('asignado_id');
            $table->foreign('asignado_id')->references('id')->on('users');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};
