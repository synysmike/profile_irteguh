<?php

namespace App\Services;

use App\Models\CashTransaction;
use App\Models\ChartOfAccount;
use App\Models\Project;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LayananFulfillmentService
{
    /**
     * Konfirmasi draft Sale Layanan Kami: link/split alokasi + posting kas.
     */
    public function confirmSale(Sale $sale): Sale
    {
        if (! $sale->is_layanan_pending) {
            throw ValidationException::withMessages([
                'sale' => ['Faktur ini bukan draft Order Layanan yang menunggu konfirmasi.'],
            ]);
        }

        if ($sale->cashTransaction) {
            throw ValidationException::withMessages([
                'sale' => ['Draft sudah punya transaksi kas.'],
            ]);
        }

        return DB::transaction(function () use ($sale) {
            $sale->load(['saleItems']);

            if ($sale->saleItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'sale' => ['Draft tidak punya item.'],
                ]);
            }

            foreach ($sale->saleItems as $item) {
                $this->fulfillSaleItem($item);
            }

            $cashAccount = ChartOfAccount::active()->where('type', 'kas')->ordered()->first();
            if ($cashAccount) {
                CashTransaction::create([
                    'transaction_date' => $sale->sale_date,
                    'transaction_type' => 'debit',
                    'chart_of_account_id' => $cashAccount->id,
                    'sumber_dana_id' => app(SumberDanaResolver::class)->penjualanId(),
                    'amount' => $sale->total,
                    'description' => 'Penerimaan kas dari penjualan Layanan Kami: '.$sale->invoice_number,
                    'reference' => $sale->invoice_number,
                    'sale_id' => $sale->id,
                ]);
            }

            $sale->update([
                'is_layanan_pending' => false,
                'is_posted' => true,
                'posted_at' => now(),
                'notes' => trim(($sale->notes ? $sale->notes."\n" : '').'[Layanan Kami] Dikonfirmasi admin '.now()->format('Y-m-d H:i')),
            ]);

            return $sale->fresh(['customer', 'saleItems', 'cashTransaction']);
        });
    }

    /**
     * Tolak draft Sale: hapus faktur (belum potong stok/kas).
     */
    public function rejectSale(Sale $sale): void
    {
        if (! $sale->is_layanan_pending) {
            throw ValidationException::withMessages([
                'sale' => ['Hanya draft Order Layanan yang bisa ditolak.'],
            ]);
        }

        if ($sale->cashTransaction) {
            throw ValidationException::withMessages([
                'sale' => ['Draft sudah punya kas — tidak bisa ditolak otomatis.'],
            ]);
        }

        DB::transaction(function () use ($sale) {
            $sale->saleItems()->delete();
            $sale->delete();
        });
    }

    /**
     * Konfirmasi draft Project: lepas flag pending, status berjalan, pastikan termin ada.
     */
    public function confirmProject(Project $project): Project
    {
        if (! $project->is_layanan_pending) {
            throw ValidationException::withMessages([
                'project' => ['Project ini bukan draft Order Layanan yang menunggu konfirmasi.'],
            ]);
        }

        if ($project->status === 'cancelled') {
            throw ValidationException::withMessages([
                'project' => ['Project dibatalkan tidak bisa dikonfirmasi.'],
            ]);
        }

        return DB::transaction(function () use ($project) {
            $project->update([
                'is_layanan_pending' => false,
                'status' => 'in_progress',
                'notes' => trim(($project->notes ? $project->notes."\n" : '').'[Layanan Kami] Dikonfirmasi admin '.now()->format('Y-m-d H:i')),
            ]);

            $project->refresh();

            if ($project->paymentTerms()->count() === 0) {
                if ($project->payment_method === 'full') {
                    ProjectFinanceService::rebuildFullPaymentTerm($project);
                } else {
                    ProjectFinanceService::syncPaymentTerms($project, [
                        ['label' => 'DP', 'percentage' => 50, 'due_date' => null],
                        ['label' => 'Pelunasan', 'percentage' => 50, 'due_date' => null],
                    ]);
                }
            }

            return $project->fresh(['customer', 'paymentTerms', 'service']);
        });
    }

    /**
     * Tolak draft Project: batalkan tanpa menyentuh template etalase.
     */
    public function rejectProject(Project $project): Project
    {
        if (! $project->is_layanan_pending) {
            throw ValidationException::withMessages([
                'project' => ['Hanya draft Order Layanan yang bisa ditolak.'],
            ]);
        }

        if ($project->paymentTerms()->where('status', 'paid')->exists()) {
            throw ValidationException::withMessages([
                'project' => ['Project sudah ada termin lunas — tolak manual.'],
            ]);
        }

        return DB::transaction(function () use ($project) {
            $project->paymentTerms()->delete();

            $project->update([
                'is_layanan_pending' => false,
                'status' => 'cancelled',
                'show_in_layanan' => false,
                'notes' => trim(($project->notes ? $project->notes."\n" : '').'[Layanan Kami] Ditolak admin '.now()->format('Y-m-d H:i')),
            ]);

            return $project->fresh();
        });
    }

    private function fulfillSaleItem(SaleItem $item): void
    {
        if ($item->sale_transaction_id) {
            $linked = SaleTransaction::find($item->sale_transaction_id);
            if ($linked) {
                $linked->update([
                    'is_active' => false,
                    'show_in_layanan' => false,
                ]);
            }

            return;
        }

        $allocId = $this->parseAllocId($item->notes);
        if (! $allocId) {
            throw ValidationException::withMessages([
                'items' => ["Item \"{$item->description}\" tidak punya referensi alokasi."],
            ]);
        }

        $allocation = SaleTransaction::query()
            ->whereKey($allocId)
            ->lockForUpdate()
            ->first();

        if (! $allocation) {
            throw ValidationException::withMessages([
                'items' => ["Alokasi #{$allocId} tidak ditemukan untuk \"{$item->description}\"."],
            ]);
        }

        if ($allocation->project_id || $allocation->isInvoiced() || ! $allocation->is_active) {
            throw ValidationException::withMessages([
                'items' => ["Alokasi \"{$allocation->description}\" sudah tidak tersedia."],
            ]);
        }

        $qty = (int) $item->quantity;
        if ($qty < 1 || $qty > (int) $allocation->quantity) {
            throw ValidationException::withMessages([
                'items' => ["Qty \"{$item->description}\" melebihi sisa alokasi ({$allocation->quantity})."],
            ]);
        }

        if ($qty === (int) $allocation->quantity) {
            $allocation->update([
                'is_active' => false,
                'show_in_layanan' => false,
            ]);
            $item->update([
                'sale_transaction_id' => $allocation->id,
                'notes' => trim(str_replace('layanan_alloc:'.$allocId, '', (string) $item->notes)),
            ]);

            return;
        }

        // Split: potong sebagian dari alokasi etalase
        $ratio = $qty / (int) $allocation->quantity;
        $soldDiscount = round((float) ($allocation->discount_amount ?? 0) * $ratio, 2);
        $soldSubtotal = round((float) $item->unit_price * $qty, 2);

        $sold = SaleTransaction::create([
            'purchase_id' => $allocation->purchase_id,
            'project_id' => null,
            'discount_id' => $allocation->discount_id,
            'discount_name' => $allocation->discount_name,
            'discount_type' => $allocation->discount_type,
            'discount_value' => $allocation->discount_value,
            'discount_amount' => $soldDiscount,
            'code' => $allocation->code,
            'description' => $allocation->description,
            'quantity' => $qty,
            'unit_price' => $item->unit_price,
            'subtotal' => $soldSubtotal,
            'notes' => 'Split dari alokasi #'.$allocation->id.' (Layanan Kami)',
            'is_active' => false,
            'show_in_layanan' => false,
            'service_id' => null,
        ]);

        $remainQty = (int) $allocation->quantity - $qty;
        $remainDiscount = round((float) ($allocation->discount_amount ?? 0) - $soldDiscount, 2);
        $allocation->update([
            'quantity' => $remainQty,
            'discount_amount' => max($remainDiscount, 0),
            'subtotal' => round((float) $allocation->unit_price * $remainQty - max($remainDiscount, 0), 2),
        ]);

        $item->update([
            'sale_transaction_id' => $sold->id,
            'subtotal' => $soldSubtotal,
            'discount_amount' => $soldDiscount,
            'notes' => 'split_from_alloc:'.$allocId,
        ]);
    }

    private function parseAllocId(?string $notes): ?int
    {
        if (! $notes) {
            return null;
        }

        if (preg_match('/layanan_alloc:(\d+)/', $notes, $m)) {
            return (int) $m[1];
        }

        return null;
    }
}
