<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Tax;
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
            $taxes = Tax::where('is_active', true)->orderBy('name')->get();
            return response()->json([
                'html' => view('admin.purchases.partials.form', ['purchase' => null, 'suppliers' => $suppliers, 'taxes' => $taxes])->render()
            ]);
        }
        return redirect()->route('admin.keuangan.transaksi.pembelian');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'tax_id' => 'nullable|exists:taxes,id',
            'invoice_number' => 'required|string|max:50|unique:purchases,invoice_number',
            'purchase_date' => 'required|date',
            'description' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['subtotal'] = $validated['quantity'] * $validated['unit_price'];

        $tax = !empty($validated['tax_id']) ? Tax::find($validated['tax_id']) : null;
        $taxAmount = $tax ? $tax->calculateAmount((float) $validated['subtotal']) : 0;
        $validated['ppn_amount'] = $taxAmount;
        $validated['tax_name'] = $tax?->name;
        $validated['tax_rate'] = $tax?->rate;
        $validated['tax_calculation_type'] = $tax?->calculation_type;
        $validated['total'] = $tax && $tax->isDeduction()
            ? ($validated['subtotal'] - $taxAmount)
            : ($validated['subtotal'] + $taxAmount);

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
                'message' => 'Grosir berhasil ditambahkan. Transaksi kas otomatis dibuat.',
            ]);
        }

        return redirect()->route('admin.keuangan.transaksi.pembelian')
            ->with('success', 'Grosir berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $purchase = Purchase::with('supplier')->findOrFail($id);

        if (request()->ajax()) {
            $suppliers = Supplier::active()->orderBy('name')->get();
            $taxes = Tax::where('is_active', true)->orderBy('name')->get();
            return response()->json([
                'html' => view('admin.purchases.partials.form', ['purchase' => $purchase, 'suppliers' => $suppliers, 'taxes' => $taxes])->render(),
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
            'tax_id' => 'nullable|exists:taxes,id',
            'invoice_number' => 'required|string|max:50|unique:purchases,invoice_number,' . $id,
            'purchase_date' => 'required|date',
            'description' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $allocated = $purchase->allocatedQuantity();
        if ($validated['quantity'] < $allocated) {
            $message = "Qty tidak boleh kurang dari {$allocated} unit yang sudah dialokasikan ke transaksi penjualan.";
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => ['quantity' => [$message]],
                ], 422);
            }

            return back()->withInput()->withErrors(['quantity' => $message]);
        }

        $validated['subtotal'] = $validated['quantity'] * $validated['unit_price'];

        $tax = !empty($validated['tax_id']) ? Tax::find($validated['tax_id']) : null;
        $taxAmount = $tax ? $tax->calculateAmount((float) $validated['subtotal']) : 0;
        $validated['ppn_amount'] = $taxAmount;
        $validated['tax_name'] = $tax?->name;
        $validated['tax_rate'] = $tax?->rate;
        $validated['tax_calculation_type'] = $tax?->calculation_type;
        $validated['total'] = $tax && $tax->isDeduction()
            ? ($validated['subtotal'] - $taxAmount)
            : ($validated['subtotal'] + $taxAmount);

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
                'message' => 'Grosir berhasil diperbarui. Transaksi kas diperbarui.',
            ]);
        }

        return redirect()->route('admin.keuangan.transaksi.pembelian')
            ->with('success', 'Grosir berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $purchase = Purchase::findOrFail($id);

        if ($purchase->saleTransactions()->exists()) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grosir tidak dapat dihapus karena sudah dipakai pada transaksi penjualan.',
                ], 422);
            }

            return back()->with('error', 'Grosir tidak dapat dihapus karena sudah dipakai pada transaksi penjualan.');
        }

        // Hapus cash transaction terkait
        if ($purchase->cashTransaction) {
            $purchase->cashTransaction->delete();
        }
        
        $purchase->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Grosir berhasil dihapus. Transaksi kas terkait juga dihapus.',
            ]);
        }

        return redirect()->route('admin.keuangan.transaksi.pembelian')
            ->with('success', 'Grosir berhasil dihapus.');
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
