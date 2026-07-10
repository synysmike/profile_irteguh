<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('customer_type_id')
                ->nullable()
                ->after('customer_type')
                ->constrained('customer_types')
                ->nullOnDelete();
        });

        $typeMap = DB::table('customer_types')->pluck('id', 'legacy_key');

        DB::table('customers')
            ->where('customer_type', 'individual')
            ->update(['customer_type_id' => $typeMap['individual'] ?? null]);

        DB::table('customers')
            ->where('customer_type', 'company')
            ->update(['customer_type_id' => $typeMap['company'] ?? null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_type_id');
        });
    }
};
