<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sumber_danas', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('flow_type', ['masuk', 'keluar', 'both'])->default('both');
            $table->string('system_key', 50)->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->foreignId('sumber_dana_id')
                ->nullable()
                ->after('chart_of_account_id')
                ->constrained('sumber_danas')
                ->nullOnDelete();
        });

        $now = now();
        $defaults = [
            ['code' => 'SD-PENJ', 'name' => 'Penerimaan Penjualan', 'flow_type' => 'masuk', 'system_key' => 'penjualan', 'sort_order' => 10],
            ['code' => 'SD-PROJ', 'name' => 'Termin Project', 'flow_type' => 'masuk', 'system_key' => 'project_termin', 'sort_order' => 20],
            ['code' => 'SD-MODAL', 'name' => 'Setoran Modal', 'flow_type' => 'masuk', 'system_key' => null, 'sort_order' => 30],
            ['code' => 'SD-PINJ', 'name' => 'Pinjaman / Hutang Masuk', 'flow_type' => 'masuk', 'system_key' => null, 'sort_order' => 40],
            ['code' => 'SD-GROSIR', 'name' => 'Pembelian Grosir', 'flow_type' => 'keluar', 'system_key' => 'grosir', 'sort_order' => 50],
            ['code' => 'SD-GAJI', 'name' => 'Gaji & Payroll', 'flow_type' => 'keluar', 'system_key' => null, 'sort_order' => 60],
            ['code' => 'SD-OPS', 'name' => 'Beban Operasional', 'flow_type' => 'keluar', 'system_key' => null, 'sort_order' => 70],
            ['code' => 'SD-LAIN-M', 'name' => 'Lainnya (Masuk)', 'flow_type' => 'masuk', 'system_key' => null, 'sort_order' => 80],
            ['code' => 'SD-LAIN-K', 'name' => 'Lainnya (Keluar)', 'flow_type' => 'keluar', 'system_key' => null, 'sort_order' => 90],
        ];

        foreach ($defaults as $row) {
            DB::table('sumber_danas')->insert([
                'code' => $row['code'],
                'name' => $row['name'],
                'description' => null,
                'flow_type' => $row['flow_type'],
                'system_key' => $row['system_key'],
                'is_active' => true,
                'sort_order' => $row['sort_order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $penjualanId = DB::table('sumber_danas')->where('system_key', 'penjualan')->value('id');
        $grosirId = DB::table('sumber_danas')->where('system_key', 'grosir')->value('id');
        $projectId = DB::table('sumber_danas')->where('system_key', 'project_termin')->value('id');

        if ($penjualanId) {
            DB::table('cash_transactions')
                ->whereNotNull('sale_id')
                ->whereNull('project_payment_term_id')
                ->whereNull('project_id')
                ->update(['sumber_dana_id' => $penjualanId]);
        }

        if ($projectId) {
            DB::table('cash_transactions')
                ->where(function ($q) {
                    $q->whereNotNull('project_payment_term_id')
                        ->orWhereNotNull('project_id');
                })
                ->update(['sumber_dana_id' => $projectId]);
        }

        if ($grosirId) {
            DB::table('cash_transactions')
                ->whereNotNull('purchase_id')
                ->update(['sumber_dana_id' => $grosirId]);
        }
    }

    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sumber_dana_id');
        });

        Schema::dropIfExists('sumber_danas');
    }
};
