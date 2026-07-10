<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_category_id');
        });

        Schema::table('customer_categories', function (Blueprint $table) {
            $table->enum('legacy_key', ['individual', 'company'])->default('individual')->after('name');
        });

        DB::table('customer_categories')->delete();

        $individualId = DB::table('customer_categories')->insertGetId([
            'name' => 'Individu',
            'legacy_key' => 'individual',
            'description' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $companyId = DB::table('customer_categories')->insertGetId([
            'name' => 'Perusahaan',
            'legacy_key' => 'company',
            'description' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('customer_types', function (Blueprint $table) {
            $table->foreignId('customer_category_id')
                ->nullable()
                ->after('legacy_key')
                ->constrained('customer_categories')
                ->restrictOnDelete();
        });

        DB::table('customer_types')
            ->where('legacy_key', 'individual')
            ->update(['customer_category_id' => $individualId]);

        DB::table('customer_types')
            ->where('legacy_key', 'company')
            ->update(['customer_category_id' => $companyId]);

        DB::table('customer_types')
            ->whereNull('customer_category_id')
            ->update(['customer_category_id' => $individualId]);
    }

    public function down(): void
    {
        Schema::table('customer_types', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_category_id');
        });

        Schema::table('customer_categories', function (Blueprint $table) {
            $table->dropColumn('legacy_key');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('customer_category_id')
                ->nullable()
                ->after('customer_type_id')
                ->constrained('customer_categories')
                ->nullOnDelete();
        });
    }
};
