<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('code', 30)->nullable()->unique();
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('value', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('discount_id')->nullable()->after('tax_id')->constrained('discounts')->nullOnDelete();
            $table->string('discount_name')->nullable()->after('discount_id');
            $table->string('discount_type', 20)->nullable()->after('discount_name');
            $table->decimal('discount_value', 15, 2)->nullable()->after('discount_type');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_value');
        });

        Schema::table('sale_transactions', function (Blueprint $table) {
            $table->foreignId('discount_id')->nullable()->after('project_id')->constrained('discounts')->nullOnDelete();
            $table->string('discount_name')->nullable()->after('discount_id');
            $table->string('discount_type', 20)->nullable()->after('discount_name');
            $table->decimal('discount_value', 15, 2)->nullable()->after('discount_type');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_value');
        });
    }

    public function down(): void
    {
        Schema::table('sale_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discount_id');
            $table->dropColumn(['discount_name', 'discount_type', 'discount_value', 'discount_amount']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discount_id');
            $table->dropColumn(['discount_name', 'discount_type', 'discount_value', 'discount_amount']);
        });

        Schema::dropIfExists('discounts');
    }
};
