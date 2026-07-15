<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Project;
use App\Models\ProjectPaymentTerm;
use App\Models\Tax;
use App\Services\ProjectFinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $projects = Project::with(['customer', 'tax', 'paymentTerms'])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.projects.index', compact('projects'));
    }

    public function show(string $id)
    {
        $project = Project::with(['customer', 'tax', 'paymentTerms.sale', 'sales'])
            ->findOrFail($id);

        return view('admin.projects.show', compact('project'));
    }

    public function create()
    {
        $customers = Customer::active()->orderBy('name')->get();
        $taxes = Tax::where('is_active', true)->orderBy('name')->get();

        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.projects.partials.form', [
                    'project' => null,
                    'customers' => $customers,
                    'taxes' => $taxes,
                ])->render(),
            ]);
        }

        return view('admin.projects.create', compact('customers', 'taxes'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProject($request);

        DB::beginTransaction();
        try {
            $tax = !empty($validated['tax_id']) ? Tax::find($validated['tax_id']) : null;
            $amounts = ProjectFinanceService::applyTax((float) $validated['subtotal'], $tax);

            $project = Project::create([
                'code' => Project::generateCode(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'customer_id' => $validated['customer_id'],
                'tax_id' => $validated['tax_id'] ?? null,
                'status' => $validated['status'],
                'progress_percent' => $validated['progress_percent'] ?? 0,
                'subtotal' => $amounts['subtotal'],
                'ppn_amount' => $amounts['ppn_amount'],
                'tax_name' => $amounts['tax_name'],
                'tax_rate' => $amounts['tax_rate'],
                'tax_calculation_type' => $amounts['tax_calculation_type'],
                'total' => $amounts['total'],
                'payment_method' => $validated['payment_method'],
                'start_date' => $validated['start_date'] ?? null,
                'due_date' => $validated['due_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->syncTermsForProject($project, $validated);

            DB::commit();

            return $this->respondSuccess($request, 'Project berhasil ditambahkan.', $project);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->respondError($request, $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $project = Project::with('paymentTerms')->findOrFail($id);
        $customers = Customer::active()->orderBy('name')->get();
        $taxes = Tax::where('is_active', true)->orderBy('name')->get();

        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.projects.partials.form', compact('project', 'customers', 'taxes'))->render(),
                'data' => $project,
            ]);
        }

        return view('admin.projects.edit', compact('project', 'customers', 'taxes'));
    }

    public function update(Request $request, string $id)
    {
        $project = Project::with('paymentTerms')->findOrFail($id);
        $validated = $this->validateProject($request, $project);

        DB::beginTransaction();
        try {
            $tax = !empty($validated['tax_id']) ? Tax::find($validated['tax_id']) : null;
            $amounts = ProjectFinanceService::applyTax((float) $validated['subtotal'], $tax);

            $project->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'customer_id' => $validated['customer_id'],
                'tax_id' => $validated['tax_id'] ?? null,
                'status' => $validated['status'],
                'progress_percent' => $validated['progress_percent'] ?? 0,
                'subtotal' => $amounts['subtotal'],
                'ppn_amount' => $amounts['ppn_amount'],
                'tax_name' => $amounts['tax_name'],
                'tax_rate' => $amounts['tax_rate'],
                'tax_calculation_type' => $amounts['tax_calculation_type'],
                'total' => $amounts['total'],
                'payment_method' => $validated['payment_method'],
                'start_date' => $validated['start_date'] ?? null,
                'due_date' => $validated['due_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Keep paid terms; syncPaymentTerms only replaces unpaid rows.
            $this->syncTermsForProject($project, $validated);

            DB::commit();

            return $this->respondSuccess($request, 'Project berhasil diperbarui.', $project);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->respondError($request, $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $project = Project::findOrFail($id);

        if ($project->paymentTerms()->where('status', 'paid')->exists()) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project tidak bisa dihapus karena sudah ada pembayaran/invoice.',
                ], 422);
            }

            return back()->with('error', 'Project tidak bisa dihapus karena sudah ada pembayaran/invoice.');
        }

        $project->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Project berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.projects.index')->with('success', 'Project berhasil dihapus.');
    }

    public function updateStatus(Request $request, string $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Project::statusLabels()))],
            'progress_percent' => 'nullable|integer|min:0|max:100',
        ]);

        $project->update([
            'status' => $validated['status'],
            'progress_percent' => $validated['progress_percent'] ?? $project->progress_percent,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status project diperbarui.',
                'data' => $project,
            ]);
        }

        return back()->with('success', 'Status project diperbarui.');
    }

    public function payTerm(Request $request, string $projectId, string $termId)
    {
        $term = ProjectPaymentTerm::where('project_id', $projectId)->findOrFail($termId);

        $validated = $request->validate([
            'payment_date' => 'nullable|date',
        ]);

        try {
            $sale = ProjectFinanceService::postTermPayment($term, $validated['payment_date'] ?? null);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pembayaran termin berhasil diposting ke penjualan & kas.',
                    'invoice_url' => route('admin.sales.invoice', $sale->id),
                ]);
            }

            return redirect()
                ->route('admin.projects.show', $projectId)
                ->with('success', 'Pembayaran termin berhasil diposting ke penjualan & kas.');
        } catch (\Throwable $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function unpayTerm(Request $request, string $projectId, string $termId)
    {
        $term = ProjectPaymentTerm::where('project_id', $projectId)->findOrFail($termId);

        try {
            ProjectFinanceService::reverseTermPayment($term);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status termin dikembalikan ke belum terbayar. Invoice & kas terkait dihapus.',
                ]);
            }

            return redirect()
                ->route('admin.projects.show', $projectId)
                ->with('success', 'Status termin dikembalikan ke belum terbayar. Invoice & kas terkait dihapus.');
        } catch (\Throwable $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    private function validateProject(Request $request, ?Project $project = null): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer_id' => 'required|exists:customers,id',
            'tax_id' => 'nullable|exists:taxes,id',
            'status' => ['required', Rule::in(array_keys(Project::statusLabels()))],
            'progress_percent' => 'nullable|integer|min:0|max:100',
            'subtotal' => 'required|numeric|min:0',
            'payment_method' => 'required|in:full,installment',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
            'dp' => 'nullable|array',
            'dp.label' => 'nullable|string|max:120',
            'dp.percentage' => 'nullable|numeric|min:0|max:100',
            'dp.due_date' => 'nullable|date',
            'dp.term_id' => 'nullable|integer',
            'terms' => 'nullable|array',
            'terms.*.label' => 'nullable|string|max:120',
            'terms.*.percentage' => 'nullable|numeric|min:0|max:100',
            'terms.*.due_date' => 'nullable|date',
        ]);

        if ($validated['payment_method'] === 'installment') {
            $existingTerms = $project ? $project->paymentTerms : collect();
            $existingDp = $existingTerms->first(fn ($t) => strcasecmp((string) $t->label, 'DP') === 0)
                ?? $existingTerms->sortBy('term_number')->first();
            $dpIsPaid = $existingDp && $existingDp->status === 'paid';

            // Paid *additional* terms only — DP is always counted from the form.
            $paidExtraPercent = round(
                (float) $existingTerms
                    ->where('status', 'paid')
                    ->when($existingDp, fn ($c) => $c->where('id', '!=', $existingDp->id))
                    ->sum('percentage'),
                2
            );

            $dpLabel = trim((string) ($validated['dp']['label'] ?? 'DP')) ?: 'DP';
            $dpPercent = (float) str_replace(',', '.', (string) ($validated['dp']['percentage'] ?? 0));
            if ($dpPercent <= 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'dp.percentage' => ['Persentase DP wajib diisi dan harus lebih dari 0.'],
                ]);
            }

            $dpPayload = [
                'label' => $dpLabel,
                'percentage' => $dpPercent,
                'due_date' => $validated['dp']['due_date'] ?? null,
            ];

            $extras = collect($validated['terms'] ?? [])
                ->map(function ($t) {
                    return [
                        'label' => trim((string) ($t['label'] ?? '')),
                        'percentage' => (float) str_replace(',', '.', (string) ($t['percentage'] ?? 0)),
                        'due_date' => $t['due_date'] ?? null,
                    ];
                })
                ->filter(function ($t) {
                    if ($t['label'] === '' || $t['percentage'] <= 0) {
                        return false;
                    }
                    return strcasecmp($t['label'], 'DP') !== 0;
                })
                ->values();

            $editableSum = round($dpPercent + $extras->sum(fn ($t) => $t['percentage']), 2);
            $sum = round($paidExtraPercent + $editableSum, 2);
            if (abs($sum - 100) > 0.01) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'terms' => [
                        'Total persentase harus 100% (termin tambahan lunas '
                        . number_format($paidExtraPercent, 2, '.', '')
                        . '% + DP '
                        . number_format($dpPercent, 2, '.', '')
                        . '% + termin tambahan '
                        . number_format($extras->sum(fn ($t) => $t['percentage']), 2, '.', '')
                        . '% = '
                        . number_format($sum, 2, '.', '')
                        . '%).',
                    ],
                ]);
            }

            $validated['dp_is_paid'] = $dpIsPaid;
            $validated['dp_term_id'] = $dpIsPaid ? ($existingDp->id ?? null) : null;
            $validated['dp_payload'] = $dpPayload;
            // Unpaid sync payload: recreate DP only when not paid; always recreate unpaid extras.
            $validated['terms'] = $dpIsPaid
                ? $extras->all()
                : collect([$dpPayload])->concat($extras)->values()->all();
        }

        if ($project && $project->paymentTerms()->where('status', 'paid')->exists()) {
            if ((float) $validated['subtotal'] !== (float) $project->subtotal) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'subtotal' => ['Nilai project tidak bisa diubah setelah ada termin yang sudah dibayar.'],
                ]);
            }
        }

        return $validated;
    }

    private function syncTermsForProject(Project $project, array $validated): void
    {
        if ($validated['payment_method'] === 'full') {
            ProjectFinanceService::rebuildFullPaymentTerm($project);
            return;
        }

        if (!empty($validated['dp_is_paid']) && !empty($validated['dp_term_id'])) {
            ProjectFinanceService::updateExistingTerm(
                $project,
                (int) $validated['dp_term_id'],
                $validated['dp_payload'] ?? []
            );
        }

        ProjectFinanceService::syncPaymentTerms($project, $validated['terms'] ?? []);
    }

    private function respondSuccess(Request $request, string $message, Project $project)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $project,
            ]);
        }

        return redirect()->route('admin.projects.index')->with('success', $message);
    }

    private function respondError(Request $request, string $message)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return back()->withInput()->with('error', $message);
    }
}
