<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            $table->json('publication_platforms')->nullable()->after('publication_message');
            $table->string('instagram_media_id')->nullable()->after('facebook_post_id');
        });
    }

    public function down(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            $table->dropColumn(['publication_platforms', 'instagram_media_id']);
        });
    }
};
