<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campania_mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campania_id')->constrained('campanias')->cascadeOnDelete();
            $table->foreignId('remitente_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tarea_id')->nullable()->constrained('tareas')->nullOnDelete();
            $table->string('audiencia', 20);
            $table->text('contenido');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['campania_id', 'audiencia', 'created_at'], 'campania_mensajes_feed_index');
        });

        Schema::create('campania_mensaje_destinatarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mensaje_id')->constrained('campania_mensajes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('leido_at')->nullable();
            $table->timestamps();

            $table->unique(['mensaje_id', 'user_id'], 'campania_mensaje_destinatario_unique');
            $table->index(['user_id', 'leido_at'], 'campania_mensaje_lectura_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campania_mensaje_destinatarios');
        Schema::dropIfExists('campania_mensajes');
    }
};
