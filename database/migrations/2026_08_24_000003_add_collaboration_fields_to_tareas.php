<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            $table->string('tipo_contenido', 30)->nullable()->after('entregable');
            $table->boolean('visible_cliente')->default(false)->after('requiere_aprobacion');
        });

        Schema::create('tarea_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarea_id')->constrained('tareas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['tarea_id', 'user_id']);
        });

        DB::table('tareas')
            ->whereNotNull('asignado_id')
            ->orderBy('id')
            ->select(['id', 'asignado_id'])
            ->chunkById(200, function ($tasks) {
                $now = now();
                DB::table('tarea_user')->insertOrIgnore($tasks->map(fn ($task) => [
                    'tarea_id' => $task->id,
                    'user_id' => $task->asignado_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all());
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarea_user');

        Schema::table('tareas', function (Blueprint $table) {
            $table->dropColumn(['tipo_contenido', 'visible_cliente']);
        });
    }
};
