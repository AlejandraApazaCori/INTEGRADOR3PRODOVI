<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('codigos_pagos', function (Blueprint $table) {
            $table->timestamp('descargado_at')->nullable()->after('fecha_utilizacion');
        });
    }

    public function down(): void
    {
        Schema::table('codigos_pagos', function (Blueprint $table) {
            $table->dropColumn('descargado_at');
        });
    }
};
