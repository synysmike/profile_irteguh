<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleReview;
use App\Models\SaleReviewMedia;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class PublicSaleReviewController extends Controller
{
    public function show(Request $request, Sale $sale)
    {
        if ($sale->project_id && $sale->project) {
            return redirect()->route('project.review.show', $sale->project);
        }

        $sale->load(['customer', 'publishedReviews.media']);

        if ($this->canAccess($request, $sale)) {
            $existing = SaleReview::query()
                ->with('media')
                ->where('sale_id', $sale->id)
                ->where('customer_id', $sale->customer_id)
                ->first();

            return view('public.sale-review', [
                'sale' => $sale,
                'existing' => $existing,
                'customer' => $sale->customer,
            ]);
        }

        return view('invoice.verify', [
            'invoiceNumber' => $sale->invoice_number,
            'customerName' => $sale->customer?->name,
            'hasCustomerPhone' => (bool) PhoneNumber::normalize($sale->customer?->phone),
            'verifyRoute' => route('sale.review.verify', $sale),
            'verifyTitle' => 'Verifikasi akses ulasan',
            'verifySubtitle' => 'Masukkan nomor HP pelanggan yang terdaftar pada faktur ini.',
            'verifyButton' => 'Lanjut ke Form Ulasan',
            'metaLabel' => 'No. Faktur',
        ]);
    }

    public function verify(Request $request, Sale $sale)
    {
        if ($sale->project_id && $sale->project) {
            return redirect()->route('project.review.show', $sale->project);
        }

        $key = 'sale-review-verify:' . $sale->id . ':' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 8)) {
            return back()->with('error', 'Terlalu banyak percobaan. Coba lagi nanti.');
        }
        RateLimiter::hit($key, 300);

        $sale->load('customer');
        $expected = PhoneNumber::normalize($sale->customer?->phone);
        if (! $expected) {
            return back()->with('error', 'Nomor HP customer belum tercatat. Hubungi admin.');
        }

        $validated = $request->validate([
            'phone' => 'required|string|max:30',
        ], [
            'phone.required' => 'Nomor HP wajib diisi.',
        ]);

        $input = PhoneNumber::normalize($validated['phone']);
        if (! $input || $input !== $expected) {
            return back()->withInput()->with('error', 'Nomor HP tidak cocok. Akses ditolak.');
        }

        $request->session()->put($this->sessionKey($sale), true);

        return redirect()->route('sale.review.show', $sale);
    }

    public function store(Request $request, Sale $sale)
    {
        if ($sale->project_id) {
            abort(404);
        }

        if (! $this->canAccess($request, $sale)) {
            abort(403);
        }

        $sale->load('customer');
        if (! $sale->customer_id) {
            return back()->with('error', 'Faktur belum memiliki customer.');
        }

        $existing = SaleReview::query()
            ->where('sale_id', $sale->id)
            ->where('customer_id', $sale->customer_id)
            ->first();

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
            'reviewer_name' => 'nullable|string|max:120',
            'media' => 'nullable|array|max:8',
            'media.*' => 'file|mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime|max:51200',
            'remove_media' => 'nullable|array',
            'remove_media.*' => 'integer',
        ], [
            'rating.required' => 'Rating wajib dipilih.',
            'media.max' => 'Maksimal 8 lampiran per unggahan.',
            'media.*.max' => 'Setiap file maksimal 50 MB.',
            'media.*.mimetypes' => 'File harus gambar (JPG/PNG/WEBP) atau video (MP4/WEBM/MOV).',
        ]);

        DB::transaction(function () use ($request, $sale, $validated, $existing) {
            $review = SaleReview::updateOrCreate(
                [
                    'sale_id' => $sale->id,
                    'customer_id' => $sale->customer_id,
                ],
                [
                    'rating' => (int) $validated['rating'],
                    'comment' => trim((string) ($validated['comment'] ?? '')) ?: null,
                    'reviewer_name' => trim((string) ($validated['reviewer_name'] ?? ''))
                        ?: ($sale->customer?->name),
                    'is_published' => true,
                    'submitted_at' => now(),
                    'ip_address' => $request->ip(),
                ]
            );

            $removeIds = collect($validated['remove_media'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->all();

            if ($removeIds) {
                $review->media()->whereIn('id', $removeIds)->get()->each->delete();
            }

            $sort = (int) $review->media()->max('sort_order');
            foreach ($request->file('media', []) as $file) {
                if (! $file) {
                    continue;
                }
                if ($review->media()->count() >= 8) {
                    break;
                }

                $mime = (string) $file->getMimeType();
                $type = str_starts_with($mime, 'video/') ? 'video' : 'image';
                $path = $file->store('sale-reviews/' . $sale->id, 'public');
                $sort++;

                SaleReviewMedia::create([
                    'sale_review_id' => $review->id,
                    'file_path' => $path,
                    'media_type' => $type,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $mime,
                    'file_size' => $file->getSize(),
                    'sort_order' => $sort,
                ]);
            }
        });

        $wasNew = $existing === null;

        return redirect()
            ->route('sale.review.show', $sale)
            ->with('success', $wasNew
                ? 'Terima kasih! Ulasan dan lampiran Anda telah dikirim.'
                : 'Ulasan Anda berhasil diperbarui.');
    }

    public function destroyMedia(Request $request, Sale $sale, SaleReviewMedia $media)
    {
        if ($sale->project_id) {
            abort(404);
        }

        if (! $this->canAccess($request, $sale)) {
            abort(403);
        }

        $review = $media->review;
        if (! $review || (int) $review->sale_id !== (int) $sale->id || (int) $review->customer_id !== (int) $sale->customer_id) {
            abort(404);
        }

        $media->delete();

        return back()->with('success', 'Lampiran dihapus.');
    }

    private function canAccess(Request $request, Sale $sale): bool
    {
        if (Auth::check()) {
            return true;
        }

        return (bool) $request->session()->get($this->sessionKey($sale), false);
    }

    private function sessionKey(Sale $sale): string
    {
        return 'sale_review_verified.' . $sale->id;
    }
}
