<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tareas MODIFY estado ENUM('pendiente','en_progreso','completada','rechazada','no_iniciado','en_curso','entregado','reformular','aprobado','publicado') NOT NULL DEFAULT 'no_iniciado'");
        }

        DB::table('tareas')->where('estado', 'pendiente')->update(['estado' => 'no_iniciado']);
        DB::table('tareas')->where('estado', 'en_progreso')->update(['estado' => 'en_curso']);
        DB::table('tareas')->where('estado', 'completada')->update(['estado' => 'entregado']);
        DB::table('tareas')->where('estado', 'rechazada')->update(['estado' => 'reformular']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tareas MODIFY estado ENUM('no_iniciado','pendiente','en_curso','entregado','reformular','aprobado','publicado') NOT NULL DEFAULT 'no_iniciado'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE tareas MODIFY estado ENUM('no_iniciado','pendiente','en_curso','entregado','reformular','aprobado','publicado','en_progreso','completada','rechazada') NOT NULL DEFAULT 'pendiente'");
        DB::table('tareas')->where('estado', 'no_iniciado')->update(['estado' => 'pendiente']);
        DB::table('tareas')->where('estado', 'en_curso')->update(['estado' => 'en_progreso']);
        DB::table('tareas')->whereIn('estado', ['entregado', 'aprobado', 'publicado'])->update(['estado' => 'completada']);
        DB::table('tareas')->where('estado', 'reformular')->update(['estado' => 'rechazada']);
        DB::statement("ALTER TABLE tareas MODIFY estado ENUM('pendiente','en_progreso','completada','rechazada') NOT NULL DEFAULT 'pendiente'");
    }
};
