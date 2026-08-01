<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Support\SaleWhatsAppInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicSaleInvoiceController extends Controller
{
    /**
     * Tampilkan form verifikasi HP, atau invoice jika sudah terverifikasi / admin login.
     */
    public function show(Request $request, Sale $sale)
    {
        $sale->load(['customer', 'saleItems', 'project']);

        if ($this->canViewInvoice($request, $sale)) {
            return $this->renderInvoice($sale);
        }

        return view('invoice.verify', [
            'sale' => $sale,
            'invoiceNumber' => $sale->invoice_number,
            'customerName' => $sale->customer?->name,
            'hasCustomerPhone' => (bool) SaleWhatsAppInvoice::normalizePhone($sale->customer?->phone),
            'verifyRoute' => route('invoice.sales.verify', $sale),
        ]);
    }

    /**
     * Verifikasi nomor HP customer sebelum membuka invoice.
     */
    public function verify(Request $request, Sale $sale)
    {
        $sale->load('customer');

        $expected = SaleWhatsAppInvoice::normalizePhone($sale->customer?->phone);
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

        $request->session()->put($this->sessionKey($sale), true);

        return redirect()->route('invoice.sales.show', $sale);
    }

    private function canViewInvoice(Request $request, Sale $sale): bool
    {
        if (Auth::check()) {
            return true;
        }

        return (bool) $request->session()->get($this->sessionKey($sale), false);
    }

    private function sessionKey(Sale $sale): string
    {
        return 'invoice_verified.' . $sale->id;
    }

    private function renderInvoice(Sale $sale)
    {
        // Jangan mutate data di GET publik — tampilkan apa yang tersimpan.
        return view('admin.sales.invoice', [
            'sale' => $sale->loadMissing(['customer', 'saleItems', 'project']),
            'isPublic' => ! Auth::check(),
        ]);
    }
}
