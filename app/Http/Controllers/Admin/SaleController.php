<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleTransaction;
use App\Models\Customer;
use App\Models\Tax;
use App\Models\CashTransaction;
use App\Models\ChartOfAccount;
use App\Services\PosCheckoutService;
use App\Support\SaleWhatsAppInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return redirect()->route('admin.keuangan.transaksi.penjualan');
    }

    private const PENDING_SESSION_KEY = 'pending_sale_transaction_ids';

    /**
     * Get current pending transaction IDs from session and return list with totals.
     */
    public function pendingTransactionsList()
    {
        $ids = Session::get(self::PENDING_SESSION_KEY, []);
        $ids = array_values(array_filter(array_unique($ids)));
        $transactions = SaleTransaction::whereIn('id', $ids)->orderBy('description')->get();
        $subtotal = $transactions->sum('subtotal');
        return response()->json([
            'items' => $transactions->map(function ($t) {
                return [
                    'id' => $t->id,
                    'description' => $t->description,
                    'quantity' => $t->quantity,
                    'unit_price' => (float) $t->unit_price,
                    'subtotal' => (float) $t->subtotal,
                    'code' => $t->code,
                ];
            })->values()->all(),
            'subtotal' => $subtotal,
            'ppn_amount' => 0,
            'total' => $subtotal,
        ]);
    }

    /**
     * Add transaction(s) to pending list (session).
     */
    public function addPendingTransaction(Request $request)
    {
        $request->validate([
            'transaction_ids' => 'required|array|min:1',
            'transaction_ids.*' => 'required|exists:sale_transactions,id',
        ]);

        $invalidCount = SaleTransaction::whereIn('id', $request->transaction_ids)
            ->whereNull('purchase_id')
            ->count();
        if ($invalidCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya transaksi yang terhubung ke grosir yang dapat di-invoice.',
            ], 422);
        }
        $ids = Session::get(self::PENDING_SESSION_KEY, []);
        foreach ($request->transaction_ids as $id) {
            $ids[] = (int) $id;
        }
        $ids = array_values(array_unique($ids));
        Session::put(self::PENDING_SESSION_KEY, $ids);
        return $this->pendingTransactionsList();
    }

    /**
     * Remove one transaction from pending list.
     */
    public function removePendingTransaction(string $id)
    {
        $ids = Session::get(self::PENDING_SESSION_KEY, []);
        $ids = array_values(array_filter($ids, fn ($i) => (string) $i !== (string) $id));
        Session::put(self::PENDING_SESSION_KEY, $ids);
        return $this->pendingTransactionsList();
    }

    /**
     * Clear all pending transactions (daftar sementara dikosongkan).
     */
    public function clearPendingTransactions()
    {
        Session::forget(self::PENDING_SESSION_KEY);
        return $this->pendingTransactionsList();
    }

    public function create()
    {
        if (request()->ajax()) {
            $customers = Customer::active()->orderBy('name')->get();
            $taxes = Tax::where('is_active', true)->orderBy('name')->get();
            return response()->json([
                'html' => view('admin.sales.partials.form', ['sale' => null, 'customers' => $customers, 'taxes' => $taxes])->render()
            ]);
        }
        return redirect()->route('admin.keuangan.transaksi.penjualan');
    }

    public function store(Request $request)
    {
        $transactionIds = Session::get(self::PENDING_SESSION_KEY, []);
        if (empty($transactionIds)) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada transaksi. Tambah transaksi ke daftar sementara lalu simpan invoice.',
                    'errors' => ['transaction_ids' => ['Minimal 1 transaksi harus ditambahkan.']],
                ], 422);
            }
            return back()->withInput()->with('error', 'Belum ada transaksi. Tambah transaksi ke daftar lalu simpan invoice.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'tax_id' => 'nullable|exists:taxes,id',
            'invoice_number' => 'required|string|max:50|unique:sales,invoice_number',
            'sale_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $transactions = SaleTransaction::whereIn('id', $transactionIds)->get();
        $subtotal = $transactions->sum('subtotal');
        $tax = !empty($validated['tax_id']) ? Tax::find($validated['tax_id']) : null;
        $taxAmount = $tax ? $tax->calculateAmount((float) $subtotal) : 0;
        $validated['subtotal'] = $subtotal;
        $validated['ppn_amount'] = $taxAmount;
        $validated['tax_name'] = $tax?->name;
        $validated['tax_rate'] = $tax?->rate;
        $validated['tax_calculation_type'] = $tax?->calculation_type;
        $validated['total'] = $tax && $tax->isDeduction()
            ? ($subtotal - $taxAmount)
            : ($subtotal + $taxAmount);

        DB::beginTransaction();
        try {
            $sale = Sale::create($validated);

            foreach ($transactions->values() as $index => $transaction) {
                $item = new SaleItem([
                    'sale_id' => $sale->id,
                    'sale_transaction_id' => $transaction->id,
                    'description' => $transaction->description,
                    'quantity' => $transaction->quantity,
                    'unit_price' => $transaction->unit_price,
                    'subtotal' => $transaction->subtotal,
                    'notes' => $transaction->notes,
                    'sort_order' => $index,
                ]);
                $item->save();
                $transaction->update(['is_active' => false]);
            }

            Session::forget(self::PENDING_SESSION_KEY);

            // Auto-posting ke Kas: Penerimaan Kas (Debit) dari penjualan
            $cashAccount = ChartOfAccount::active()->where('type', 'kas')->ordered()->first();
            if ($cashAccount) {
                CashTransaction::create([
                    'transaction_date' => $sale->sale_date,
                    'transaction_type' => 'debit',
                    'chart_of_account_id' => $cashAccount->id,
                    'amount' => $sale->total,
                    'description' => 'Penerimaan kas dari penjualan: ' . $sale->invoice_number,
                    'reference' => $sale->invoice_number,
                    'sale_id' => $sale->id,
                ]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Penjualan berhasil ditambahkan. Transaksi kas otomatis dibuat.',
                ]);
            }

            return redirect()->route('admin.keuangan.transaksi.penjualan')
                ->with('success', 'Penjualan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                ], 500);
            }
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan penjualan.');
        }
    }

    public function edit(string $id)
    {
        $sale = Sale::with(['customer', 'saleItems'])->findOrFail($id);

        if (request()->ajax()) {
            $customers = Customer::active()->orderBy('name')->get();
            $taxes = Tax::where('is_active', true)->orderBy('name')->get();
            // Map sale items to transaction IDs for form
            $sale->transaction_ids = $sale->saleItems->pluck('id')->toArray();
            return response()->json([
                'html' => view('admin.sales.partials.form', ['sale' => $sale, 'customers' => $customers, 'taxes' => $taxes])->render(),
                'data' => $sale
            ]);
        }

        return redirect()->route('admin.keuangan.transaksi.penjualan');
    }

    public function update(Request $request, string $id)
    {
        $sale = Sale::findOrFail($id);

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'tax_id' => 'nullable|exists:taxes,id',
            'invoice_number' => 'required|string|max:50|unique:sales,invoice_number,' . $id,
            'sale_date' => 'required|date',
            'subtotal' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'transaction_ids' => 'required|array|min:1',
            'transaction_ids.*' => 'required|exists:sale_transactions,id',
        ]);

        $tax = !empty($validated['tax_id']) ? Tax::find($validated['tax_id']) : null;
        $taxAmount = $tax ? $tax->calculateAmount((float) $validated['subtotal']) : 0;
        $validated['ppn_amount'] = $taxAmount;
        $validated['tax_name'] = $tax?->name;
        $validated['tax_rate'] = $tax?->rate;
        $validated['tax_calculation_type'] = $tax?->calculation_type;
        $validated['total'] = $tax && $tax->isDeduction()
            ? ($validated['subtotal'] - $taxAmount)
            : ($validated['subtotal'] + $taxAmount);

        DB::beginTransaction();
        try {
            $sale->update($validated);

            // Delete existing items
            $sale->saleItems()->delete();

            // Create new sale items from selected transactions
            $transactions = SaleTransaction::whereIn('id', $request->transaction_ids)->get();
            foreach ($transactions as $index => $transaction) {
                $item = new SaleItem([
                    'sale_id' => $sale->id,
                    'sale_transaction_id' => $transaction->id,
                    'description' => $transaction->description,
                    'quantity' => $transaction->quantity,
                    'unit_price' => $transaction->unit_price,
                    'subtotal' => $transaction->subtotal,
                    'notes' => $transaction->notes,
                    'sort_order' => $index,
                ]);
                $item->save();
                $transaction->update(['is_active' => false]);
            }

            // Update cash transaction terkait jika ada
            if ($sale->cashTransaction) {
                $sale->cashTransaction->update([
                    'transaction_date' => $sale->sale_date,
                    'amount' => $sale->total,
                    'description' => 'Penerimaan kas dari penjualan: ' . $sale->invoice_number,
                    'reference' => $sale->invoice_number,
                ]);
            } else {
                // Buat baru jika belum ada
                $cashAccount = ChartOfAccount::active()->where('type', 'kas')->ordered()->first();
                if ($cashAccount) {
                    CashTransaction::create([
                        'transaction_date' => $sale->sale_date,
                        'transaction_type' => 'debit',
                        'chart_of_account_id' => $cashAccount->id,
                        'amount' => $sale->total,
                        'description' => 'Penerimaan kas dari penjualan: ' . $sale->invoice_number,
                        'reference' => $sale->invoice_number,
                        'sale_id' => $sale->id,
                    ]);
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Penjualan berhasil diperbarui. Transaksi kas diperbarui.',
                ]);
            }

            return redirect()->route('admin.keuangan.transaksi.penjualan')
                ->with('success', 'Penjualan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                ], 500);
            }
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui penjualan.');
        }
    }

    public function destroy(string $id)
    {
        $sale = Sale::findOrFail($id);
        
        // Hapus cash transaction terkait
        if ($sale->cashTransaction) {
            $sale->cashTransaction->delete();
        }
        
        $sale->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Penjualan berhasil dihapus. Transaksi kas terkait juga dihapus.',
            ]);
        }

        return redirect()->route('admin.keuangan.transaksi.penjualan')
            ->with('success', 'Penjualan berhasil dihapus.');
    }

    /**
     * POS catalog: stok grosir tersisa + alokasi siap invoice.
     */
    public function posCatalog(PosCheckoutService $pos)
    {
        return response()->json($pos->catalog());
    }

    /**
     * POS checkout: buat SaleTransaction (jika dari stok) + Sale + SaleItem + Kas.
     */
    public function posCheckout(Request $request, PosCheckoutService $pos)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'tax_id' => 'nullable|exists:taxes,id',
            'invoice_number' => 'required|string|max:50|unique:sales,invoice_number',
            'sale_date' => 'required|date',
            'notes' => 'nullable|string',
            'cart' => 'required|array|min:1',
            'cart.*.type' => 'required|in:purchase,sale_transaction',
            'cart.*.purchase_id' => 'nullable|exists:purchases,id',
            'cart.*.sale_transaction_id' => 'nullable|exists:sale_transactions,id',
            'cart.*.quantity' => 'nullable|integer|min:1',
            'cart.*.unit_price' => 'nullable|numeric|min:0',
            'cart.*.notes' => 'nullable|string',
        ]);

        try {
            $sale = $pos->checkout([
                'customer_id' => $validated['customer_id'],
                'tax_id' => $validated['tax_id'] ?? null,
                'invoice_number' => $validated['invoice_number'],
                'sale_date' => $validated['sale_date'],
                'notes' => $validated['notes'] ?? null,
            ], $validated['cart']);

            Session::forget(self::PENDING_SESSION_KEY);

            return response()->json([
                'success' => true,
                'message' => 'Invoice berhasil dibuat.',
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'invoice_url' => route('admin.sales.invoice', $sale->id),
                'whatsapp_url' => SaleWhatsAppInvoice::destinationPhone($sale)
                    ? route('admin.sales.whatsapp', $sale->id)
                    : null,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tampilkan invoice penjualan (untuk cetak).
     */
    public function invoice(string $id)
    {
        $sale = Sale::with(['customer', 'saleItems', 'project'])->findOrFail($id);

        if ($sale->project_id && $sale->project_payment_term_id) {
            \App\Services\ProjectFinanceService::refreshTermInvoiceItems($sale);
            $sale->load(['customer', 'saleItems', 'project']);
        }

        return view('admin.sales.invoice', compact('sale'));
    }

    /**
     * Open WhatsApp chat to the invoice customer's phone with a prefilled invoice message.
     */
    public function whatsapp(string $id)
    {
        $sale = Sale::with(['customer', 'saleItems', 'cashTransaction', 'project'])->findOrFail($id);

        if ($sale->project_id && $sale->project_payment_term_id) {
            \App\Services\ProjectFinanceService::refreshTermInvoiceItems($sale);
            $sale->load(['customer', 'saleItems', 'cashTransaction', 'project']);
        }

        if (! SaleWhatsAppInvoice::destinationPhone($sale)) {
            return redirect()
                ->route('admin.keuangan.transaksi.penjualan')
                ->with('error', 'Customer pada invoice ini belum memiliki nomor HP. Lengkapi data customer terlebih dahulu.');
        }

        return redirect()->away(SaleWhatsAppInvoice::shareUrl($sale));
    }
}
