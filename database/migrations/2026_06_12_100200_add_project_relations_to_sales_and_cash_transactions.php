<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('customer_id')->constrained('projects')->nullOnDelete();
            $table->unsignedBigInteger('project_payment_term_id')->nullable()->after('project_id');
        });

        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('purchase_id')->constrained('projects')->nullOnDelete();
            $table->unsignedBigInteger('project_payment_term_id')->nullable()->after('project_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_payment_term_id');
            $table->dropConstrainedForeignId('project_id');
        });

        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_payment_term_id');
            $table->dropConstrainedForeignId('project_id');
        });
    }
};
