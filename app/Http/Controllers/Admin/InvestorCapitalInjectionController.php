<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Investor;
use App\Models\InvestorBenefitPackage;
use App\Models\InvestorCapitalInjection;
use App\Models\Setting;
use App\Models\SumberDana;
use App\Services\SumberDanaResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvestorCapitalInjectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $injections = InvestorCapitalInjection::with(['investor', 'benefitPackage', 'sumberDana'])
            ->latestFirst()
            ->get();

        return view('admin.keuangan.modal-investor.index', [
            'injections' => $injections,
            'investors' => Investor::active()->orderBy('name')->get(),
            'packages' => InvestorBenefitPackage::active()->ordered()->get(),
            'sumberDanas' => SumberDana::active()->forFlow('debit')->ordered()->get(),
            'defaultSumberDanaId' => app(SumberDanaResolver::class)->modalInvestorId(),
            'equityAccount' => ChartOfAccount::active()->where('code', '3-3100')->first(),
            'bankName' => Setting::investorBankName(),
            'bankAccount' => Setting::investorBankAccount(),
            'bankHolder' => Setting::investorBankHolder(),
        ]);
    }

    public function updatePaymentAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'investor_bank_name' => 'nullable|string|max:120',
            'investor_bank_account' => 'nullable|string|max:60',
            'investor_bank_holder' => 'nullable|string|max:120',
        ]);

        Setting::set('investor_bank_name', trim((string) ($validated['investor_bank_name'] ?? '')));
        Setting::set('investor_bank_account', trim((string) ($validated['investor_bank_account'] ?? '')));
        Setting::set('investor_bank_holder', trim((string) ($validated['investor_bank_holder'] ?? '')));

        return redirect()->route('admin.keuangan.modal-investor.index')
            ->with('success', 'Rekening penerima setoran investor disimpan.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateInjection($request);
        $package = InvestorBenefitPackage::findOrFail($validated['benefit_package_id']);

        $injection = InvestorCapitalInjection::create([
            'invoice_number' => InvestorCapitalInjection::generateInvoiceNumber(),
            'investor_id' => $validated['investor_id'],
            'benefit_package_id' => $package->id,
            'injection_date' => $validated['injection_date'],
            'amount' => $validated['amount'],
            'sumber_dana_id' => $validated['sumber_dana_id'],
            'equity_account_id' => ChartOfAccount::active()->where('code', '3-3100')->value('id'),
            'benefit_name' => $package->name,
            'profit_share_percent' => $package->profit_share_percent,
            'loss_share_percent' => $package->loss_share_percent,
            'terms_content' => $validated['terms_content'] ?: $package->terms_template,
            'status' => 'pending_approval',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.keuangan.modal-investor.index')
            ->with('success', 'Setoran dibuat. Bagikan link invoice ke investor untuk persetujuan.')
            ->with('invoice_link', $injection->publicInvoiceUrl());
    }

    public function update(Request $request, InvestorCapitalInjection $injection): RedirectResponse
    {
        if (! $injection->isEditable()) {
            return back()->with('error', 'Setoran yang sudah disetujui/ditolak tidak bisa diedit.');
        }

        $validated = $this->validateInjection($request);
        $package = InvestorBenefitPackage::findOrFail($validated['benefit_package_id']);

        $injection->update([
            'investor_id' => $validated['investor_id'],
            'benefit_package_id' => $package->id,
            'injection_date' => $validated['injection_date'],
            'amount' => $validated['amount'],
            'sumber_dana_id' => $validated['sumber_dana_id'],
            'benefit_name' => $package->name,
            'profit_share_percent' => $package->profit_share_percent,
            'loss_share_percent' => $package->loss_share_percent,
            'terms_content' => $validated['terms_content'] ?: $package->terms_template,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.keuangan.modal-investor.index')
            ->with('success', 'Setoran diperbarui.');
    }

    public function destroy(InvestorCapitalInjection $injection): RedirectResponse
    {
        if (! $injection->isEditable()) {
            return back()->with('error', 'Setoran yang sudah final tidak bisa dihapus.');
        }

        $injection->delete();

        return redirect()->route('admin.keuangan.modal-investor.index')
            ->with('success', 'Setoran dihapus.');
    }

    private function validateInjection(Request $request): array
    {
        $packageId = $request->input('benefit_package_id');
        $minDeposit = 1;
        if ($packageId) {
            $minDeposit = (float) (InvestorBenefitPackage::where('id', $packageId)->value('min_deposit_amount') ?: 0);
            if ($minDeposit <= 0) {
                $minDeposit = 1;
            }
        }

        return $request->validate([
            'investor_id' => 'required|exists:investors,id',
            'benefit_package_id' => 'required|exists:investor_benefit_packages,id',
            'injection_date' => 'required|date',
            'amount' => [
                'required',
                'numeric',
                'min:' . $minDeposit,
            ],
            'sumber_dana_id' => 'required|exists:sumber_danas,id',
            'terms_content' => 'nullable|string',
            'notes' => 'nullable|string|max:2000',
        ], [
            'amount.min' => 'Nominal setoran minimal Rp ' . number_format($minDeposit, 0, ',', '.') . ' sesuai paket benefit.',
        ]);
    }
}
