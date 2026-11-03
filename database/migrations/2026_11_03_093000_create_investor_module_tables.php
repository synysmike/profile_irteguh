<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('investor_benefit_packages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->decimal('profit_share_percent', 8, 2)->default(0);
            $table->decimal('loss_share_percent', 8, 2)->default(0);
            $table->text('description')->nullable();
            $table->longText('terms_template')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('investor_capital_injections', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('investor_id')->constrained('investors')->cascadeOnDelete();
            $table->foreignId('benefit_package_id')->nullable()->constrained('investor_benefit_packages')->nullOnDelete();
            $table->date('injection_date');
            $table->decimal('amount', 15, 2)->default(0);
            $table->foreignId('sumber_dana_id')->nullable()->constrained('sumber_danas')->nullOnDelete();
            $table->foreignId('equity_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->string('benefit_name')->nullable();
            $table->decimal('profit_share_percent', 8, 2)->default(0);
            $table->decimal('loss_share_percent', 8, 2)->default(0);
            $table->longText('terms_content')->nullable();
            $table->string('status', 30)->default('pending_approval');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_note')->nullable();
            $table->unsignedBigInteger('cash_transaction_id')->nullable();
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('investor_dividend_periods', function (Blueprint $table) {
            $table->id();
            $table->string('period_label');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('profit_base_amount', 15, 2)->default(0);
            $table->string('status', 30)->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('investor_dividend_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dividend_period_id')->constrained('investor_dividend_periods')->cascadeOnDelete();
            $table->foreignId('investor_id')->constrained('investors')->cascadeOnDelete();
            $table->foreignId('capital_injection_id')->nullable()->constrained('investor_capital_injections')->nullOnDelete();
            $table->decimal('share_percent_snapshot', 8, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status', 30)->default('pending');
            $table->unsignedBigInteger('cash_transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('investor_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_id')->constrained('investors')->cascadeOnDelete();
            $table->foreignId('capital_injection_id')->nullable()->constrained('investor_capital_injections')->nullOnDelete();
            $table->string('title');
            $table->string('file_path');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->foreignId('investor_capital_injection_id')
                ->nullable()
                ->after('project_payment_term_id')
                ->constrained('investor_capital_injections')
                ->nullOnDelete();
            $table->foreignId('investor_dividend_allocation_id')
                ->nullable()
                ->after('investor_capital_injection_id')
                ->constrained('investor_dividend_allocations')
                ->nullOnDelete();
        });

        Schema::table('investor_capital_injections', function (Blueprint $table) {
            $table->foreign('cash_transaction_id')->references('id')->on('cash_transactions')->nullOnDelete();
            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->nullOnDelete();
        });

        Schema::table('investor_dividend_allocations', function (Blueprint $table) {
            $table->foreign('cash_transaction_id')->references('id')->on('cash_transactions')->nullOnDelete();
        });

        $now = now();

        if (! DB::table('chart_of_accounts')->where('code', '3-3100')->exists()) {
            DB::table('chart_of_accounts')->insert([
                'code' => '3-3100',
                'name' => 'Modal Investor',
                'type' => 'modal',
                'parent_id' => null,
                'order' => 31,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (! DB::table('chart_of_accounts')->where('code', '5-5100')->exists()) {
            DB::table('chart_of_accounts')->insert([
                'code' => '5-5100',
                'name' => 'Dividen Investor',
                'type' => 'beban',
                'parent_id' => null,
                'order' => 51,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (! DB::table('sumber_danas')->where('system_key', 'modal_investor')->exists()) {
            DB::table('sumber_danas')->insert([
                'code' => 'SD-MODAL-INV',
                'name' => 'Setoran Modal Investor',
                'description' => 'Penerimaan kas dari setoran modal investor',
                'flow_type' => 'masuk',
                'system_key' => 'modal_investor',
                'is_active' => true,
                'sort_order' => 25,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (! DB::table('sumber_danas')->where('system_key', 'dividen_investor')->exists()) {
            DB::table('sumber_danas')->insert([
                'code' => 'SD-DIVIDEN',
                'name' => 'Payout Dividen Investor',
                'description' => 'Pengeluaran kas untuk pembayaran dividen investor',
                'flow_type' => 'keluar',
                'system_key' => 'dividen_investor',
                'is_active' => true,
                'sort_order' => 55,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $packages = [
            ['code' => 'BEN-SILVER', 'name' => 'Silver', 'profit' => 5, 'loss' => 5, 'order' => 10],
            ['code' => 'BEN-GOLD', 'name' => 'Gold', 'profit' => 10, 'loss' => 10, 'order' => 20],
            ['code' => 'BEN-PLATINUM', 'name' => 'Platinum', 'profit' => 20, 'loss' => 20, 'order' => 30],
        ];

        foreach ($packages as $pkg) {
            if (DB::table('investor_benefit_packages')->where('code', $pkg['code'])->exists()) {
                continue;
            }
            DB::table('investor_benefit_packages')->insert([
                'code' => $pkg['code'],
                'name' => $pkg['name'],
                'profit_share_percent' => $pkg['profit'],
                'loss_share_percent' => $pkg['loss'],
                'description' => 'Paket benefit ' . $pkg['name'],
                'terms_template' => "SYARAT DAN KETENTUAN INVESTASI\n\n"
                    . "1. Investor menyetor modal sesuai nominal pada dokumen ini.\n"
                    . "2. Pembagian laba/rugi mengikuti paket benefit yang dipilih.\n"
                    . "3. Pembayaran dividen dilakukan sesuai periode yang ditetapkan perusahaan.\n"
                    . "4. Investor menyatakan telah membaca dan menyetujui seluruh ketentuan ini.\n",
                'is_active' => true,
                'sort_order' => $pkg['order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('investor_dividend_allocations', function (Blueprint $table) {
            $table->dropForeign(['cash_transaction_id']);
        });

        Schema::table('investor_capital_injections', function (Blueprint $table) {
            $table->dropForeign(['cash_transaction_id']);
            $table->dropForeign(['journal_entry_id']);
        });

        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('investor_dividend_allocation_id');
            $table->dropConstrainedForeignId('investor_capital_injection_id');
        });

        Schema::dropIfExists('investor_documents');
        Schema::dropIfExists('investor_dividend_allocations');
        Schema::dropIfExists('investor_dividend_periods');
        Schema::dropIfExists('investor_capital_injections');
        Schema::dropIfExists('investor_benefit_packages');
        Schema::dropIfExists('investors');
    }
};
