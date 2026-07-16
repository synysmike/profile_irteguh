<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_letter_assignees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_letter_id')->constrained('assignment_letters')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('name');
            $table->enum('gender', ['L', 'P']);
            $table->string('ktp', 32);
            $table->string('phone', 32);
            $table->timestamps();
        });

        $letters = DB::table('assignment_letters')->get();
        foreach ($letters as $letter) {
            if (empty($letter->assignee_name)) {
                continue;
            }
            DB::table('assignment_letter_assignees')->insert([
                'assignment_letter_id' => $letter->id,
                'sort_order' => 0,
                'name' => $letter->assignee_name,
                'gender' => in_array($letter->assignee_gender, ['L', 'P'], true) ? $letter->assignee_gender : 'L',
                'ktp' => $letter->assignee_ktp ?: '-',
                'phone' => $letter->assignee_phone ?: '-',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('assignment_letters', function (Blueprint $table) {
            $table->dropColumn(['assignee_name', 'assignee_gender', 'assignee_ktp', 'assignee_phone']);
        });
    }

    public function down(): void
    {
        Schema::table('assignment_letters', function (Blueprint $table) {
            $table->string('assignee_name')->nullable();
            $table->enum('assignee_gender', ['L', 'P'])->nullable();
            $table->string('assignee_ktp', 32)->nullable();
            $table->string('assignee_phone', 32)->nullable();
        });

        $assignees = DB::table('assignment_letter_assignees')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('assignment_letter_id');

        foreach ($assignees as $letterId => $rows) {
            $first = $rows->first();
            DB::table('assignment_letters')->where('id', $letterId)->update([
                'assignee_name' => $first->name,
                'assignee_gender' => $first->gender,
                'assignee_ktp' => $first->ktp,
                'assignee_phone' => $first->phone,
            ]);
        }

        Schema::dropIfExists('assignment_letter_assignees');
    }
};
