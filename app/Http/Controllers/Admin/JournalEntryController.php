<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class JournalEntryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return redirect()->route('admin.keuangan.jurnal.index');
    }

    public function create()
    {
        if (request()->ajax()) {
            $accounts = ChartOfAccount::active()->ordered()->get();
            return response()->json([
                'html' => view('admin.journal-entries.partials.form', ['journalEntry' => null, 'accounts' => $accounts])->render()
            ]);
        }
        return redirect()->route('admin.keuangan.jurnal.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'reference' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'chart_of_account_id' => 'required|array',
            'chart_of_account_id.*' => 'required|exists:chart_of_accounts,id',
            'debit' => 'required|array',
            'debit.*' => 'nullable|numeric|min:0',
            'credit' => 'required|array',
            'credit.*' => 'nullable|numeric|min:0',
            'memo' => 'nullable|array',
            'memo.*' => 'nullable|string|max:255',
        ]);

        $lines = [];
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($validated['chart_of_account_id'] as $i => $accountId) {
            $debit = (float) ($validated['debit'][$i] ?? 0);
            $credit = (float) ($validated['credit'][$i] ?? 0);
            if ($debit <= 0 && $credit <= 0) {
                continue;
            }
            $lines[] = [
                'chart_of_account_id' => $accountId,
                'debit' => $debit,
                'credit' => $credit,
                'memo' => $validated['memo'][$i] ?? null,
            ];
            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        if (count($lines) < 2) {
            if ($request->ajax()) {
                return response()->json(['errors' => ['lines' => ['Minimal 2 baris jurnal (debit/credit).']]], 422);
            }
            return back()->withErrors(['lines' => 'Minimal 2 baris jurnal.']);
        }

        if (abs($totalDebit - $totalCredit) > 0.01) {
            if ($request->ajax()) {
                return response()->json(['errors' => ['lines' => ['Total debit harus sama dengan total kredit.']]], 422);
            }
            return back()->withErrors(['lines' => 'Total debit harus sama dengan total kredit.']);
        }

        $entry = JournalEntry::create([
            'entry_date' => $validated['entry_date'],
            'reference' => $validated['reference'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        foreach ($lines as $line) {
            $entry->lines()->create($line);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Jurnal berhasil ditambahkan.',
            ]);
        }

        return redirect()->route('admin.keuangan.jurnal.index')
            ->with('success', 'Jurnal berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $journalEntry = JournalEntry::with('lines.chartOfAccount')->findOrFail($id);

        if (request()->ajax()) {
            $accounts = ChartOfAccount::active()->ordered()->get();
            return response()->json([
                'html' => view('admin.journal-entries.partials.form', ['journalEntry' => $journalEntry, 'accounts' => $accounts])->render(),
                'data' => $journalEntry
            ]);
        }

        return redirect()->route('admin.keuangan.jurnal.index');
    }

    public function update(Request $request, string $id)
    {
        $journalEntry = JournalEntry::with('lines')->findOrFail($id);

        $validated = $request->validate([
            'entry_date' => 'required|date',
            'reference' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'chart_of_account_id' => 'required|array',
            'chart_of_account_id.*' => 'required|exists:chart_of_accounts,id',
            'debit' => 'required|array',
            'debit.*' => 'nullable|numeric|min:0',
            'credit' => 'required|array',
            'credit.*' => 'nullable|numeric|min:0',
            'memo' => 'nullable|array',
            'memo.*' => 'nullable|string|max:255',
        ]);

        $lines = [];
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($validated['chart_of_account_id'] as $i => $accountId) {
            $debit = (float) ($validated['debit'][$i] ?? 0);
            $credit = (float) ($validated['credit'][$i] ?? 0);
            if ($debit <= 0 && $credit <= 0) {
                continue;
            }
            $lines[] = [
                'chart_of_account_id' => $accountId,
                'debit' => $debit,
                'credit' => $credit,
                'memo' => $validated['memo'][$i] ?? null,
            ];
            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        if (count($lines) < 2) {
            if ($request->ajax()) {
                return response()->json(['errors' => ['lines' => ['Minimal 2 baris jurnal (debit/credit).']]], 422);
            }
            return back()->withErrors(['lines' => 'Minimal 2 baris jurnal.']);
        }

        if (abs($totalDebit - $totalCredit) > 0.01) {
            if ($request->ajax()) {
                return response()->json(['errors' => ['lines' => ['Total debit harus sama dengan total kredit.']]], 422);
            }
            return back()->withErrors(['lines' => 'Total debit harus sama dengan total kredit.']);
        }

        $journalEntry->update([
            'entry_date' => $validated['entry_date'],
            'reference' => $validated['reference'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        $journalEntry->lines()->delete();
        foreach ($lines as $line) {
            $journalEntry->lines()->create($line);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Jurnal berhasil diperbarui.',
            ]);
        }

        return redirect()->route('admin.keuangan.jurnal.index')
            ->with('success', 'Jurnal berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $journalEntry = JournalEntry::findOrFail($id);
        $journalEntry->lines()->delete();
        $journalEntry->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Jurnal berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.keuangan.jurnal.index')
            ->with('success', 'Jurnal berhasil dihapus.');
    }
}
