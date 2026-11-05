<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_progress_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('file_path', 500);
            $table->string('media_type', 20)->default('image'); // image|video
            $table->string('original_name')->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('caption')->nullable();
            $table->string('status_snapshot', 30)->nullable();
            $table->unsignedTinyInteger('progress_snapshot')->nullable();
            $table->boolean('show_in_portfolio')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'show_in_portfolio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_progress_media');
    }
};
