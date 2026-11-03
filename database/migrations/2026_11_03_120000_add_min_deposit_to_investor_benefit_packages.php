<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investor_benefit_packages', function (Blueprint $table) {
            $table->decimal('min_deposit_amount', 15, 2)->default(0)->after('loss_share_percent');
        });

        // Default minimal per paket seed
        DB::table('investor_benefit_packages')->where('code', 'BEN-SILVER')->update(['min_deposit_amount' => 5000000]);
        DB::table('investor_benefit_packages')->where('code', 'BEN-GOLD')->update(['min_deposit_amount' => 10000000]);
        DB::table('investor_benefit_packages')->where('code', 'BEN-PLATINUM')->update(['min_deposit_amount' => 25000000]);
    }

    public function down(): void
    {
        Schema::table('investor_benefit_packages', function (Blueprint $table) {
            $table->dropColumn('min_deposit_amount');
        });
    }
};
