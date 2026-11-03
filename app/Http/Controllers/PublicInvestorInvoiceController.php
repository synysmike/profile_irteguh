<?php

namespace App\Http\Controllers;

use App\Models\InvestorCapitalInjection;
use App\Models\Setting;
use App\Services\InvestorCapitalService;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class PublicInvestorInvoiceController extends Controller
{
    public function show(Request $request, InvestorCapitalInjection $injection)
    {
        $injection->load(['investor', 'benefitPackage', 'sumberDana']);

        if ($this->canView($request, $injection)) {
            return view('invoice.investor', [
                'injection' => $injection,
                'isPublic' => ! Auth::check(),
                'bankName' => Setting::investorBankName(),
                'bankAccount' => Setting::investorBankAccount(),
                'bankHolder' => Setting::investorBankHolder(),
            ]);
        }

        return view('invoice.verify', [
            'invoiceNumber' => $injection->invoice_number,
            'customerName' => $injection->investor?->name,
            'hasCustomerPhone' => (bool) PhoneNumber::normalize($injection->investor?->phone),
            'verifyRoute' => URL::temporarySignedRoute(
                'invoice.investor.verify',
                now()->addDays(30),
                ['injection' => $injection->id]
            ),
        ]);
    }

    public function verify(Request $request, InvestorCapitalInjection $injection)
    {
        $key = 'investor-invoice-verify:' . $injection->id . ':' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 8)) {
            return back()->with('error', 'Terlalu banyak percobaan. Coba lagi nanti.');
        }
        RateLimiter::hit($key, 300);

        $expected = PhoneNumber::normalize($injection->investor?->phone);
        if (! $expected) {
            return back()->with('error', 'Nomor HP investor belum tercatat. Hubungi admin.');
        }

        $validated = $request->validate([
            'phone' => 'required|string|max:30',
        ]);

        $input = PhoneNumber::normalize($validated['phone']);
        if (! $input || $input !== $expected) {
            return back()->withInput()->with('error', 'Nomor HP tidak cocok. Akses ditolak.');
        }

        $request->session()->put($this->sessionKey($injection), true);

        return redirect()->to(URL::temporarySignedRoute(
            'invoice.investor.show',
            now()->addDays(30),
            ['injection' => $injection->id]
        ));
    }

    public function approve(Request $request, InvestorCapitalInjection $injection, InvestorCapitalService $service)
    {
        if (! $this->canView($request, $injection)) {
            abort(403);
        }

        $service->approve($injection);

        return redirect()->to(URL::temporarySignedRoute(
            'invoice.investor.show',
            now()->addDays(30),
            ['injection' => $injection->id]
        ))->with('success', 'Terima kasih. Syarat & ketentuan disetujui. Silakan lanjutkan pembayaran dan unggah bukti transfer.');
    }

    public function pay(Request $request, InvestorCapitalInjection $injection, InvestorCapitalService $service)
    {
        if (! $this->canView($request, $injection)) {
            abort(403);
        }

        $validated = $request->validate([
            'payment_date' => 'required|date',
            'payment_reference' => 'nullable|string|max:120',
            'payment_sender_name' => 'nullable|string|max:120',
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        ], [
            'payment_proof.required' => 'Bukti transfer wajib diunggah.',
            'payment_proof.mimes' => 'Bukti transfer harus berupa gambar atau PDF.',
            'payment_proof.max' => 'Ukuran bukti transfer maksimal 5 MB.',
        ]);

        try {
            $service->confirmPayment($injection, $request->file('payment_proof'), $validated);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->to(URL::temporarySignedRoute(
            'invoice.investor.show',
            now()->addDays(30),
            ['injection' => $injection->id]
        ))->with('success', 'Bukti transfer diterima. Dana masuk dari akun investor telah dicatat.');
    }

    public function reject(Request $request, InvestorCapitalInjection $injection, InvestorCapitalService $service)
    {
        if (! $this->canView($request, $injection)) {
            abort(403);
        }

        $validated = $request->validate([
            'rejection_note' => 'nullable|string|max:1000',
        ]);

        $service->reject($injection, $validated['rejection_note'] ?? null);

        return redirect()->to(URL::temporarySignedRoute(
            'invoice.investor.show',
            now()->addDays(30),
            ['injection' => $injection->id]
        ))->with('success', 'Penolakan tercatat.');
    }

    private function canView(Request $request, InvestorCapitalInjection $injection): bool
    {
        if (Auth::check()) {
            return true;
        }

        return (bool) $request->session()->get($this->sessionKey($injection), false);
    }

    private function sessionKey(InvestorCapitalInjection $injection): string
    {
        return 'investor_invoice_verified.' . $injection->id;
    }
}
