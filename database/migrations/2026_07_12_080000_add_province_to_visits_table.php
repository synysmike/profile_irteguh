<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->string('province')->nullable()->after('country');
            $table->index('country');
            $table->index('province');
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropIndex(['country']);
            $table->dropIndex(['province']);
            $table->dropIndex(['city']);
            $table->dropColumn('province');
        });
    }
};
