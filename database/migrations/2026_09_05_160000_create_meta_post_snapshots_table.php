<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_post_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_account_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 16);
            $table->string('account_id', 100);
            $table->string('post_id', 100);
            $table->dateTime('published_at');
            $table->dateTime('observed_at');
            $table->unsignedBigInteger('likes');
            $table->unsignedBigInteger('comments');
            $table->unique(['social_account_id', 'account_id', 'post_id', 'observed_at'], 'meta_snapshot_unique');
            $table->index(['social_account_id', 'observed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_post_snapshots');
    }
};
