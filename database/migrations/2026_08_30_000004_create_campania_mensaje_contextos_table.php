<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campania_mensaje_contextos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campania_id')->constrained('campanias')->cascadeOnDelete();
            $table->foreignId('creado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nombre', 100);
            $table->timestamps();

            $table->unique(['campania_id', 'nombre'], 'campania_mensaje_contexto_nombre_unique');
        });

        Schema::table('campania_mensajes', function (Blueprint $table) {
            $table->foreignId('contexto_id')->nullable()->after('tarea_id')
                ->constrained('campania_mensaje_contextos')->nullOnDelete();
            $table->index(['campania_id', 'contexto_id', 'created_at'], 'campania_mensajes_contexto_custom_index');
        });
    }

    public function down(): void
    {
        Schema::table('campania_mensajes', function (Blueprint $table) {
            $table->dropIndex('campania_mensajes_contexto_custom_index');
            $table->dropConstrainedForeignId('contexto_id');
        });

        Schema::dropIfExists('campania_mensaje_contextos');
    }
};
