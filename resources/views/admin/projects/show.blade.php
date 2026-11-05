@extends('admin.keuangan.layout')

@section('title', 'Detail Project - ' . $project->code)

@section('keuangan_content')
@php
    $availableAllocations = $availableAllocations ?? collect();
    $availablePurchases = $availablePurchases ?? collect();
@endphp
<div class="mb-6">
    <a href="{{ route('admin.projects.index') }}" class="text-purple-600 hover:text-purple-800 mb-4 inline-block">← Kembali ke Project</a>
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $project->title }}</h2>
            <p class="text-gray-600 mt-1">{{ $project->code }} · {{ $project->customer?->name }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button"
                    onclick="openResourceModal('customerModal', 'customers', 'Customer', null, '?quick=1')"
                    class="px-4 py-2 bg-white border border-purple-300 text-purple-700 rounded-md hover:bg-purple-50 text-sm font-semibold">
                + Customer
            </button>
            <button type="button" onclick="openResourceModal('projectModal', 'projects', 'Project', {{ $project->id }})" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 text-sm font-semibold">Edit Project</button>
        </div>
    </div>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6"><p class="text-green-700">{{ session('success') }}</p></div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6"><p class="text-red-700">{{ session('error') }}</p></div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="text-sm text-gray-500 mb-1">Status</div>
        <div class="text-lg font-semibold text-gray-900">{{ \App\Models\Project::statusLabels()[$project->status] ?? $project->status }}</div>
        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
            <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $project->progress_percent }}%"></div>
        </div>
        <div class="text-xs text-gray-500 mt-1">{{ $project->progress_percent }}%</div>
        <form method="POST" action="{{ route('admin.projects.update-status', $project) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
            @csrf
            @method('PATCH')
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                @foreach(\App\Models\Project::statusLabels() as $value => $label)
                <option value="{{ $value }}" {{ $project->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <input type="number" name="progress_percent" min="0" max="100" value="{{ $project->progress_percent }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Progress %">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Bukti progres (foto/video)</label>
                <input type="file" name="progress_media[]" multiple accept="image/*,video/mp4,video/webm,video/quicktime"
                       class="w-full text-xs text-gray-600">
                <p class="text-[11px] text-gray-400 mt-1">JPG/PNG/WEBP atau MP4/WEBM/MOV · max 50MB/file · max 10 file</p>
            </div>
            <input type="text" name="media_caption" maxlength="255" placeholder="Caption (opsional)"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
            <label class="flex items-center gap-2 text-xs text-gray-700">
                <input type="hidden" name="show_in_portfolio" value="0">
                <input type="checkbox" name="show_in_portfolio" value="1" checked class="rounded border-gray-300">
                Tampilkan di portofolio publik
            </label>
            <button type="submit" class="w-full px-3 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-900">Update Progress</button>
        </form>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="text-sm text-gray-500 mb-1">Nilai, Diskon & Pajak</div>
        <div class="space-y-1 text-sm">
            <div class="flex justify-between"><span>Jasa kotor</span><span>Rp {{ number_format($project->base_subtotal ?? 0, 0, ',', '.') }}</span></div>
            @if($project->assignmentLetters->flatMap->jasaItems->isNotEmpty())
            <p class="text-xs text-indigo-600">Dari item jasa surat tugas (qty bisa diubah di bawah).</p>
            @endif
            <div class="flex justify-between text-red-700">
                <span>Diskon jasa{{ $project->discount_name ? ' ('.$project->discount_name.')' : '' }}</span>
                <span>- Rp {{ number_format($project->discount_amount ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between"><span>Jasa net</span><span>Rp {{ number_format($project->jasaNetSubtotal(), 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span>DPP stok</span><span>Rp {{ number_format($project->stockSubtotal(), 0, ',', '.') }}</span></div>
            <div class="flex justify-between font-medium"><span>DPP total</span><span>Rp {{ number_format($project->subtotal, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span>{{ $project->tax_name ?: 'Pajak' }}</span><span>Rp {{ number_format($project->ppn_amount, 0, ',', '.') }}</span></div>
            <div class="flex justify-between font-semibold border-t pt-2"><span>Total</span><span>Rp {{ number_format($project->total, 0, ',', '.') }}</span></div>
        </div>
        <form method="POST" action="{{ route('admin.projects.update-discount', $project) }}" class="mt-4 space-y-2 border-t pt-3">
            @csrf
            @method('PATCH')
            <label class="block text-xs font-medium text-gray-600">Diskon jasa</label>
            <select name="discount_id" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white">
                <option value="">Tanpa Diskon</option>
                @foreach(($discounts ?? collect()) as $discount)
                <option value="{{ $discount->id }}" {{ (string) $project->discount_id === (string) $discount->id ? 'selected' : '' }}>
                    {{ $discount->label() }}
                </option>
                @endforeach
            </select>
            <button type="submit" class="w-full px-3 py-2 bg-purple-600 text-white rounded-md text-sm hover:bg-purple-700">Terapkan Diskon Jasa</button>
        </form>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="text-sm text-gray-500 mb-1">Pembayaran</div>
        <div class="text-lg font-semibold">{{ $project->payment_method === 'full' ? 'Langsung Lunas' : 'Termin' }}</div>
        <div class="mt-2 text-sm text-gray-600">Terkumpul: Rp {{ number_format($project->paidAmount(), 0, ',', '.') }} ({{ $project->paymentProgressPercent() }}%)</div>
        <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
            <div class="bg-green-600 h-2 rounded-full" style="width: {{ $project->paymentProgressPercent() }}%"></div>
        </div>
    </div>
</div>

@php
    $projectJasaItems = $project->assignmentLetters->flatMap(fn ($letter) => $letter->jasaItems->map(function ($item) use ($letter) {
        $item->setAttribute('letter_number', $letter->number);
        return $item;
    }));
    $jasaQtyLocked = $project->paymentTerms->where('status', 'paid')->isNotEmpty();
@endphp

{{-- Ulasan customer --}}
<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden mb-6">
    <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Ulasan Customer</h3>
            <p class="text-sm text-gray-500 mt-0.5">
                Customer mengisi lewat link yang sama dengan invoice (WA).
                @if($project->averageRating())
                · Rata-rata <strong>{{ $project->averageRating() }}/5</strong>
                @endif
            </p>
        </div>
        <a href="{{ $project->publicReviewUrl() }}" target="_blank" class="text-sm text-emerald-700 hover:text-emerald-900 font-medium break-all">
            Buka link ulasan →
        </a>
    </div>
    @if($project->reviews->isEmpty())
    <div class="px-6 py-8 text-center text-gray-500 text-sm">Belum ada ulasan dari customer.</div>
    @else
    <div class="divide-y">
        @foreach($project->reviews as $review)
        <div class="px-4 sm:px-6 py-4 flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="font-medium text-gray-900">{{ $review->displayName() }}</div>
                <div class="text-amber-500 text-sm tracking-wide">{{ $review->stars() }} ({{ $review->rating }}/5)</div>
                @if($review->comment)
                <p class="text-sm text-gray-700 mt-1 whitespace-pre-wrap">{{ $review->comment }}</p>
                @endif
                @if($review->media->count())
                <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-2 max-w-lg">
                    @foreach($review->media as $media)
                    <div class="rounded overflow-hidden border bg-gray-100 aspect-video">
                        @if($media->isVideo())
                        <video src="{{ $media->url() }}" controls class="w-full h-full object-cover"></video>
                        @else
                        <a href="{{ $media->url() }}" target="_blank"><img src="{{ $media->url() }}" alt="" class="w-full h-full object-cover"></a>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
                <div class="text-xs text-gray-500 mt-1">{{ $review->submitted_at?->format('d/m/Y H:i') }}</div>
            </div>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('admin.projects.reviews.publish', [$project, $review]) }}">
                    @csrf @method('PATCH')
                    <button class="text-xs px-2 py-1 rounded {{ $review->is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-600' }}">
                        {{ $review->is_published ? 'Publik' : 'Disembunyikan' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.projects.reviews.destroy', [$project, $review]) }}" onsubmit="return confirm('Hapus ulasan ini?')">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-600">Hapus</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- Bukti progres foto/video --}}
<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden mb-6">
    <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-2">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Bukti Progres</h3>
            <p class="text-sm text-gray-500 mt-0.5">Foto/video update status. Yang ditandai portofolio ikut tampil di halaman publik.</p>
        </div>
        <span class="text-xs text-gray-500">{{ $project->progressMedia->count() }} file</span>
    </div>
    @if($project->progressMedia->isEmpty())
    <div class="px-6 py-8 text-center text-gray-500 text-sm">Belum ada bukti progres. Unggah lewat form Status di atas.</div>
    @else
    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($project->progressMedia as $media)
        <div class="border border-gray-200 rounded-lg overflow-hidden bg-gray-50">
            <div class="aspect-video bg-black/5 flex items-center justify-center overflow-hidden">
                @if($media->isVideo())
                <video src="{{ $media->url() }}" controls class="w-full h-full object-contain bg-black"></video>
                @else
                <img src="{{ $media->url() }}" alt="{{ $media->caption ?: $media->original_name }}" class="w-full h-full object-cover">
                @endif
            </div>
            <div class="p-3 space-y-2">
                <div class="text-xs text-gray-500">
                    {{ strtoupper($media->media_type) }}
                    @if($media->status_snapshot) · {{ \App\Models\Project::statusLabels()[$media->status_snapshot] ?? $media->status_snapshot }}@endif
                    @if(!is_null($media->progress_snapshot)) · {{ $media->progress_snapshot }}%@endif
                    · {{ $media->created_at?->format('d/m/Y H:i') }}
                </div>
                @if($media->caption)
                <div class="text-sm text-gray-800">{{ $media->caption }}</div>
                @endif
                <div class="flex flex-wrap gap-2 items-center justify-between">
                    <form method="POST" action="{{ route('admin.projects.progress-media.portfolio', [$project, $media]) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="text-xs px-2 py-1 rounded {{ $media->show_in_portfolio ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-600' }}">
                            {{ $media->show_in_portfolio ? 'Di Portofolio' : 'Tidak di Portofolio' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.projects.progress-media.destroy', [$project, $media]) }}" onsubmit="return confirm('Hapus media ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 hover:text-red-800">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- Item Jasa (dari surat tugas) — qty bisa disesuaikan --}}
<div id="item-jasa" class="bg-white rounded-lg shadow border-2 border-indigo-300 overflow-hidden mb-6">
    <div class="px-4 sm:px-6 py-4 border-b border-indigo-100 bg-indigo-50 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Item Jasa</h3>
            <p class="text-sm text-gray-600 mt-1">
                Berasal dari surat tugas. Sesuaikan qty lalu simpan — nilai jasa kotor, DPP, termin unpaid, dan total invoice ikut berubah.
            </p>
        </div>
        <a href="{{ route('admin.projects.assignment-letters.create', $project) }}" class="text-sm text-indigo-700 hover:text-indigo-900 font-medium">+ Surat Tugas / Tambah Jasa</a>
    </div>

    @if($projectJasaItems->isEmpty())
    <div class="px-6 py-8 text-center text-gray-500 text-sm">
        Belum ada item jasa. Tambahkan lewat <strong>Surat Tugas</strong>, atau isi nilai jasa manual di Edit Project.
    </div>
    @else
    <form method="POST" action="{{ route('admin.projects.jasa-items.update', $project) }}" id="form-project-jasa-qty">
        @csrf
        @method('PATCH')
        @if($jasaQtyLocked)
        <div class="mx-4 sm:mx-6 mt-4 bg-amber-50 border border-amber-200 rounded-md px-3 py-2 text-sm text-amber-800">
            Qty terkunci karena sudah ada termin yang lunas.
        </div>
        @endif
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Surat</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jasa</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Harga</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase w-28">Qty</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="project-jasa-rows">
                    @foreach($projectJasaItems as $index => $item)
                    <tr class="project-jasa-row" data-price="{{ (float) $item->unit_price }}">
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $item->letter_number }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->description }}</td>
                        <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right">
                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                            <input type="number" name="items[{{ $index }}][quantity]" min="1" step="1" required
                                   value="{{ $item->quantity }}"
                                   {{ $jasaQtyLocked ? 'readonly' : '' }}
                                   class="jasa-qty-input w-24 ml-auto block px-2 py-1.5 border border-gray-300 rounded-md text-sm text-right {{ $jasaQtyLocked ? 'bg-gray-50' : '' }}">
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-medium jasa-line-subtotal">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-sm text-right font-semibold text-gray-700">Total jasa (nilai jasa kotor)</td>
                        <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900" id="project-jasa-total">
                            Rp {{ number_format($projectJasaItems->sum('subtotal'), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @unless($jasaQtyLocked)
        <div class="px-4 sm:px-6 py-4 border-t border-gray-200 flex justify-end">
            <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-semibold">
                Simpan Qty &amp; Hitung Ulang Invoice
            </button>
        </div>
        @endunless
    </form>
    <script>
    (function () {
        const rows = document.querySelectorAll('#project-jasa-rows .project-jasa-row');
        const totalEl = document.getElementById('project-jasa-total');
        if (!rows.length) return;
        function formatRp(n) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n || 0));
        }
        function recalc() {
            let total = 0;
            rows.forEach(function (row) {
                const price = parseFloat(row.dataset.price) || 0;
                const qty = parseFloat(row.querySelector('.jasa-qty-input')?.value) || 0;
                const sub = price * qty;
                total += sub;
                const cell = row.querySelector('.jasa-line-subtotal');
                if (cell) cell.textContent = formatRp(sub);
            });
            if (totalEl) totalEl.textContent = formatRp(total);
        }
        rows.forEach(function (row) {
            row.querySelector('.jasa-qty-input')?.addEventListener('input', recalc);
        });
    })();
    </script>
    @endif
</div>

{{-- Alokasi Stok --}}
<div id="alokasi-stok" class="bg-white rounded-lg shadow border-2 border-purple-300 overflow-hidden mb-6">
    <div class="px-4 sm:px-6 py-4 border-b border-purple-100 bg-purple-50 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Alokasi Stok untuk Project</h3>
            <p class="text-sm text-gray-600 mt-1">Ambil unit dari stok grosir. Subtotal otomatis masuk ke DPP, pajak, dan termin pembayaran.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" onclick="openResourceModal('purchaseModal', 'purchases', 'Grosir')" class="px-3 py-1.5 bg-purple-600 text-white rounded-md text-sm font-semibold hover:bg-purple-700">
                + Tambah Grosir
            </button>
            <a href="{{ route('admin.keuangan.sale-transactions.index') }}" class="text-sm text-purple-700 hover:text-purple-900 font-medium">Kelola Alokasi Stok →</a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Grosir</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Harga</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Diskon</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($project->saleTransactions as $allocation)
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $allocation->purchase?->invoice_number ?? '—' }}
                        <span class="block text-xs text-gray-400">{{ $allocation->purchase?->supplier?->name }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $allocation->description }}</td>
                    <td class="px-4 py-3 text-sm text-right">{{ number_format($allocation->quantity, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($allocation->unit_price, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-right text-red-700">
                        @if((float) ($allocation->discount_amount ?? 0) > 0)
                        - Rp {{ number_format($allocation->discount_amount, 0, ',', '.') }}
                        <span class="block text-xs text-gray-400">{{ $allocation->discount_name }}</span>
                        @else
                        —
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-right font-medium">Rp {{ number_format($allocation->subtotal, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-sm">
                        <form method="POST" action="{{ route('admin.projects.sale-transactions.detach', [$project, $allocation]) }}" onsubmit="return confirm('Lepas alokasi stok ini dari project? Nilai & termin akan dihitung ulang.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Lepas</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-gray-500 text-sm">Belum ada stok terpasang. Gunakan form di bawah untuk melampirkan.</td>
                </tr>
                @endforelse
            </tbody>
            @if($project->saleTransactions->isNotEmpty())
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="5" class="px-4 py-3 text-sm font-semibold text-right text-gray-700">Total alokasi stok</td>
                    <td class="px-4 py-3 text-sm font-semibold text-right">Rp {{ number_format($project->stockSubtotal(), 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    <div class="px-4 sm:px-6 py-4 border-t border-gray-200 space-y-5 bg-white">
        <form method="POST" action="{{ route('admin.projects.sale-transactions.attach', $project) }}" class="space-y-3">
            @csrf
            <input type="hidden" name="mode" value="purchase">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="text-sm font-semibold text-gray-800">1. Tambah dari stok grosir</div>
                <button type="button" onclick="openResourceModal('purchaseModal', 'purchases', 'Grosir')" class="text-xs text-purple-700 hover:text-purple-900 font-medium">
                    + Input barang grosir baru
                </button>
            </div>
            @if($availablePurchases->isEmpty())
            <p class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-md px-3 py-2">
                Stok grosir tersisa kosong.
                <button type="button" onclick="openResourceModal('purchaseModal', 'purchases', 'Grosir')" class="underline font-medium text-purple-700 hover:text-purple-900">Tambah Grosir</button>
                di sini, atau buka
                <a href="{{ route('admin.keuangan.transaksi.pembelian') }}" class="underline font-medium">halaman Grosir</a>.
            </p>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Barang grosir *</label>
                    <select name="purchase_id" id="project-purchase-select" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white">
                        <option value="">-- Pilih stok --</option>
                        @foreach($availablePurchases as $purchase)
                        <option value="{{ $purchase->id }}"
                                data-remaining="{{ $purchase->remainingQuantity() }}"
                                data-cost="{{ (float) $purchase->unit_price }}">
                            {{ $purchase->invoice_number }} — {{ $purchase->displayDescription() }}
                            (sisa {{ $purchase->remainingQuantity() }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Qty *</label>
                    <input type="number" name="quantity" id="project-purchase-qty" min="1" value="1" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Harga jual *</label>
                    <input type="number" name="unit_price" id="project-purchase-price" min="0" step="1" value="0" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Diskon stok</label>
                    <select name="discount_id" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white">
                        <option value="">Tanpa Diskon</option>
                        @foreach(($discounts ?? collect()) as $discount)
                        <option value="{{ $discount->id }}">{{ $discount->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md text-sm font-semibold hover:bg-purple-700">Lampirkan dari Grosir</button>
            @endif
        </form>

        <form method="POST" action="{{ route('admin.projects.sale-transactions.attach', $project) }}" class="space-y-3 pt-4 border-t border-gray-100">
            @csrf
            <input type="hidden" name="mode" value="existing">
            <div class="text-sm font-semibold text-gray-800">2. Atau pilih alokasi siap pakai</div>
            <div class="flex flex-col sm:flex-row gap-3 sm:items-end">
                <div class="flex-1 min-w-0">
                    <label for="sale_transaction_id" class="block text-xs font-medium text-gray-600 mb-1">Alokasi stok</label>
                    <select name="sale_transaction_id" id="sale_transaction_id" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white" @if($availableAllocations->isEmpty()) disabled @else required @endif>
                        <option value="">-- Pilih alokasi tersedia --</option>
                        @forelse($availableAllocations as $opt)
                        <option value="{{ $opt->id }}">
                            {{ $opt->description }}
                            · Qty {{ $opt->quantity }}
                            · Rp {{ number_format($opt->subtotal, 0, ',', '.') }}
                            @if($opt->purchase) [{{ $opt->purchase->invoice_number }}] @endif
                        </option>
                        @empty
                        <option value="" disabled>Tidak ada alokasi bebas (sudah terpakai invoice/project)</option>
                        @endforelse
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm font-semibold hover:bg-gray-900 shrink-0 disabled:opacity-50" @if($availableAllocations->isEmpty()) disabled @endif>
                    Lampirkan Alokasi
                </button>
            </div>
        </form>
    </div>
</div>
<script>
(function() {
    var sel = document.getElementById('project-purchase-select');
    var qty = document.getElementById('project-purchase-qty');
    var price = document.getElementById('project-purchase-price');
    if (!sel) return;
    sel.addEventListener('change', function() {
        var opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) return;
        var remaining = parseInt(opt.getAttribute('data-remaining') || '1', 10);
        var cost = parseFloat(opt.getAttribute('data-cost') || '0') || 0;
        if (qty) {
            qty.max = remaining;
            if ((parseInt(qty.value, 10) || 0) > remaining) qty.value = remaining;
        }
        if (price && (!price.value || parseFloat(price.value) === 0)) {
            price.value = Math.round(cost);
        }
    });
})();
</script>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Termin Pembayaran</h3>
            <p class="text-sm text-gray-500 mt-1">Klik "Posting ke Keuangan" untuk membuat invoice penjualan, pajak, dan penerimaan kas.</p>
        </div>
        <a href="{{ route('admin.projects.terms.edit', $project) }}" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 text-sm font-semibold shrink-0">
            Edit Termin
        </a>
    </div>
    <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Label</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">%</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Nominal</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jatuh Tempo</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($project->paymentTerms as $term)
            <tr>
                <td class="px-4 py-3 text-sm">{{ $term->term_number }}</td>
                <td class="px-4 py-3 text-sm font-medium">{{ $term->label }}</td>
                <td class="px-4 py-3 text-sm text-right">{{ number_format($term->percentage, 2, ',', '.') }}%</td>
                <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($term->amount, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-sm">{{ $term->due_date?->format('d/m/Y') ?? '—' }}</td>
                <td class="px-4 py-3 text-sm">
                    @if($term->status === 'paid')
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Lunas</span>
                    @else
                    <span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800">Pending</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                    @if($term->sale_id && $term->sale)
                    <a href="{{ $term->sale->publicInvoiceUrl() }}" target="_blank" class="text-blue-600 hover:text-blue-800 mr-3">Invoice</a>
                    <a href="{{ route('admin.sales.whatsapp', $term->sale_id) }}" target="_blank" rel="noopener" class="text-green-600 hover:text-green-800 mr-3">WA</a>
                    @endif
                    @if($term->status !== 'paid')
                    <a href="{{ $term->publicInvoiceUrl() }}" target="_blank" class="text-blue-600 hover:text-blue-800 mr-3" title="Invoice penagihan (cetak / PDF)">Invoice</a>
                    @if($project->customer?->phone)
                    <a href="{{ route('admin.projects.whatsapp-term', [$project, $term]) }}" target="_blank" rel="noopener" class="text-green-600 hover:text-green-800 mr-3" title="Kirim draft invoice (Belum Lunas) ke {{ $project->customer->phone }}">Kirim WA</a>
                    @else
                    <span class="text-gray-400 mr-3 cursor-not-allowed" title="Customer belum punya nomor HP">Kirim WA</span>
                    @endif
                    <form method="POST" action="{{ route('admin.projects.pay-term', [$project, $term]) }}" class="inline" onsubmit="return confirm('Posting pembayaran termin ini ke penjualan & kas?')">
                        @csrf
                        <button type="submit" class="text-purple-600 hover:text-purple-800 font-medium">Posting ke Keuangan</button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('admin.projects.unpay-term', [$project, $term]) }}" class="inline" onsubmit="return confirm('Batalkan pelunasan termin ini?\nInvoice penjualan dan transaksi kas terkait akan dihapus, status kembali ke Pending.')">
                        @csrf
                        <button type="submit" class="text-amber-700 hover:text-amber-900 font-medium">Batalkan Lunas</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada termin.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Surat Tugas</h3>
            <p class="text-sm text-gray-500 mt-1">Buat surat tugas untuk petugas yang ditunjuk pada project ini.</p>
        </div>
        <a href="{{ route('admin.projects.assignment-letters.create', $project) }}" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 text-sm font-semibold">
            + Buat Surat Tugas
        </a>
    </div>
    <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nomor</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Yang Bertugas</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($project->assignmentLetters as $letter)
            <tr>
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $letter->number }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $letter->letter_date?->format('d/m/Y') }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ $letter->assigneeNamesSummary() }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $letter->assignees->count() }} orang</td>
                <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                    <a href="{{ route('admin.projects.assignment-letters.show', [$project, $letter]) }}" class="text-blue-600 hover:text-blue-800 mr-3">Lihat</a>
                    <a href="{{ route('admin.projects.assignment-letters.edit', [$project, $letter]) }}" class="text-purple-600 hover:text-purple-800 mr-3">Edit</a>
                    <form method="POST" action="{{ route('admin.projects.assignment-letters.destroy', [$project, $letter]) }}" class="inline" onsubmit="return confirm('Hapus surat tugas ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                    Belum ada surat tugas.
                    <a href="{{ route('admin.projects.assignment-letters.create', $project) }}" class="text-purple-600 hover:text-purple-800">Buat sekarang</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

@if($project->description || $project->notes)
<div class="bg-white rounded-lg shadow border border-gray-200 p-6">
    @if($project->description)<p class="text-gray-700 mb-3">{{ $project->description }}</p>@endif
    @if($project->notes)<p class="text-sm text-gray-500"><strong>Catatan:</strong> {{ $project->notes }}</p>@endif
</div>
@endif

@include('admin.components.modal', ['modalId' => 'projectModal', 'title' => 'Edit Project'])
@include('admin.components.modal', ['modalId' => 'purchaseModal', 'title' => 'Tambah Grosir'])
@include('admin.components.modal', ['modalId' => 'customerModal', 'title' => 'Tambah Customer'])
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const originalOpen = window.openResourceModal;
    if (typeof originalOpen !== 'function') return;

    window.openResourceModal = function(modalId, resourceName, singularName, id) {
        originalOpen(modalId, resourceName, singularName, id);
        if (resourceName !== 'projects') return;
        setTimeout(function() { if (typeof window.initProjectForm === 'function') window.initProjectForm(); }, 200);
        setTimeout(function() { if (typeof window.initProjectForm === 'function') window.initProjectForm(); }, 450);
    };
});
</script>
@endpush
