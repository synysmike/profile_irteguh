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
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('tax_id')->nullable()->after('customer_id')->constrained('taxes')->nullOnDelete();
            $table->string('tax_name')->nullable()->after('ppn_amount');
            $table->decimal('tax_rate', 5, 2)->nullable()->after('tax_name');
            $table->enum('tax_calculation_type', ['addition', 'deduction'])->nullable()->after('tax_rate');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('tax_id')->nullable()->after('supplier_id')->constrained('taxes')->nullOnDelete();
            $table->string('tax_name')->nullable()->after('ppn_amount');
            $table->decimal('tax_rate', 5, 2)->nullable()->after('tax_name');
            $table->enum('tax_calculation_type', ['addition', 'deduction'])->nullable()->after('tax_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tax_id');
            $table->dropColumn(['tax_name', 'tax_rate', 'tax_calculation_type']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tax_id');
            $table->dropColumn(['tax_name', 'tax_rate', 'tax_calculation_type']);
        });
    }
};
