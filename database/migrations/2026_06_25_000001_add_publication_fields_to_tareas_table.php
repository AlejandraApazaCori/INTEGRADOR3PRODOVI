<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            $table->string('publication_status')->nullable()->after('prioridad');
            $table->timestamp('publication_scheduled_at')->nullable()->after('publication_status');
            $table->timestamp('published_at')->nullable()->after('publication_scheduled_at');
            $table->string('facebook_post_id')->nullable()->after('published_at');
            $table->text('publication_error')->nullable()->after('facebook_post_id');
        });
    }

    public function down(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            $table->dropColumn([
                'publication_status',
                'publication_scheduled_at',
                'published_at',
                'facebook_post_id',
                'publication_error',
            ]);
        });
    }
};
