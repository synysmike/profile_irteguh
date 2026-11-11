<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_transactions', function (Blueprint $table) {
            $table->boolean('show_in_layanan')->default(false)->after('is_active');
            $table->foreignId('service_id')
                ->nullable()
                ->after('show_in_layanan')
                ->constrained('services')
                ->nullOnDelete();
            $table->index(['show_in_layanan', 'service_id']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('show_in_layanan')->default(false)->after('is_portfolio_featured');
            $table->foreignId('service_id')
                ->nullable()
                ->after('show_in_layanan')
                ->constrained('services')
                ->nullOnDelete();
            $table->index(['show_in_layanan', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::table('sale_transactions', function (Blueprint $table) {
            $table->dropIndex(['show_in_layanan', 'service_id']);
            $table->dropConstrainedForeignId('service_id');
            $table->dropColumn('show_in_layanan');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['show_in_layanan', 'service_id']);
            $table->dropConstrainedForeignId('service_id');
            $table->dropColumn('show_in_layanan');
        });
    }
};
