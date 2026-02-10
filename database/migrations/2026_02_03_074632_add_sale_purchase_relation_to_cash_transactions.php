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
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->foreignId('sale_id')->nullable()->after('chart_of_account_id')->constrained('sales')->nullOnDelete();
            $table->foreignId('purchase_id')->nullable()->after('sale_id')->constrained('purchases')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->dropForeign(['purchase_id']);
            $table->dropColumn(['sale_id', 'purchase_id']);
        });
    }
};
