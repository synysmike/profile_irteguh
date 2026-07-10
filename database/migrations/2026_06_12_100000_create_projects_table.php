<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->enum('status', ['pending', 'in_progress', 'review', 'completed', 'cancelled'])->default('pending');
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('ppn_amount', 15, 2)->default(0);
            $table->string('tax_name')->nullable();
            $table->decimal('tax_rate', 5, 2)->nullable();
            $table->enum('tax_calculation_type', ['addition', 'deduction'])->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->enum('payment_method', ['full', 'installment'])->default('full');
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
