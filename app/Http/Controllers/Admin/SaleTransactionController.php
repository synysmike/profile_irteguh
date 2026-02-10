<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaleTransaction;
use Illuminate\Http\Request;

class SaleTransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $transactions = SaleTransaction::orderBy('description')->get();
        return view('admin.keuangan.transaksi.sale-transactions', compact('transactions'));
    }

    public function create()
    {
        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.sale-transactions.partials.form', ['transaction' => null])->render()
            ]);
        }
        return redirect()->route('admin.keuangan.sale-transactions.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:sale_transactions,code',
            'description' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|string|in:0,1',
        ]);

        $validated['subtotal'] = $validated['quantity'] * $validated['unit_price'];
        $validated['is_active'] = $request->input('is_active', '0') == '1';

        $transaction = SaleTransaction::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil ditambahkan.',
            ]);
        }

        return redirect()->route('admin.keuangan.sale-transactions.index')
            ->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $transaction = SaleTransaction::findOrFail($id);

        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.sale-transactions.partials.form', ['transaction' => $transaction])->render(),
                'data' => $transaction
            ]);
        }

        return redirect()->route('admin.keuangan.sale-transactions.index');
    }

    public function update(Request $request, string $id)
    {
        $transaction = SaleTransaction::findOrFail($id);

        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:sale_transactions,code,' . $id,
            'description' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|string|in:0,1',
        ]);

        $validated['subtotal'] = $validated['quantity'] * $validated['unit_price'];
        $validated['is_active'] = $request->input('is_active', '0') == '1';

        $transaction->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diperbarui.',
            ]);
        }

        return redirect()->route('admin.keuangan.sale-transactions.index')
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $transaction = SaleTransaction::findOrFail($id);
        $transaction->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.keuangan.sale-transactions.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }
}
