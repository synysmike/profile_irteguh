<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvestorDividendAllocation;
use App\Models\InvestorDividendPeriod;
use App\Services\InvestorDividendService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InvestorDividendController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $periods = InvestorDividendPeriod::withCount([
            'allocations',
            'allocations as pending_count' => fn ($q) => $q->where('status', 'pending')->where('amount', '>', 0),
            'allocations as paid_count' => fn ($q) => $q->where('status', 'paid'),
        ])
            ->withSum(['allocations as pending_amount' => fn ($q) => $q->where('status', 'pending')], 'amount')
            ->withSum(['allocations as paid_amount' => fn ($q) => $q->where('status', 'paid')], 'amount')
            ->latestFirst()
            ->get();

        $pendingQueue = InvestorDividendAllocation::query()
            ->with(['investor', 'period', 'capitalInjection'])
            ->where('status', 'pending')
            ->where('amount', '>', 0)
            ->latest('id')
            ->limit(50)
            ->get();

        $totalPending = (float) InvestorDividendAllocation::query()
            ->where('status', 'pending')
            ->where('amount', '>', 0)
            ->sum('amount');

        $totalPaid = (float) InvestorDividendAllocation::query()
            ->where('status', 'paid')
            ->sum('amount');

        $paidThisMonth = (float) InvestorDividendAllocation::query()
            ->where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        $filter = $request->get('tab', 'periods');

        return view('admin.keuangan.dividen-investor.index', compact(
            'periods',
            'pendingQueue',
            'totalPending',
            'totalPaid',
            'paidThisMonth',
            'filter'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_label' => 'required|string|max:120',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'profit_base_amount' => 'required|numeric',
            'notes' => 'nullable|string|max:2000',
        ]);

        InvestorDividendPeriod::create([
            ...$validated,
            'status' => 'draft',
        ]);

        return redirect()->route('admin.keuangan.dividen-investor.index')
            ->with('success', 'Periode dividen dibuat.');
    }

    public function show(Request $request, InvestorDividendPeriod $period)
    {
        $period->load(['allocations.investor', 'allocations.capitalInjection', 'allocations.cashTransaction']);

        $tab = $request->get('tab', 'all');
        $allocations = $period->allocations;
        if ($tab === 'pending') {
            $allocations = $allocations->where('status', 'pending')->values();
        } elseif ($tab === 'paid') {
            $allocations = $allocations->where('status', 'paid')->values();
        }

        $summary = [
            'total' => (float) $period->allocations->sum('amount'),
            'pending' => (float) $period->allocations->where('status', 'pending')->sum('amount'),
            'paid' => (float) $period->allocations->where('status', 'paid')->sum('amount'),
            'pending_count' => $period->allocations->where('status', 'pending')->where('amount', '>', 0)->count(),
            'paid_count' => $period->allocations->where('status', 'paid')->count(),
            'missing_bank' => $period->allocations
                ->where('status', 'pending')
                ->where('amount', '>', 0)
                ->filter(fn ($a) => ! $a->investor?->hasBankAccount())
                ->count(),
        ];

        return view('admin.keuangan.dividen-investor.show', [
            'period' => $period,
            'allocations' => $allocations,
            'tab' => $tab,
            'summary' => $summary,
        ]);
    }

    public function calculate(InvestorDividendPeriod $period, InvestorDividendService $service): RedirectResponse
    {
        try {
            $service->calculate($period);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('admin.keuangan.dividen-investor.show', $period)
            ->with('success', 'Alokasi dividen dihitung dari setoran modal yang sudah lunas.');
    }

    public function updateAllocation(Request $request, InvestorDividendAllocation $allocation): RedirectResponse
    {
        if ($allocation->status === 'paid') {
            return back()->with('error', 'Alokasi sudah dibayar.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $allocation->update(['amount' => $validated['amount']]);

        return back()->with('success', 'Nominal alokasi diperbarui.');
    }

    public function payAllocation(Request $request, InvestorDividendAllocation $allocation, InvestorDividendService $service): RedirectResponse
    {
        $validated = $request->validate([
            'payment_date' => 'nullable|date',
            'payment_reference' => 'nullable|string|max:120',
            'payment_note' => 'nullable|string|max:1000',
        ]);

        try {
            $service->payAllocation($allocation, $validated);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'Pembayaran dividen dicatat ke kas keluar (sumber Dividen Investor).');
    }

    public function payAll(Request $request, InvestorDividendPeriod $period, InvestorDividendService $service): RedirectResponse
    {
        $validated = $request->validate([
            'payment_date' => 'nullable|date',
            'payment_reference' => 'nullable|string|max:120',
            'payment_note' => 'nullable|string|max:1000',
        ]);

        try {
            $count = $service->payAllPending($period, $validated);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', "{$count} pembayaran dividen diproses.");
    }
}
