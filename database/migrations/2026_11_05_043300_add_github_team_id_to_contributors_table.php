<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contributors', function (Blueprint $table) {
            $table->foreignId('github_team_id')
                ->nullable()
                ->after('is_active')
                ->constrained('github_teams')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contributors', function (Blueprint $table) {
            $table->dropConstrainedForeignId('github_team_id');
        });
    }
};
