<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvestorBenefitPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvestorBenefitPackageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $packages = InvestorBenefitPackage::query()
            ->withCount('capitalInjections')
            ->ordered()
            ->get();

        return view('admin.keuangan.benefit-investor.index', compact('packages'));
    }

    public function store(Request $request): RedirectResponse
    {
        InvestorBenefitPackage::create($this->validatePackage($request));

        return redirect()->route('admin.keuangan.benefit-investor.index')
            ->with('success', 'Paket benefit berhasil ditambahkan.');
    }

    public function update(Request $request, InvestorBenefitPackage $package): RedirectResponse
    {
        $package->update($this->validatePackage($request, $package));

        return redirect()->route('admin.keuangan.benefit-investor.index')
            ->with('success', 'Paket benefit berhasil diperbarui.');
    }

    public function destroy(InvestorBenefitPackage $package): RedirectResponse
    {
        if ($package->capitalInjections()->exists()) {
            return redirect()->route('admin.keuangan.benefit-investor.index')
                ->with('error', 'Paket tidak bisa dihapus karena sudah dipakai setoran. Nonaktifkan saja.');
        }

        $package->delete();

        return redirect()->route('admin.keuangan.benefit-investor.index')
            ->with('success', 'Paket benefit dihapus.');
    }

    private function validatePackage(Request $request, ?InvestorBenefitPackage $package = null): array
    {
        $validated = $request->validate([
            'code' => [
                'required', 'string', 'max:30',
                Rule::unique('investor_benefit_packages', 'code')->ignore($package?->id),
            ],
            'name' => 'required|string|max:120',
            'profit_share_percent' => 'required|numeric|min:0|max:100',
            'loss_share_percent' => 'required|numeric|min:0|max:100',
            'min_deposit_amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            'terms_template' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 100);
        $validated['min_deposit_amount'] = (float) $validated['min_deposit_amount'];

        return $validated;
    }
}
