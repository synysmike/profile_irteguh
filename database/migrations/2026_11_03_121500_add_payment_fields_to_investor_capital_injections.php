<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investor_capital_injections', function (Blueprint $table) {
            $table->string('payment_proof_path', 500)->nullable()->after('notes');
            $table->string('payment_reference', 120)->nullable()->after('payment_proof_path');
            $table->string('payment_sender_name', 120)->nullable()->after('payment_reference');
            $table->date('payment_date')->nullable()->after('payment_sender_name');
            $table->timestamp('paid_at')->nullable()->after('payment_date');
        });
    }

    public function down(): void
    {
        Schema::table('investor_capital_injections', function (Blueprint $table) {
            $table->dropColumn([
                'payment_proof_path',
                'payment_reference',
                'payment_sender_name',
                'payment_date',
                'paid_at',
            ]);
        });
    }
};
