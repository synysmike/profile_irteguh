<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $accounts = ChartOfAccount::ordered()->get();
        return view('admin.keuangan.chart-of-accounts.index', compact('accounts'));
    }

    public function create()
    {
        $parents = ChartOfAccount::ordered()->get();
        return view('admin.keuangan.chart-of-accounts.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:chart_of_accounts,code',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:kas,piutang,hutang,modal,pendapatan,beban',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['order'] = $validated['order'] ?? 0;

        ChartOfAccount::create($validated);

        return redirect()->route('admin.keuangan.chart-of-accounts.index')
            ->with('success', 'Akun perkiraan berhasil ditambahkan.');
    }

    public function show(ChartOfAccount $chart_of_account)
    {
        $account = $chart_of_account->load('parent', 'children');
        return view('admin.keuangan.chart-of-accounts.show', compact('account'));
    }

    public function edit(ChartOfAccount $chart_of_account)
    {
        $account = $chart_of_account;
        $parents = ChartOfAccount::where('id', '!=', $account->id)->ordered()->get();
        return view('admin.keuangan.chart-of-accounts.edit', compact('account', 'parents'));
    }

    public function update(Request $request, ChartOfAccount $chart_of_account)
    {
        $account = $chart_of_account;

        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:chart_of_accounts,code,' . $account->id,
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:kas,piutang,hutang,modal,pendapatan,beban',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['order'] = $validated['order'] ?? $account->order;

        $account->update($validated);

        return redirect()->route('admin.keuangan.chart-of-accounts.index')
            ->with('success', 'Akun perkiraan berhasil diperbarui.');
    }

    public function destroy(ChartOfAccount $chart_of_account)
    {
        $account = $chart_of_account;

        if ($account->children()->exists()) {
            return redirect()->route('admin.keuangan.chart-of-accounts.index')
                ->with('error', 'Akun ini memiliki sub-akun. Hapus sub-akun terlebih dahulu.');
        }

        $account->delete();

        return redirect()->route('admin.keuangan.chart-of-accounts.index')
            ->with('success', 'Akun perkiraan berhasil dihapus.');
    }
}
