<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->boolean('show_in_public')->default(false)->after('notes');
            $table->index(['show_in_public', 'project_id', 'sale_date']);
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['show_in_public', 'project_id', 'sale_date']);
            $table->dropColumn('show_in_public');
        });
    }
};
