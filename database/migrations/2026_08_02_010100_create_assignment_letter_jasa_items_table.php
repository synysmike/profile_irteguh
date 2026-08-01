<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_letter_jasa_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_letter_id')
                ->constrained('assignment_letters')
                ->cascadeOnDelete();
            $table->foreignId('jasa_id')
                ->nullable()
                ->constrained('jasas')
                ->nullOnDelete();
            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_letter_jasa_items');
    }
};
