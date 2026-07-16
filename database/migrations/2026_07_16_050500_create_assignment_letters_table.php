<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('number')->unique();
            $table->date('letter_date');
            $table->string('subject')->nullable();
            $table->text('task_description')->nullable();
            $table->string('assignee_name');
            $table->enum('assignee_gender', ['L', 'P']);
            $table->string('assignee_ktp', 32);
            $table->string('assignee_phone', 32);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_letters');
    }
};
