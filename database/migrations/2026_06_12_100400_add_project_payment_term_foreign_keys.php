<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreign('project_payment_term_id')->references('id')->on('project_payment_terms')->nullOnDelete();
        });

        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->foreign('project_payment_term_id')->references('id')->on('project_payment_terms')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['project_payment_term_id']);
        });

        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropForeign(['project_payment_term_id']);
        });
    }
};
