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

            if ($project->paymentTerms()->where('status', 'paid')->doesntExist()) {
                $this->syncTermsForProject($project, $validated);
            }

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
            'terms' => 'nullable|array',
            'terms.*.label' => 'nullable|string|max:120',
            'terms.*.percentage' => 'nullable|numeric|min:0|max:100',
            'terms.*.due_date' => 'nullable|date',
        ]);

        if ($validated['payment_method'] === 'installment') {
            $terms = collect($validated['terms'] ?? [])->filter(fn ($t) => !empty($t['label']));
            if ($terms->isEmpty()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'terms' => ['Minimal 1 termin harus diisi untuk metode cicilan.'],
                ]);
            }

            $sum = round($terms->sum(fn ($t) => (float) ($t['percentage'] ?? 0)), 2);
            if (abs($sum - 100) > 0.01) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'terms' => ['Total persentase termin harus 100%. Saat ini: ' . $sum . '%'],
                ]);
            }
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
