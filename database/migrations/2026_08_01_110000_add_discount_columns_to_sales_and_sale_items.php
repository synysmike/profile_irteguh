<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('discount_id')->nullable()->after('tax_id')->constrained('discounts')->nullOnDelete();
            $table->string('discount_name')->nullable()->after('discount_id');
            $table->string('discount_type', 20)->nullable()->after('discount_name');
            $table->decimal('discount_value', 15, 2)->nullable()->after('discount_type');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_value');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->string('discount_name')->nullable()->after('unit_price');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_name');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['discount_name', 'discount_amount']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discount_id');
            $table->dropColumn(['discount_name', 'discount_type', 'discount_value', 'discount_amount']);
        });
    }
};
