<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('case_studies');

        Schema::table('projects', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('title');
            $table->boolean('is_portfolio_public')->default(false)->after('notes');
            $table->boolean('is_portfolio_featured')->default(false)->after('is_portfolio_public');
            $table->string('portfolio_category', 120)->nullable()->after('is_portfolio_featured');
            $table->unsignedSmallInteger('portfolio_year')->nullable()->after('portfolio_category');
            $table->unsignedInteger('portfolio_sort_order')->default(0)->after('portfolio_year');
            $table->string('portfolio_client_context')->nullable()->after('portfolio_sort_order');
            $table->text('portfolio_excerpt')->nullable()->after('portfolio_client_context');
            $table->text('portfolio_challenge')->nullable()->after('portfolio_excerpt');
            $table->text('portfolio_solution')->nullable()->after('portfolio_challenge');
            $table->text('portfolio_outcome')->nullable()->after('portfolio_solution');
            $table->json('portfolio_tags')->nullable()->after('portfolio_outcome');
            $table->string('portfolio_cover_path', 500)->nullable()->after('portfolio_tags');
            $table->json('portfolio_visuals')->nullable()->after('portfolio_cover_path');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'is_portfolio_public',
                'is_portfolio_featured',
                'portfolio_category',
                'portfolio_year',
                'portfolio_sort_order',
                'portfolio_client_context',
                'portfolio_excerpt',
                'portfolio_challenge',
                'portfolio_solution',
                'portfolio_outcome',
                'portfolio_tags',
                'portfolio_cover_path',
                'portfolio_visuals',
            ]);
        });

        Schema::create('case_studies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('client_context')->nullable();
            $table->text('challenge');
            $table->text('solution');
            $table->text('outcome');
            $table->json('visuals')->nullable();
            $table->json('tags')->nullable();
            $table->string('category');
            $table->integer('year');
            $table->text('excerpt')->nullable();
            $table->boolean('featured')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }
};
