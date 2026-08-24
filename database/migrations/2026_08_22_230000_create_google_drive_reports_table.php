<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_drive_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_key')->unique();
            $table->string('file_id');
            $table->string('folder_id');
            $table->string('file_name');
            $table->text('web_view_link');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_drive_reports');
    }
};
