<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->boolean('is_layanan_pending')->default(false)->after('show_in_public');
            $table->index('is_layanan_pending');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('layanan_template_project_id')
                ->nullable()
                ->after('service_id')
                ->constrained('projects')
                ->nullOnDelete();
            $table->boolean('is_layanan_pending')->default(false)->after('layanan_template_project_id');
            $table->index('is_layanan_pending');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['is_layanan_pending']);
            $table->dropColumn('is_layanan_pending');
            $table->dropConstrainedForeignId('layanan_template_project_id');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['is_layanan_pending']);
            $table->dropColumn('is_layanan_pending');
        });
    }
};
