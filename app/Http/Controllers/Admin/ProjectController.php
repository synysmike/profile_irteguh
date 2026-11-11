<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\Project;
use App\Models\ProjectPaymentTerm;
use App\Models\ProjectProgressMedia;
use App\Models\Purchase;
use App\Models\SaleTransaction;
use App\Models\Service;
use App\Models\Tax;
use App\Services\LayananFulfillmentService;
use App\Services\ProjectFinanceService;
use App\Support\ProjectTermWhatsAppInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $projects = Project::with(['customer', 'tax', 'paymentTerms', 'service'])
            ->orderByDesc('created_at')
            ->get();
        $services = Service::ordered()->get();

        return view('admin.projects.index', compact('projects', 'services'));
    }

    /**
     * Konfirmasi draft Order Layanan Kami (clone project) → status berjalan + termin default.
     */
    public function confirmLayanan(Project $project, LayananFulfillmentService $fulfillment)
    {
        try {
            $fulfillment->confirmProject($project);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first() ?: $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal konfirmasi project: '.$e->getMessage());
        }

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('success', "Order Layanan {$project->code} dikonfirmasi. Project aktif.");
    }

    /**
     * Tolak draft Order Layanan Kami → status cancelled.
     */
    public function rejectLayanan(Project $project, LayananFulfillmentService $fulfillment)
    {
        try {
            $fulfillment->rejectProject($project);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first() ?: $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menolak project: '.$e->getMessage());
        }

        return back()->with('success', "Draft Order Layanan {$project->code} ditolak (dibatalkan).");
    }

    /**
     * Toggle / set tampil di etalase Layanan Kami (jasa dari project template).
     */
    public function updateLayanan(Request $request, Project $project)
    {
        if ($project->status === 'cancelled') {
            return back()->with('error', 'Project dibatalkan tidak bisa dipajang di Layanan Kami.');
        }

        if ($project->is_layanan_pending) {
            return back()->with('error', 'Draft Order Layanan tidak bisa dipajang di etalase. Konfirmasi dulu atau pakai project template.');
        }

        if ($request->boolean('show_in_layanan')) {
            $validated = $request->validate([
                'service_id' => 'required|exists:services,id',
            ], [
                'service_id.required' => 'Pilih kategori layanan sebelum menampilkan di etalase.',
            ]);

            $project->update([
                'show_in_layanan' => true,
                'service_id' => (int) $validated['service_id'],
            ]);

            return back()->with('success', 'Project ditampilkan di Layanan Kami sebagai template jasa.');
        }

        $project->update([
            'show_in_layanan' => false,
            'service_id' => null,
        ]);

        return back()->with('success', 'Project disembunyikan dari Layanan Kami.');
    }

    public function show(string $id)
    {
        $project = Project::with([
            'customer',
            'tax',
            'paymentTerms.sale',
            'sales',
            'assignmentLetters.assignees',
            'assignmentLetters.jasaItems',
            'saleTransactions.purchase.supplier',
            'progressMedia',
            'reviews.customer',
            'reviews.media',
        ])->findOrFail($id);

        $availableAllocations = SaleTransaction::availableForProject()
            ->with(['purchase.supplier'])
            ->orderBy('description')
            ->get();

        $availablePurchases = Purchase::with('supplier')
            ->latestFirst()
            ->get()
            ->filter(fn (Purchase $p) => $p->remainingQuantity() > 0)
            ->values();

        $discounts = Discount::where('is_active', true)->orderBy('name')->get();

        return view('admin.projects.show', compact(
            'project',
            'availableAllocations',
            'availablePurchases',
            'discounts'
        ));
    }

    public function create()
    {
        $customers = Customer::active()->orderBy('name')->get();
        $taxes = Tax::where('is_active', true)->orderBy('name')->get();
        $discounts = Discount::where('is_active', true)->orderBy('name')->get();

        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.projects.partials.form', [
                    'project' => null,
                    'customers' => $customers,
                    'taxes' => $taxes,
                    'discounts' => $discounts,
                ])->render(),
            ]);
        }

        return view('admin.projects.create', compact('customers', 'taxes', 'discounts'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProject($request);

        DB::beginTransaction();
        try {
            $tax = !empty($validated['tax_id']) ? Tax::find($validated['tax_id']) : null;
            $discount = !empty($validated['discount_id']) ? Discount::find($validated['discount_id']) : null;
            $baseSubtotal = (float) $validated['subtotal'];
            $jasa = ProjectFinanceService::applyDiscount($baseSubtotal, $discount);
            $amounts = ProjectFinanceService::applyTax($jasa['net'], $tax);

            $portfolio = $this->portfolioPayload($request, $validated);

            $project = Project::create(array_merge([
                'code' => Project::generateCode(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'customer_id' => $validated['customer_id'],
                'tax_id' => $validated['tax_id'] ?? null,
                'status' => $validated['status'],
                'progress_percent' => $validated['progress_percent'] ?? 0,
                'base_subtotal' => $baseSubtotal,
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
            ], $portfolio, ProjectFinanceService::discountSnapshot($discount, $jasa['discount_amount'])));

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
        $project = Project::with(['paymentTerms', 'assignmentLetters.jasaItems'])->findOrFail($id);
        $customers = Customer::active()->orderBy('name')->get();
        $taxes = Tax::where('is_active', true)->orderBy('name')->get();
        $discounts = Discount::where('is_active', true)->orderBy('name')->get();

        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.projects.partials.form', compact('project', 'customers', 'taxes', 'discounts'))->render(),
                'data' => $project,
            ]);
        }

        return view('admin.projects.edit', compact('project', 'customers', 'taxes', 'discounts'));
    }

    public function update(Request $request, string $id)
    {
        $project = Project::with(['paymentTerms', 'assignmentLetters.jasaItems'])->findOrFail($id);
        $validated = $this->validateProject($request, $project);

        DB::beginTransaction();
        try {
            $hasLetterJasa = $project->assignmentLetters->flatMap->jasaItems->isNotEmpty();
            $baseSubtotal = $hasLetterJasa
                ? (float) ($project->base_subtotal ?? 0)
                : (float) $validated['subtotal'];

            $portfolio = $this->portfolioPayload($request, $validated, $project);

            $project->update(array_merge([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'customer_id' => $validated['customer_id'],
                'tax_id' => $validated['tax_id'] ?? null,
                'discount_id' => $validated['discount_id'] ?? null,
                'status' => $validated['status'],
                'progress_percent' => $validated['progress_percent'] ?? 0,
                'base_subtotal' => $baseSubtotal,
                'payment_method' => $validated['payment_method'],
                'start_date' => $validated['start_date'] ?? null,
                'due_date' => $validated['due_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ], $portfolio));

            // DPP final = jasa net + alokasi stok; pajak & termin unpaid ikut dihitung ulang.
            if ($hasLetterJasa) {
                ProjectFinanceService::syncJasaFromAssignmentLetters($project->fresh());
            } else {
                ProjectFinanceService::recalculateFromAllocations($project);
            }
            $project->refresh();

            // Keep paid terms; syncPaymentTerms only replaces unpaid rows.
            $this->syncTermsForProject($project, $validated);

            DB::commit();

            return $this->respondSuccess($request, 'Project berhasil diperbarui.', $project);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->respondError($request, $e->getMessage());
        }
    }

    public function editTerms(string $projectId)
    {
        $project = Project::with('paymentTerms')->findOrFail($projectId);

        return view('admin.projects.edit-terms', compact('project'));
    }

    public function updateTerms(Request $request, string $projectId)
    {
        $project = Project::with('paymentTerms')->findOrFail($projectId);
        $validated = $this->validatePaymentTerms($request, $project);

        DB::beginTransaction();
        try {
            $project->update([
                'payment_method' => $validated['payment_method'],
            ]);

            ProjectFinanceService::recalculateFromAllocations($project);
            $project->refresh();

            $this->syncTermsForProject($project, $validated);

            DB::commit();

            return redirect()
                ->route('admin.projects.show', $project)
                ->with('success', 'Termin pembayaran berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', $e->getMessage());
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
            'media_caption' => 'nullable|string|max:255',
            'show_in_portfolio' => 'nullable',
            'progress_media' => 'nullable|array|max:10',
            'progress_media.*' => 'file|mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime|max:51200',
        ], [
            'progress_media.*.max' => 'Setiap file maksimal 50 MB.',
            'progress_media.*.mimetypes' => 'File harus berupa gambar (JPG/PNG/WEBP) atau video (MP4/WEBM/MOV).',
        ]);

        $progress = $validated['progress_percent'] ?? $project->progress_percent;

        $project->update([
            'status' => $validated['status'],
            'progress_percent' => $progress,
        ]);

        $uploaded = 0;
        $showInPortfolio = $request->boolean('show_in_portfolio');
        $caption = trim((string) ($validated['media_caption'] ?? '')) ?: null;

        foreach ($request->file('progress_media', []) as $file) {
            if (! $file) {
                continue;
            }
            $mime = (string) $file->getMimeType();
            $type = str_starts_with($mime, 'video/') ? 'video' : 'image';
            $path = $file->store('project-progress/' . $project->id, 'public');

            ProjectProgressMedia::create([
                'project_id' => $project->id,
                'file_path' => $path,
                'media_type' => $type,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $mime,
                'file_size' => $file->getSize(),
                'caption' => $caption,
                'status_snapshot' => $validated['status'],
                'progress_snapshot' => $progress,
                'show_in_portfolio' => $showInPortfolio,
                'sort_order' => (int) $project->progressMedia()->max('sort_order') + 1,
                'uploaded_by' => $request->user()?->id,
            ]);
            $uploaded++;
        }

        $message = 'Status project diperbarui.';
        if ($uploaded > 0) {
            $message .= " {$uploaded} bukti progres diunggah.";
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $project->fresh('progressMedia'),
            ]);
        }

        return back()->with('success', $message);
    }

    public function toggleProgressMediaPortfolio(Request $request, string $projectId, string $mediaId)
    {
        $media = ProjectProgressMedia::where('project_id', $projectId)->findOrFail($mediaId);
        $media->update([
            'show_in_portfolio' => ! $media->show_in_portfolio,
        ]);

        return back()->with(
            'success',
            $media->show_in_portfolio
                ? 'Media ditampilkan di portofolio publik.'
                : 'Media disembunyikan dari portofolio publik.'
        );
    }

    public function destroyProgressMedia(string $projectId, string $mediaId)
    {
        $media = ProjectProgressMedia::where('project_id', $projectId)->findOrFail($mediaId);
        $media->delete();

        return back()->with('success', 'Bukti progres dihapus.');
    }

    public function toggleReviewPublish(string $projectId, string $reviewId)
    {
        $review = \App\Models\ProjectReview::where('project_id', $projectId)->findOrFail($reviewId);
        $review->update(['is_published' => ! $review->is_published]);

        return back()->with(
            'success',
            $review->is_published ? 'Ulasan ditampilkan di portofolio.' : 'Ulasan disembunyikan dari portofolio.'
        );
    }

    public function destroyReview(string $projectId, string $reviewId)
    {
        $review = \App\Models\ProjectReview::where('project_id', $projectId)->findOrFail($reviewId);
        $review->delete();

        return back()->with('success', 'Ulasan dihapus.');
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
                    'invoice_url' => $sale->publicInvoiceUrl(),
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

    /**
     * Halaman invoice penagihan (cetak / PDF) untuk termin yang belum diposting.
     * Arahkan ke URL publik (admin bypass verifikasi HP saat login).
     */
    public function termInvoice(string $projectId, string $termId)
    {
        $term = ProjectPaymentTerm::where('project_id', $projectId)->with('sale')->findOrFail($termId);

        return redirect()->to($term->publicInvoiceUrl());
    }

    /**
     * Kirim draft invoice termin (belum posting) via WhatsApp ke customer.
     * Status pembayaran dalam pesan selalu "Belum Lunas".
     */
    public function whatsappTerm(string $projectId, string $termId)
    {
        $project = Project::with(['customer', 'saleTransactions.purchase'])->findOrFail($projectId);
        $term = ProjectPaymentTerm::where('project_id', $projectId)->findOrFail($termId);

        if ($term->status === 'paid' || $term->sale_id) {
            return redirect()
                ->route('admin.projects.show', $project)
                ->with('error', 'Termin sudah diposting. Kirim WA dari invoice penjualan.');
        }

        if (! ProjectTermWhatsAppInvoice::destinationPhone($project)) {
            return redirect()
                ->route('admin.projects.show', $project)
                ->with('error', 'Customer project belum memiliki nomor HP. Lengkapi data customer terlebih dahulu.');
        }

        try {
            return redirect()->away(ProjectTermWhatsAppInvoice::shareUrl($project, $term));
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.projects.show', $project)
                ->with('error', $e->getMessage());
        }
    }

    private function validateProject(Request $request, ?Project $project = null): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer_id' => 'required|exists:customers,id',
            'tax_id' => 'nullable|exists:taxes,id',
            'discount_id' => 'nullable|exists:discounts,id',
            'status' => ['required', Rule::in(array_keys(Project::statusLabels()))],
            'progress_percent' => 'nullable|integer|min:0|max:100',
            'subtotal' => 'required|numeric|min:0',
            'payment_method' => 'required|in:full,installment',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
            'is_portfolio_public' => 'nullable',
            'is_portfolio_featured' => 'nullable',
            'portfolio_category' => ['nullable', 'string', Rule::in(Project::portfolioCategories())],
            'portfolio_year' => 'nullable|integer|min:2000|max:2100',
            'portfolio_sort_order' => 'nullable|integer|min:0|max:9999',
            'portfolio_client_context' => 'nullable|string|max:255',
            'portfolio_excerpt' => 'nullable|string|max:2000',
            'portfolio_challenge' => 'nullable|string',
            'portfolio_solution' => 'nullable|string',
            'portfolio_outcome' => 'nullable|string',
            'portfolio_tags' => 'nullable|string|max:500',
            'portfolio_cover' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'remove_portfolio_cover' => 'nullable',
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

        $validated = $this->normalizePaymentTermsPayload($validated, $project);

        if ($project && $project->paymentTerms()->where('status', 'paid')->exists()) {
            $lockedBase = (float) ($project->base_subtotal ?? $project->subtotal);
            if ((float) $validated['subtotal'] !== $lockedBase) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'subtotal' => ['Nilai dasar project tidak bisa diubah setelah ada termin yang sudah dibayar.'],
                ]);
            }
        }

        return $validated;
    }

    private function validatePaymentTerms(Request $request, Project $project): array
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:full,installment',
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

        return $this->normalizePaymentTermsPayload($validated, $project);
    }

    private function normalizePaymentTermsPayload(array $validated, ?Project $project = null): array
    {
        if ($validated['payment_method'] !== 'installment') {
            return $validated;
        }

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

        return $validated;
    }

    public function updateDiscount(Request $request, string $projectId)
    {
        $project = Project::findOrFail($projectId);
        $validated = $request->validate([
            'discount_id' => 'nullable|exists:discounts,id',
        ]);

        try {
            $project->update([
                'discount_id' => $validated['discount_id'] ?? null,
            ]);
            ProjectFinanceService::recalculateFromAllocations($project);

            return redirect()
                ->route('admin.projects.show', $project)
                ->with('success', 'Diskon jasa project diperbarui. DPP, pajak, dan termin dihitung ulang.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Sesuaikan qty item jasa (dari surat tugas) di detail project — memengaruhi base_subtotal & invoice.
     */
    public function updateJasaItems(Request $request, string $projectId)
    {
        $project = Project::with(['assignmentLetters.jasaItems', 'paymentTerms'])->findOrFail($projectId);

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:assignment_letter_jasa_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $itemIds = collect($validated['items'])->pluck('id')->map(fn ($id) => (int) $id)->all();
        $ownedIds = $project->assignmentLetters
            ->flatMap(fn ($letter) => $letter->jasaItems)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($itemIds as $id) {
            if (! in_array($id, $ownedIds, true)) {
                return back()->with('error', 'Item jasa tidak valid untuk project ini.');
            }
        }

        try {
            DB::transaction(function () use ($project, $validated) {
                foreach ($validated['items'] as $row) {
                    $item = \App\Models\AssignmentLetterJasaItem::findOrFail($row['id']);
                    $qty = max(1, (int) $row['quantity']);
                    $item->quantity = $qty;
                    $item->recalculateSubtotal();
                    $item->save();
                }

                ProjectFinanceService::syncJasaFromAssignmentLetters($project->fresh());
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first() ?? 'Gagal memperbarui qty jasa.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('success', 'Qty jasa diperbarui. Nilai project, termin unpaid, dan total invoice dihitung ulang.');
    }

    public function attachSaleTransaction(Request $request, string $projectId)
    {
        $project = Project::findOrFail($projectId);

        $validated = $request->validate([
            'mode' => 'nullable|in:existing,purchase',
            'sale_transaction_id' => 'nullable|exists:sale_transactions,id',
            'purchase_id' => 'nullable|exists:purchases,id',
            'quantity' => 'nullable|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'discount_id' => 'nullable|exists:discounts,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $mode = $validated['mode'] ?? ($validated['purchase_id'] ? 'purchase' : 'existing');

        try {
            if ($mode === 'purchase') {
                $request->validate([
                    'purchase_id' => 'required|exists:purchases,id',
                    'quantity' => 'required|integer|min:1',
                    'unit_price' => 'required|numeric|min:0',
                ]);
                $purchase = Purchase::findOrFail($validated['purchase_id']);
                $discount = !empty($validated['discount_id']) ? Discount::find($validated['discount_id']) : null;
                ProjectFinanceService::allocateFromPurchase(
                    $project,
                    $purchase,
                    (int) $validated['quantity'],
                    (float) $validated['unit_price'],
                    $validated['notes'] ?? null,
                    $discount
                );
            } else {
                $request->validate([
                    'sale_transaction_id' => 'required|exists:sale_transactions,id',
                ]);
                $transaction = SaleTransaction::findOrFail($validated['sale_transaction_id']);
                ProjectFinanceService::attachSaleTransaction($project, $transaction);
            }

            return redirect()
                ->route('admin.projects.show', $project)
                ->with('success', 'Alokasi stok berhasil dilampirkan. Nilai, pajak, dan termin diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function detachSaleTransaction(string $projectId, string $transactionId)
    {
        $project = Project::findOrFail($projectId);
        $transaction = SaleTransaction::where('project_id', $projectId)->findOrFail($transactionId);

        try {
            ProjectFinanceService::detachSaleTransaction($project, $transaction);

            return redirect()
                ->route('admin.projects.show', $project)
                ->with('success', 'Alokasi stok dilepas. Nilai, pajak, dan termin diperbarui.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
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

    private function portfolioPayload(Request $request, array $validated, ?Project $project = null): array
    {
        $isPublic = $request->boolean('is_portfolio_public');
        $isFeatured = $request->boolean('is_portfolio_featured');

        $tags = collect(explode(',', (string) ($validated['portfolio_tags'] ?? '')))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->values()
            ->all();

        $year = $validated['portfolio_year']
            ?? ($validated['start_date'] ? (int) date('Y', strtotime($validated['start_date'])) : null)
            ?? ($project?->portfolioYearValue());

        $slug = $project?->slug;
        if ($isPublic) {
            $slug = $slug ?: Project::makeUniqueSlug($validated['title'], $project?->id);
        }

        $coverPath = $project?->portfolio_cover_path;
        if ($request->boolean('remove_portfolio_cover') && $coverPath) {
            if (Storage::disk('public')->exists($coverPath)) {
                Storage::disk('public')->delete($coverPath);
            }
            $coverPath = null;
        }
        if ($request->hasFile('portfolio_cover')) {
            if ($coverPath && Storage::disk('public')->exists($coverPath)) {
                Storage::disk('public')->delete($coverPath);
            }
            $coverPath = $request->file('portfolio_cover')->store('portfolio-covers', 'public');
        }

        return [
            'slug' => $slug,
            'is_portfolio_public' => $isPublic,
            'is_portfolio_featured' => $isFeatured && $isPublic,
            'portfolio_category' => $validated['portfolio_category'] ?? null,
            'portfolio_year' => $year,
            'portfolio_sort_order' => (int) ($validated['portfolio_sort_order'] ?? 0),
            'portfolio_client_context' => $validated['portfolio_client_context'] ?? null,
            'portfolio_excerpt' => $validated['portfolio_excerpt'] ?? null,
            'portfolio_challenge' => $validated['portfolio_challenge'] ?? null,
            'portfolio_solution' => $validated['portfolio_solution'] ?? null,
            'portfolio_outcome' => $validated['portfolio_outcome'] ?? null,
            'portfolio_tags' => $tags ?: null,
            'portfolio_cover_path' => $coverPath,
        ];
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
