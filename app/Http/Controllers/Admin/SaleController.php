<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleTransaction;
use App\Models\Customer;
use App\Models\CashTransaction;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

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
        $ppnRate = 0.11;
        $ppnAmount = (int) round($subtotal * $ppnRate);
        $total = $subtotal + $ppnAmount;

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
            'ppn_amount' => $ppnAmount,
            'total' => $total,
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
            return response()->json([
                'html' => view('admin.sales.partials.form', ['sale' => null, 'customers' => $customers])->render()
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
            'invoice_number' => 'required|string|max:50|unique:sales,invoice_number',
            'sale_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $transactions = SaleTransaction::whereIn('id', $transactionIds)->get();
        $subtotal = $transactions->sum('subtotal');
        $ppnAmount = (int) round($subtotal * 0.11);
        $validated['subtotal'] = $subtotal;
        $validated['ppn_amount'] = $ppnAmount;
        $validated['total'] = $subtotal + $ppnAmount;

        DB::beginTransaction();
        try {
            $sale = Sale::create($validated);

            foreach ($transactions->values() as $index => $transaction) {
                $item = new SaleItem([
                    'sale_id' => $sale->id,
                    'description' => $transaction->description,
                    'quantity' => $transaction->quantity,
                    'unit_price' => $transaction->unit_price,
                    'subtotal' => $transaction->subtotal,
                    'notes' => $transaction->notes,
                    'sort_order' => $index,
                ]);
                $item->save();
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
            // Map sale items to transaction IDs for form
            $sale->transaction_ids = $sale->saleItems->pluck('id')->toArray();
            return response()->json([
                'html' => view('admin.sales.partials.form', ['sale' => $sale, 'customers' => $customers])->render(),
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
            'invoice_number' => 'required|string|max:50|unique:sales,invoice_number,' . $id,
            'sale_date' => 'required|date',
            'subtotal' => 'required|numeric|min:0',
            'ppn_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'transaction_ids' => 'required|array|min:1',
            'transaction_ids.*' => 'required|exists:sale_transactions,id',
        ]);

        $validated['ppn_amount'] = $validated['ppn_amount'] ?? 0;
        $validated['total'] = $validated['subtotal'] + $validated['ppn_amount'];

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
                    'description' => $transaction->description,
                    'quantity' => $transaction->quantity,
                    'unit_price' => $transaction->unit_price,
                    'subtotal' => $transaction->subtotal,
                    'notes' => $transaction->notes,
                    'sort_order' => $index,
                ]);
                $item->save();
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
     * Tampilkan invoice penjualan (untuk cetak).
     */
    public function invoice(string $id)
    {
        $sale = Sale::with(['customer', 'saleItems'])->findOrFail($id);
        return view('admin.sales.invoice', compact('sale'));
    }
}
