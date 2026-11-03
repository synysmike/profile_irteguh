<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investors', function (Blueprint $table) {
            $table->string('bank_name', 120)->nullable()->after('phone');
            $table->string('bank_account', 60)->nullable()->after('bank_name');
            $table->string('bank_account_name', 120)->nullable()->after('bank_account');
        });

        Schema::table('investor_dividend_allocations', function (Blueprint $table) {
            $table->date('payment_date')->nullable()->after('paid_at');
            $table->string('payment_reference', 120)->nullable()->after('payment_date');
            $table->text('payment_note')->nullable()->after('payment_reference');
        });
    }

    public function down(): void
    {
        Schema::table('investors', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account', 'bank_account_name']);
        });

        Schema::table('investor_dividend_allocations', function (Blueprint $table) {
            $table->dropColumn(['payment_date', 'payment_reference', 'payment_note']);
        });
    }
};
