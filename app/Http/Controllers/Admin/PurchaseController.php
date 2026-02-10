<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\CashTransaction;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return redirect()->route('admin.keuangan.transaksi.pembelian');
    }

    public function create()
    {
        if (request()->ajax()) {
            $suppliers = Supplier::active()->orderBy('name')->get();
            return response()->json([
                'html' => view('admin.purchases.partials.form', ['purchase' => null, 'suppliers' => $suppliers])->render()
            ]);
        }
        return redirect()->route('admin.keuangan.transaksi.pembelian');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'required|string|max:50|unique:purchases,invoice_number',
            'purchase_date' => 'required|date',
            'subtotal' => 'required|numeric|min:0',
            'ppn_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['ppn_amount'] = $validated['ppn_amount'] ?? 0;
        $validated['total'] = $validated['subtotal'] + $validated['ppn_amount'];

        $purchase = Purchase::create($validated);

        // Auto-posting ke Kas: Pengeluaran Kas (Credit) untuk pembelian
        $cashAccount = ChartOfAccount::active()->where('type', 'kas')->ordered()->first();
        if ($cashAccount) {
            CashTransaction::create([
                'transaction_date' => $purchase->purchase_date,
                'transaction_type' => 'credit',
                'chart_of_account_id' => $cashAccount->id,
                'amount' => $purchase->total,
                'description' => 'Pengeluaran kas untuk pembelian: ' . $purchase->invoice_number,
                'reference' => $purchase->invoice_number,
                'purchase_id' => $purchase->id,
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pembelian berhasil ditambahkan. Transaksi kas otomatis dibuat.',
            ]);
        }

        return redirect()->route('admin.keuangan.transaksi.pembelian')
            ->with('success', 'Pembelian berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $purchase = Purchase::with('supplier')->findOrFail($id);

        if (request()->ajax()) {
            $suppliers = Supplier::active()->orderBy('name')->get();
            return response()->json([
                'html' => view('admin.purchases.partials.form', ['purchase' => $purchase, 'suppliers' => $suppliers])->render(),
                'data' => $purchase
            ]);
        }

        return redirect()->route('admin.keuangan.transaksi.pembelian');
    }

    public function update(Request $request, string $id)
    {
        $purchase = Purchase::findOrFail($id);

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'required|string|max:50|unique:purchases,invoice_number,' . $id,
            'purchase_date' => 'required|date',
            'subtotal' => 'required|numeric|min:0',
            'ppn_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['ppn_amount'] = $validated['ppn_amount'] ?? 0;
        $validated['total'] = $validated['subtotal'] + $validated['ppn_amount'];

        $purchase->update($validated);

        // Update cash transaction terkait jika ada
        if ($purchase->cashTransaction) {
            $purchase->cashTransaction->update([
                'transaction_date' => $purchase->purchase_date,
                'amount' => $purchase->total,
                'description' => 'Pengeluaran kas untuk pembelian: ' . $purchase->invoice_number,
                'reference' => $purchase->invoice_number,
            ]);
        } else {
            // Buat baru jika belum ada
            $cashAccount = ChartOfAccount::active()->where('type', 'kas')->ordered()->first();
            if ($cashAccount) {
                CashTransaction::create([
                    'transaction_date' => $purchase->purchase_date,
                    'transaction_type' => 'credit',
                    'chart_of_account_id' => $cashAccount->id,
                    'amount' => $purchase->total,
                    'description' => 'Pengeluaran kas untuk pembelian: ' . $purchase->invoice_number,
                    'reference' => $purchase->invoice_number,
                    'purchase_id' => $purchase->id,
                ]);
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pembelian berhasil diperbarui. Transaksi kas diperbarui.',
            ]);
        }

        return redirect()->route('admin.keuangan.transaksi.pembelian')
            ->with('success', 'Pembelian berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $purchase = Purchase::findOrFail($id);
        
        // Hapus cash transaction terkait
        if ($purchase->cashTransaction) {
            $purchase->cashTransaction->delete();
        }
        
        $purchase->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pembelian berhasil dihapus. Transaksi kas terkait juga dihapus.',
            ]);
        }

        return redirect()->route('admin.keuangan.transaksi.pembelian')
            ->with('success', 'Pembelian berhasil dihapus.');
    }

    /**
     * Tampilkan invoice pembelian (untuk cetak).
     */
    public function invoice(string $id)
    {
        $purchase = Purchase::with('supplier')->findOrFail($id);
        return view('admin.purchases.invoice', compact('purchase'));
    }
}
