<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashTransaction;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CashTransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return redirect()->route('admin.keuangan.transaksi.kas-bank');
    }

    public function create()
    {
        if (request()->ajax()) {
            $accounts = ChartOfAccount::active()->where('type', 'kas')->ordered()->get();
            return response()->json([
                'html' => view('admin.cash-transactions.partials.form', ['cashTransaction' => null, 'accounts' => $accounts])->render()
            ]);
        }
        return redirect()->route('admin.keuangan.transaksi.kas-bank');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:debit,credit',
            'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'reference' => 'nullable|string|max:100',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // max 5MB
        ]);

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $filename = 'cash_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('cash-transactions', $filename, 'public');
            $validated['document_path'] = $path;
        }

        unset($validated['document']);

        CashTransaction::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi kas berhasil ditambahkan.',
            ]);
        }

        return redirect()->route('admin.keuangan.transaksi.kas-bank')
            ->with('success', 'Transaksi kas berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $cashTransaction = CashTransaction::with('chartOfAccount')->findOrFail($id);

        if (request()->ajax()) {
            $accounts = ChartOfAccount::active()->where('type', 'kas')->ordered()->get();
            return response()->json([
                'html' => view('admin.cash-transactions.partials.form', ['cashTransaction' => $cashTransaction, 'accounts' => $accounts])->render(),
                'data' => $cashTransaction
            ]);
        }

        return redirect()->route('admin.keuangan.transaksi.kas-bank');
    }

    public function update(Request $request, string $id)
    {
        $cashTransaction = CashTransaction::findOrFail($id);

        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:debit,credit',
            'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'reference' => 'nullable|string|max:100',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // max 5MB
            'remove_document' => 'nullable|boolean',
        ]);

        // Handle file upload
        if ($request->hasFile('document')) {
            // Hapus file lama jika ada
            if ($cashTransaction->document_path && Storage::disk('public')->exists($cashTransaction->document_path)) {
                Storage::disk('public')->delete($cashTransaction->document_path);
            }
            // Upload file baru
            $file = $request->file('document');
            $filename = 'cash_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('cash-transactions', $filename, 'public');
            $validated['document_path'] = $path;
        } elseif ($request->has('remove_document') && $request->remove_document) {
            // Hapus file jika user memilih hapus
            if ($cashTransaction->document_path && Storage::disk('public')->exists($cashTransaction->document_path)) {
                Storage::disk('public')->delete($cashTransaction->document_path);
            }
            $validated['document_path'] = null;
        }

        unset($validated['document'], $validated['remove_document']);

        $cashTransaction->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi kas berhasil diperbarui.',
            ]);
        }

        return redirect()->route('admin.keuangan.transaksi.kas-bank')
            ->with('success', 'Transaksi kas berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $cashTransaction = CashTransaction::findOrFail($id);
        
        // Hapus file jika ada
        if ($cashTransaction->document_path && Storage::disk('public')->exists($cashTransaction->document_path)) {
            Storage::disk('public')->delete($cashTransaction->document_path);
        }
        
        $cashTransaction->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi kas berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.keuangan.transaksi.kas-bank')
            ->with('success', 'Transaksi kas berhasil dihapus.');
    }

    public function download(string $id)
    {
        $cashTransaction = CashTransaction::findOrFail($id);
        
        if (!$cashTransaction->document_path || !Storage::disk('public')->exists($cashTransaction->document_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($cashTransaction->document_path);
    }
}
