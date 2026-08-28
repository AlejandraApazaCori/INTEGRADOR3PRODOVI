<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campania_disenador', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campania_id')->constrained('campanias')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['campania_id', 'user_id']);
        });

        DB::table('campanias')
            ->whereNotNull('disenador_id')
            ->orderBy('id')
            ->select(['id', 'disenador_id'])
            ->chunkById(200, function ($campaigns) {
                $now = now();
                DB::table('campania_disenador')->insertOrIgnore($campaigns->map(fn ($campaign) => [
                    'campania_id' => $campaign->id,
                    'user_id' => $campaign->disenador_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all());
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('campania_disenador');
    }
};
