<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_review_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_review_id')->constrained('project_reviews')->cascadeOnDelete();
            $table->string('file_path', 500);
            $table->string('media_type', 20)->default('image'); // image|video
            $table->string('original_name')->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_review_media');
    }
};
