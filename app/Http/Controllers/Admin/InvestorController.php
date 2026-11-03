<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Investor;
use App\Models\InvestorDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvestorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $investors = Investor::query()
            ->withCount(['capitalInjections', 'documents'])
            ->orderBy('name')
            ->get();

        return view('admin.keuangan.investor.index', compact('investors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateInvestor($request);
        Investor::create($validated);

        return redirect()->route('admin.keuangan.investor.index')
            ->with('success', 'Investor berhasil ditambahkan.');
    }

    public function update(Request $request, Investor $investor): RedirectResponse
    {
        $validated = $this->validateInvestor($request);
        $investor->update($validated);

        return redirect()->route('admin.keuangan.investor.index')
            ->with('success', 'Investor berhasil diperbarui.');
    }

    public function destroy(Investor $investor): RedirectResponse
    {
        if ($investor->capitalInjections()->exists()) {
            return redirect()->route('admin.keuangan.investor.index')
                ->with('error', 'Investor tidak bisa dihapus karena sudah punya setoran modal.');
        }

        $investor->delete();

        return redirect()->route('admin.keuangan.investor.index')
            ->with('success', 'Investor berhasil dihapus.');
    }

    public function storeDocument(Request $request, Investor $investor): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:180',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'capital_injection_id' => 'nullable|exists:investor_capital_injections,id',
        ]);

        $path = $request->file('document')->store('investor-documents', 'public');

        InvestorDocument::create([
            'investor_id' => $investor->id,
            'capital_injection_id' => $validated['capital_injection_id'] ?? null,
            'title' => $validated['title'],
            'file_path' => $path,
            'uploaded_by' => $request->user()?->id,
        ]);

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function destroyDocument(InvestorDocument $document): RedirectResponse
    {
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();

        return back()->with('success', 'Dokumen dihapus.');
    }

    private function validateInvestor(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:180',
            'phone' => 'required|string|max:30',
            'bank_name' => 'nullable|string|max:120',
            'bank_account' => 'nullable|string|max:60',
            'bank_account_name' => 'nullable|string|max:120',
            'email' => 'nullable|email|max:180',
            'address' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
            'is_active' => 'nullable',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['bank_name'] = trim((string) ($validated['bank_name'] ?? '')) ?: null;
        $validated['bank_account'] = trim((string) ($validated['bank_account'] ?? '')) ?: null;
        $validated['bank_account_name'] = trim((string) ($validated['bank_account_name'] ?? '')) ?: null;

        return $validated;
    }
}
