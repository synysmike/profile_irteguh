<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('base_subtotal', 15, 2)->default(0)->after('progress_percent');
        });

        DB::table('projects')->update([
            'base_subtotal' => DB::raw('subtotal'),
        ]);

        Schema::table('sale_transactions', function (Blueprint $table) {
            $table->foreignId('project_id')
                ->nullable()
                ->after('purchase_id')
                ->constrained('projects')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sale_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('base_subtotal');
        });
    }
};
