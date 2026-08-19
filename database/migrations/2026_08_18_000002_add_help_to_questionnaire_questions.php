<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('preguntas_cuestionario', 'ayuda')) {
            Schema::table('preguntas_cuestionario', function (Blueprint $table) {
                $table->text('ayuda')->nullable()->after('opciones');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('preguntas_cuestionario', 'ayuda')) {
            Schema::table('preguntas_cuestionario', function (Blueprint $table) {
                $table->dropColumn('ayuda');
            });
        }
    }
};
