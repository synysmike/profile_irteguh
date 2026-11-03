<?php

namespace App\Http\Controllers;

use App\Models\Investor;
use App\Models\InvestorDocument;
use App\Support\PhoneNumber;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

class InvestorPortalController extends Controller
{
    public function loginForm(Request $request)
    {
        if ($this->currentInvestor($request)) {
            return redirect()->route('investor.portal.dashboard');
        }

        return view('investor-portal.login');
    }

    public function login(Request $request)
    {
        $key = 'investor-portal-login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return back()->with('error', 'Terlalu banyak percobaan. Coba lagi nanti.');
        }
        RateLimiter::hit($key, 300);

        $validated = $request->validate([
            'phone' => 'required|string|max:30',
        ]);

        $phone = PhoneNumber::normalize($validated['phone']);
        $investor = $phone
            ? Investor::active()->where('phone', $phone)->first()
            : null;

        if (! $investor) {
            return back()->withInput()->with('error', 'Nomor HP tidak ditemukan pada data investor aktif.');
        }

        $request->session()->put('investor_portal_id', $investor->id);

        return redirect()->route('investor.portal.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('investor_portal_id');

        return redirect()->route('investor.portal.login')->with('success', 'Anda telah keluar dari portal.');
    }

    public function dashboard(Request $request)
    {
        $investor = $this->requireInvestor($request);

        $injections = $investor->capitalInjections()->latestFirst()->get();
        $dividends = $investor->dividendAllocations()->with('period')->latest('id')->get();
        $documents = $investor->documents()->latest('id')->get();

        $totalModal = $injections->where('status', 'approved')->sum('amount');
        $dividenPaid = $dividends->where('status', 'paid')->sum('amount');
        $dividenPending = $dividends->where('status', 'pending')->sum('amount');

        return view('investor-portal.dashboard', compact(
            'investor', 'injections', 'dividends', 'documents',
            'totalModal', 'dividenPaid', 'dividenPending'
        ));
    }

    public function downloadDocument(Request $request, InvestorDocument $document)
    {
        $investor = $this->requireInvestor($request);
        if ((int) $document->investor_id !== (int) $investor->id) {
            abort(403);
        }

        if (! $document->file_path || ! Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($document->file_path, $document->title);
    }

    private function currentInvestor(Request $request): ?Investor
    {
        $id = $request->session()->get('investor_portal_id');
        if (! $id) {
            return null;
        }

        return Investor::active()->find($id);
    }

    private function requireInvestor(Request $request): Investor
    {
        $investor = $this->currentInvestor($request);
        if (! $investor) {
            throw new HttpResponseException(redirect()->route('investor.portal.login'));
        }

        return $investor;
    }
}
