<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campania_mensajes', function (Blueprint $table) {
            $table->foreignId('mensaje_padre_id')->nullable()->after('tarea_id')
                ->constrained('campania_mensajes')->nullOnDelete();
            $table->index(['campania_id', 'tarea_id', 'created_at'], 'campania_mensajes_contexto_index');
        });
    }

    public function down(): void
    {
        Schema::table('campania_mensajes', function (Blueprint $table) {
            $table->dropIndex('campania_mensajes_contexto_index');
            $table->dropConstrainedForeignId('mensaje_padre_id');
        });
    }
};
