<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->string('reviewer_name')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamp('submitted_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->unique(['sale_id', 'customer_id']);
        });

        Schema::create('sale_review_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_review_id')->constrained('sale_reviews')->cascadeOnDelete();
            $table->string('file_path', 500);
            $table->string('media_type', 20)->default('image');
            $table->string('original_name')->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_review_media');
        Schema::dropIfExists('sale_reviews');
    }
};
