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
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->enum('transaction_type', ['debit', 'credit']); // debit = penerimaan kas, credit = pengeluaran kas
            $table->foreignId('chart_of_account_id')->constrained('chart_of_accounts')->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('reference', 100)->nullable();
            $table->boolean('is_posted')->default(false);
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
