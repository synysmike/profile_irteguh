<?php

namespace App\Http\Controllers;

use App\Models\ProjectPaymentTerm;
use App\Support\ProjectTermWhatsAppInvoice;
use App\Support\SaleWhatsAppInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicProjectTermInvoiceController extends Controller
{
    /**
     * Tampilkan form verifikasi HP, atau invoice penagihan jika sudah terverifikasi / admin login.
     * Jika termin sudah diposting, arahkan ke invoice penjualan publik.
     */
    public function show(Request $request, ProjectPaymentTerm $term)
    {
        $term->load(['project.customer', 'project.saleTransactions.purchase', 'project.tax', 'sale']);

        if ($term->status === 'paid' || $term->sale_id) {
            if ($term->sale) {
                return redirect()->to($term->sale->publicInvoiceUrl());
            }

            abort(404, 'Invoice termin tidak ditemukan.');
        }

        $project = $term->project;

        if ($this->canViewInvoice($request, $term)) {
            return $this->renderInvoice($project, $term);
        }

        return view('invoice.verify', [
            'invoiceNumber' => ProjectTermWhatsAppInvoice::billingPayload($project, $term)['nota'],
            'customerName' => $project->customer?->name,
            'hasCustomerPhone' => (bool) SaleWhatsAppInvoice::normalizePhone($project->customer?->phone),
            'verifyRoute' => route('invoice.project-terms.verify', $term),
        ]);
    }

    /**
     * Verifikasi nomor HP customer sebelum membuka invoice penagihan.
     */
    public function verify(Request $request, ProjectPaymentTerm $term)
    {
        $term->load(['project.customer', 'sale']);

        if ($term->status === 'paid' || $term->sale_id) {
            if ($term->sale) {
                return redirect()->to($term->sale->publicInvoiceUrl());
            }

            abort(404);
        }

        $expected = SaleWhatsAppInvoice::normalizePhone($term->project?->customer?->phone);
        if (! $expected) {
            return back()
                ->withInput()
                ->with('error', 'Invoice ini belum bisa dibuka: nomor HP customer belum tercatat. Hubungi admin.');
        }

        $validated = $request->validate([
            'phone' => 'required|string|max:30',
        ], [
            'phone.required' => 'Nomor HP wajib diisi.',
        ]);

        $input = SaleWhatsAppInvoice::normalizePhone($validated['phone']);
        if (! $input || $input !== $expected) {
            return back()
                ->withInput()
                ->with('error', 'Nomor HP tidak cocok dengan data invoice. Akses ditolak.');
        }

        $request->session()->put($this->sessionKey($term), true);

        if ($term->project_id) {
            $request->session()->put('project_review_verified.' . $term->project_id, true);
        }

        return redirect()->route('invoice.project-terms.show', $term);
    }

    private function canViewInvoice(Request $request, ProjectPaymentTerm $term): bool
    {
        if (Auth::check()) {
            return true;
        }

        return (bool) $request->session()->get($this->sessionKey($term), false);
    }

    private function sessionKey(ProjectPaymentTerm $term): string
    {
        return 'project_term_invoice_verified.' . $term->id;
    }

    private function renderInvoice($project, ProjectPaymentTerm $term)
    {
        $payload = ProjectTermWhatsAppInvoice::billingPayload($project, $term);

        return view('admin.projects.term-invoice', [
            'project' => $project,
            'term' => $term,
            'payload' => $payload,
            'isPublic' => ! Auth::check(),
        ]);
    }
}
