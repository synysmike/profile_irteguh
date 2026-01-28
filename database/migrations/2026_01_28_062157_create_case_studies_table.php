<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('case_studies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('client_context')->nullable();
            $table->text('challenge');
            $table->text('solution');
            $table->text('outcome');
            $table->json('visuals')->nullable(); // Array of image URLs
            $table->json('tags')->nullable(); // Array of tags
            $table->string('category'); // IT Infrastructure, Automation, Creative, Legal
            $table->integer('year');
            $table->text('excerpt')->nullable();
            $table->boolean('featured')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_studies');
    }
};
