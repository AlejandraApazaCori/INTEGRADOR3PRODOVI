<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campania_mensaje_imagenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mensaje_id')->constrained('campania_mensajes')->cascadeOnDelete();
            $table->string('nombre_original');
            $table->string('ruta_archivo');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('tamanio');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campania_mensaje_imagenes');
    }
};
