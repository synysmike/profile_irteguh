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
        <button type="button" onclick="openResourceModal('projectModal', 'projects', 'Project', {{ $project->id }})" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 text-sm font-semibold">Edit Project</button>
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
        <form method="POST" action="{{ route('admin.projects.update-status', $project) }}" class="mt-4 space-y-3">
            @csrf
            @method('PATCH')
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                @foreach(\App\Models\Project::statusLabels() as $value => $label)
                <option value="{{ $value }}" {{ $project->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <input type="number" name="progress_percent" min="0" max="100" value="{{ $project->progress_percent }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Progress %">
            <button type="submit" class="w-full px-3 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-900">Update Progress</button>
        </form>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="text-sm text-gray-500 mb-1">Nilai & Pajak</div>
        <div class="space-y-1 text-sm">
            <div class="flex justify-between"><span>DPP jasa</span><span>Rp {{ number_format($project->base_subtotal ?? 0, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span>DPP stok</span><span>Rp {{ number_format($project->stockSubtotal(), 0, ',', '.') }}</span></div>
            <div class="flex justify-between font-medium"><span>DPP total</span><span>Rp {{ number_format($project->subtotal, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span>{{ $project->tax_name ?: 'Pajak' }}</span><span>Rp {{ number_format($project->ppn_amount, 0, ',', '.') }}</span></div>
            <div class="flex justify-between font-semibold border-t pt-2"><span>Total</span><span>Rp {{ number_format($project->total, 0, ',', '.') }}</span></div>
        </div>
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

{{-- Alokasi Stok --}}
<div id="alokasi-stok" class="bg-white rounded-lg shadow border-2 border-purple-300 overflow-hidden mb-6">
    <div class="px-4 sm:px-6 py-4 border-b border-purple-100 bg-purple-50 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Alokasi Stok untuk Project</h3>
            <p class="text-sm text-gray-600 mt-1">Ambil unit dari stok grosir. Subtotal otomatis masuk ke DPP, pajak, dan termin pembayaran.</p>
        </div>
        <a href="{{ route('admin.keuangan.sale-transactions.index') }}" class="text-sm text-purple-700 hover:text-purple-900 font-medium">Kelola Alokasi Stok →</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Grosir</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Harga</th>
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
                    <td colspan="6" class="px-4 py-6 text-center text-gray-500 text-sm">Belum ada stok terpasang. Gunakan form di bawah untuk melampirkan.</td>
                </tr>
                @endforelse
            </tbody>
            @if($project->saleTransactions->isNotEmpty())
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="4" class="px-4 py-3 text-sm font-semibold text-right text-gray-700">Total alokasi stok</td>
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
            <div class="text-sm font-semibold text-gray-800">1. Tambah dari stok grosir</div>
            @if($availablePurchases->isEmpty())
            <p class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-md px-3 py-2">
                Stok grosir tersisa kosong. Input dulu di
                <a href="{{ route('admin.keuangan.transaksi.pembelian') }}" class="underline font-medium">Grosir</a>.
            </p>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
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
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Termin Pembayaran</h3>
        <p class="text-sm text-gray-500 mt-1">Klik "Posting ke Keuangan" untuk membuat invoice penjualan, pajak, dan penerimaan kas.</p>
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
                    @if($term->sale_id)
                    <a href="{{ route('admin.sales.invoice', $term->sale_id) }}" target="_blank" class="text-blue-600 hover:text-blue-800 mr-3">Invoice</a>
                    @endif
                    @if($term->status !== 'paid')
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
