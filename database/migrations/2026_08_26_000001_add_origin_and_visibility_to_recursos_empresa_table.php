<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recursos_empresa', function (Blueprint $table) {
            $table->string('origen', 20)->default('cliente')->after('url');
            $table->boolean('visible_cliente')->default(true)->after('origen');
            $table->foreignId('creado_por_id')->nullable()->after('visible_cliente')->constrained('users')->nullOnDelete();
            $table->index(['empresa_id', 'origen', 'visible_cliente'], 'recursos_empresa_origen_visible_index');
        });
    }

    public function down(): void
    {
        Schema::table('recursos_empresa', function (Blueprint $table) {
            $table->dropIndex('recursos_empresa_origen_visible_index');
            $table->dropConstrainedForeignId('creado_por_id');
            $table->dropColumn(['origen', 'visible_cliente']);
        });
    }
};
