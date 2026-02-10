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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nip', 50)->nullable();
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('npwp', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
