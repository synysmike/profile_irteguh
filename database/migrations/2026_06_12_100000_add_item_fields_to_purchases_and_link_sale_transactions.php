<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('description')->nullable()->after('purchase_date');
            $table->integer('quantity')->default(1)->after('description');
            $table->decimal('unit_price', 15, 2)->default(0)->after('quantity');
        });

        Schema::table('sale_transactions', function (Blueprint $table) {
            $table->foreignId('purchase_id')->nullable()->after('id')->constrained('purchases')->nullOnDelete();
        });

        DB::table('purchases')->orderBy('id')->chunkById(100, function ($purchases) {
            foreach ($purchases as $purchase) {
                $quantity = max(1, (int) ($purchase->quantity ?? 1));
                $unitPrice = (float) ($purchase->unit_price ?? 0);
                if ($unitPrice <= 0 && (float) $purchase->subtotal > 0) {
                    $unitPrice = round(((float) $purchase->subtotal) / $quantity, 2);
                }

                DB::table('purchases')->where('id', $purchase->id)->update([
                    'description' => $purchase->description ?: ('Grosir ' . $purchase->invoice_number),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('sale_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_id');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['description', 'quantity', 'unit_price']);
        });
    }
};
