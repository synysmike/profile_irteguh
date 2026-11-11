@extends('admin.keuangan.layout')

@section('title', 'Kasir POS - Keuangan')

@section('keuangan_content')
<div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center sm:justify-between gap-3 mb-4">
    <div class="min-w-0">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Kasir POS</h2>
        <p class="text-gray-600 mt-1 text-sm sm:text-base">Alur: Grosir (stok masuk) → Kasir (jual) → Faktur. Pilih barang, isi keranjang, lalu cetak/kirim invoice.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <button type="button" onclick="openResourceModal('purchaseModal', 'purchases', 'Grosir')" class="flex-1 sm:flex-none px-3 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 text-sm font-semibold">
            + Tambah Grosir
        </button>
        <a href="{{ route('admin.keuangan.transaksi.pembelian') }}" class="flex-1 sm:flex-none text-center px-3 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium">Daftar Grosir</a>
        <a href="#faktur-list" class="flex-1 sm:flex-none text-center px-3 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium">Daftar Faktur</a>
    </div>
</div>

@if(session('error'))
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4"><p class="text-red-700 text-sm">{{ session('error') }}</p></div>
@endif
@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4"><p class="text-green-700 text-sm">{{ session('success') }}</p></div>
@endif

@php $layananPendingSales = $sales->where('is_layanan_pending', true)->count(); @endphp
@if($layananPendingSales > 0)
<div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-4">
    <p class="text-orange-800 text-sm font-medium">{{ $layananPendingSales }} draft Order Layanan menunggu konfirmasi (lihat badge di daftar faktur).</p>
</div>
@endif

<div id="pos-alert" class="hidden mb-4 rounded-lg p-3 text-sm"></div>

{{-- POS layout --}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-4 mb-8">
    {{-- Catalog --}}
    <div class="xl:col-span-7 bg-white rounded-lg shadow border border-gray-200 overflow-hidden flex flex-col min-h-[420px]">
        <div class="p-4 border-b border-gray-200 space-y-3">
            <div class="flex items-center justify-between gap-2">
                <h3 class="font-semibold text-gray-800">Stok Siap Jual</h3>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="openResourceModal('purchaseModal', 'purchases', 'Grosir')" class="text-xs text-purple-600 hover:text-purple-800 font-medium">+ Grosir</button>
                    <button type="button" id="pos-refresh" class="text-xs text-gray-600 hover:text-gray-800 font-medium">Refresh</button>
                </div>
            </div>
            <input type="search" id="pos-search" placeholder="Cari barang / no. PO / supplier..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
            <div class="flex gap-2 text-xs">
                <button type="button" data-tab="stock" class="pos-tab px-3 py-1.5 rounded-full bg-purple-600 text-white font-medium">Stok Grosir</button>
                <button type="button" data-tab="ready" class="pos-tab px-3 py-1.5 rounded-full bg-gray-100 text-gray-700 font-medium">Alokasi Siap Invoice</button>
            </div>
        </div>
        <div id="pos-catalog" class="flex-1 overflow-y-auto max-h-[520px] divide-y divide-gray-100">
            <p class="p-6 text-sm text-gray-500 text-center">Memuat stok...</p>
        </div>
    </div>

    {{-- Cart / checkout --}}
    <div class="xl:col-span-5 bg-white rounded-lg shadow border border-gray-200 overflow-hidden flex flex-col min-h-[420px]">
        <div class="p-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800">Keranjang & Checkout</h3>
        </div>
        <div class="p-4 space-y-3 border-b border-gray-100">
            <div>
                <div class="flex items-center justify-between gap-2 mb-1">
                    <label class="block text-xs font-medium text-gray-600">Customer *</label>
                    <button type="button"
                            onclick="openResourceModal('customerModal', 'customers', 'Customer', null, '?quick=1')"
                            class="text-xs text-purple-600 hover:text-purple-800 font-medium">
                        + Tambah
                    </button>
                </div>
                <select id="pos-customer" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <option value="">-- Pilih customer --</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}" data-phone="{{ $c->phone }}">{{ $c->name }}{{ $c->company_name ? ' ('.$c->company_name.')' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">No. Faktur *</label>
                    <input type="text" id="pos-invoice" value="{{ $nextInvoiceNumber }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal *</label>
                    <input type="date" id="pos-date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Pajak</label>
                <select id="pos-tax" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <option value="" data-rate="0" data-calculation="addition">Tanpa Pajak</option>
                    @foreach($taxes as $tax)
                    <option value="{{ $tax->id }}" data-rate="{{ $tax->rate }}" data-calculation="{{ $tax->calculation_type }}">
                        {{ $tax->name }} ({{ number_format($tax->rate, 2, ',', '.') }}%)
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                <input type="text" id="pos-notes" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Opsional">
            </div>
            <label class="inline-flex items-start gap-2 cursor-pointer rounded-md border border-gray-200 bg-gray-50 px-3 py-2">
                <input type="checkbox" id="pos-show-public" value="1" class="mt-0.5 w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                <span>
                    <span class="block text-xs font-medium text-gray-800">Tampilkan di halaman publik</span>
                    <span class="block text-[11px] text-gray-500">Muncul di katalog /penjualan (non-project)</span>
                </span>
            </label>
        </div>

        <div id="pos-cart" class="flex-1 overflow-y-auto max-h-[240px] p-3 space-y-2 bg-gray-50">
            <p class="text-sm text-gray-500 text-center py-6">Keranjang kosong. Pilih barang di kiri.</p>
        </div>

        <div class="p-4 border-t border-gray-200 space-y-2 bg-white">
            <div class="flex justify-between text-sm"><span class="text-gray-600">Subtotal</span><span id="pos-subtotal" class="font-medium">Rp 0</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-600">Pajak</span><span id="pos-tax-amount">Rp 0</span></div>
            <div class="flex justify-between text-base font-semibold"><span>Total</span><span id="pos-total">Rp 0</span></div>
            <div class="flex gap-2 pt-2">
                <button type="button" id="pos-clear" class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-200">Kosongkan</button>
                <button type="button" id="pos-checkout" class="flex-[2] px-3 py-2 bg-purple-600 text-white rounded-md text-sm font-semibold hover:bg-purple-700 disabled:opacity-50" disabled>Buat Invoice</button>
            </div>
        </div>
    </div>
</div>

{{-- Invoice list --}}
<div id="faktur-list" class="mb-2">
    <h3 class="text-lg font-bold text-gray-800 mb-3">Daftar Faktur Penjualan</h3>
</div>
<div class="bg-white rounded-lg shadow border border-gray-200 overflow-x-auto">
    <table class="min-w-[1100px] w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Faktur</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pajak</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Publik</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($sales as $sale)
            <tr id="salesRow_{{ $sale->id }}">
                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                    {{ $sale->invoice_number }}
                    @if($sale->is_layanan_pending)
                    <span class="ml-1 px-2 py-0.5 text-[10px] rounded-full bg-orange-100 text-orange-800 align-middle">Order Layanan</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $sale->sale_date?->format('d/m/Y') }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $sale->customer?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">
                    @if($sale->project)
                    <a href="{{ route('admin.projects.show', $sale->project_id) }}" class="text-indigo-600 hover:text-indigo-800">{{ $sale->project->code }}</a>
                    @else
                    —
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-right text-gray-600">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-sm text-right text-gray-600">
                    {{ $sale->tax_name ? $sale->tax_name . ': ' : '' }}Rp {{ number_format($sale->ppn_amount, 0, ',', '.') }}
                </td>
                <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-sm">
                    @if($sale->cashTransaction)
                    <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">Terkait Kas</span>
                    @else
                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">Belum Posting</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm">
                    @if($sale->project_id)
                    <span class="text-xs text-gray-400" title="Faktur project tidak masuk katalog penjualan publik">—</span>
                    @else
                    <form method="POST" action="{{ route('admin.sales.toggle-public', $sale) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="px-2 py-0.5 text-xs rounded-full font-medium {{ $sale->show_in_public ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                                title="{{ $sale->show_in_public ? 'Klik untuk sembunyikan dari publik' : 'Klik untuk tampilkan di /penjualan' }}">
                            {{ $sale->show_in_public ? 'Tampil' : 'Sembunyi' }}
                        </button>
                    </form>
                    @endif
                </td>
                <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                    @if($sale->is_layanan_pending)
                    <form method="POST" action="{{ route('admin.sales.layanan.confirm', $sale) }}" class="inline mr-2" onsubmit="return confirm('Konfirmasi order ini? Stok alokasi akan dipotong dan kas diposting.')">
                        @csrf
                        <button type="submit" class="text-emerald-700 hover:text-emerald-900 font-medium">Konfirmasi</button>
                    </form>
                    <form method="POST" action="{{ route('admin.sales.layanan.reject', $sale) }}" class="inline mr-2" onsubmit="return confirm('Tolak & hapus draft order ini? Stok tidak terpengaruh.')">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800">Tolak</button>
                    </form>
                    @endif
                    <a href="{{ $sale->publicInvoiceUrl() }}" target="_blank" class="text-blue-600 hover:text-blue-800 mr-3">Cetak</a>
                    @if($sale->customer?->phone)
                    <a href="{{ route('admin.sales.whatsapp', $sale->id) }}" target="_blank" rel="noopener" class="text-green-600 hover:text-green-800 mr-3" title="Kirim ke {{ $sale->customer->phone }}">WA</a>
                    @else
                    <span class="text-gray-400 mr-3 cursor-not-allowed" title="Customer belum punya nomor HP">WA</span>
                    @endif
                    @if($sale->project)
                    <button type="button"
                            class="text-emerald-600 hover:text-emerald-800 mr-3"
                            onclick="openReviewWaModal(this)"
                            title="Minta ulasan project {{ $sale->project->code }}"
                            data-subject-label="Project"
                            data-code="{{ $sale->project->code }}"
                            data-title="{{ $sale->project->title }}"
                            data-customer="{{ $sale->customer?->name ?: $sale->project->customer?->name }}"
                            data-phone="{{ \App\Support\PhoneNumber::normalize($sale->customer?->phone ?: $sale->project->customer?->phone) }}"
                            data-phone-display="{{ $sale->customer?->phone ?: $sale->project->customer?->phone }}"
                            data-status="{{ $sale->project->statusLabel() }} · Progress {{ $sale->project->progress_percent }}%"
                            data-message="{{ json_encode(\App\Support\ProjectReviewWhatsApp::buildMessage($sale->project), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}">
                        Minta Ulasan
                    </button>
                    @else
                    <button type="button"
                            class="text-emerald-600 hover:text-emerald-800 mr-3"
                            onclick="openReviewWaModal(this)"
                            title="Minta ulasan faktur {{ $sale->invoice_number }}"
                            data-subject-label="Faktur"
                            data-code="{{ $sale->invoice_number }}"
                            data-title=""
                            data-customer="{{ $sale->customer?->name }}"
                            data-phone="{{ \App\Support\PhoneNumber::normalize($sale->customer?->phone) }}"
                            data-phone-display="{{ $sale->customer?->phone }}"
                            data-status="Total Rp {{ number_format((float) $sale->total, 0, ',', '.') }} · {{ $sale->sale_date?->format('d/m/Y') }}"
                            data-message="{{ json_encode(\App\Support\SaleReviewWhatsApp::buildMessage($sale), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}">
                        Minta Ulasan
                    </button>
                    @endif
                    <button type="button" onclick="openResourceModal('saleModal', 'sales', 'Penjualan', {{ $sale->id }})" class="text-purple-600 hover:text-purple-800 mr-3">Edit</button>
                    <button type="button" onclick="deleteResource('sales', {{ $sale->id }}, 'Penjualan')" class="text-red-600 hover:text-red-800">Hapus</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="px-4 py-8 text-center text-gray-500">Belum ada faktur. Selesaikan transaksi di kasir di atas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@include('admin.components.modal', ['modalId' => 'saleModal', 'title' => 'Edit Penjualan'])
@include('admin.components.modal', ['modalId' => 'purchaseModal', 'title' => 'Tambah Grosir'])
@include('admin.components.modal', ['modalId' => 'customerModal', 'title' => 'Tambah Customer'])
@include('admin.partials.review-wa-modal')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const CATALOG_URL = @json(route('admin.sales.pos.catalog'));
    const CHECKOUT_URL = @json(route('admin.sales.pos.checkout'));
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

    let catalog = { stock: [], ready: [] };
    let activeTab = 'stock';
    let cart = [];

    const elCatalog = document.getElementById('pos-catalog');
    const elCart = document.getElementById('pos-cart');
    const elSearch = document.getElementById('pos-search');
    const elAlert = document.getElementById('pos-alert');
    const elCheckout = document.getElementById('pos-checkout');

    function rp(n) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n || 0));
    }

    function showAlert(msg, type) {
        elAlert.className = 'mb-4 rounded-lg p-3 text-sm ' + (type === 'error'
            ? 'bg-red-50 border border-red-200 text-red-700'
            : 'bg-green-50 border border-green-200 text-green-700');
        elAlert.textContent = msg;
        elAlert.classList.remove('hidden');
        setTimeout(function() { elAlert.classList.add('hidden'); }, 5000);
    }

    function cartKey(item) {
        return item.type === 'sale_transaction'
            ? 'st:' + item.sale_transaction_id
            : 'p:' + item.purchase_id;
    }

    function renderCatalog() {
        const q = (elSearch.value || '').toLowerCase().trim();
        let rows = activeTab === 'ready' ? catalog.ready : catalog.stock;
        if (q) {
            rows = rows.filter(function(r) {
                return [r.description, r.invoice_number, r.supplier].join(' ').toLowerCase().indexOf(q) !== -1;
            });
        }

        if (!rows.length) {
            elCatalog.innerHTML = '<div class="p-6 text-sm text-gray-500 text-center space-y-3">' +
                '<p>' + (activeTab === 'ready'
                    ? 'Tidak ada alokasi siap invoice. Tambah dari stok grosir, atau buat alokasi di menu Alokasi Stok.'
                    : 'Stok grosir kosong. Tambah barang grosir dulu.') + '</p>' +
                (activeTab === 'stock'
                    ? '<button type="button" onclick="openResourceModal(\'purchaseModal\', \'purchases\', \'Grosir\')" class="px-4 py-2 bg-purple-600 text-white rounded-md text-sm font-semibold hover:bg-purple-700">+ Tambah Grosir</button>'
                    : '') +
                '</div>';
            return;
        }

        let html = '';
        rows.forEach(function(r) {
            if (r.type === 'sale_transaction') {
                html += '<div class="p-3 hover:bg-purple-50 flex items-start justify-between gap-3">';
                html += '<div class="min-w-0"><div class="font-medium text-sm text-gray-900">' + escapeHtml(r.description) + '</div>';
                html += '<div class="text-xs text-gray-500 mt-0.5">' + escapeHtml(r.invoice_number || '') + ' · Qty ' + r.quantity + ' · ' + rp(r.unit_price) + '</div></div>';
                html += '<button type="button" class="pos-add shrink-0 px-3 py-1.5 bg-purple-600 text-white rounded-md text-xs font-semibold" data-type="sale_transaction" data-id="' + r.sale_transaction_id + '">+ Keranjang</button>';
                html += '</div>';
            } else {
                html += '<div class="p-3 hover:bg-purple-50">';
                html += '<div class="flex items-start justify-between gap-3">';
                html += '<div class="min-w-0"><div class="font-medium text-sm text-gray-900">' + escapeHtml(r.description) + '</div>';
                html += '<div class="text-xs text-gray-500 mt-0.5">' + escapeHtml(r.invoice_number || '') + ' · ' + escapeHtml(r.supplier || '—') + ' · Stok ' + r.remaining_quantity + '</div>';
                html += '<div class="text-xs text-gray-500">Harga beli ' + rp(r.cost_unit_price) + '</div></div></div>';
                html += '<div class="mt-2 flex flex-wrap items-end gap-2">';
                html += '<div><label class="block text-[10px] text-gray-500">Qty</label><input type="number" min="1" max="' + r.remaining_quantity + '" value="1" class="pos-qty w-20 px-2 py-1 border border-gray-300 rounded text-sm" data-max="' + r.remaining_quantity + '"></div>';
                html += '<div><label class="block text-[10px] text-gray-500">Harga jual</label><input type="number" min="0" step="1" value="' + Math.round(r.suggested_unit_price) + '" class="pos-price w-28 px-2 py-1 border border-gray-300 rounded text-sm"></div>';
                html += '<button type="button" class="pos-add px-3 py-1.5 bg-purple-600 text-white rounded-md text-xs font-semibold" data-type="purchase" data-id="' + r.purchase_id + '">+ Keranjang</button>';
                html += '</div></div>';
            }
        });
        elCatalog.innerHTML = html;

        elCatalog.querySelectorAll('.pos-add').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const row = btn.closest('div.p-3') || btn.parentElement;
                const type = btn.getAttribute('data-type');
                const id = parseInt(btn.getAttribute('data-id'), 10);
                if (type === 'sale_transaction') {
                    const src = catalog.ready.find(function(x) { return x.sale_transaction_id === id; });
                    if (!src) return;
                    addToCart({
                        type: 'sale_transaction',
                        sale_transaction_id: src.sale_transaction_id,
                        purchase_id: src.purchase_id,
                        description: src.description,
                        quantity: src.quantity,
                        unit_price: src.unit_price,
                        max_qty: src.quantity,
                        locked: true
                    });
                } else {
                    const src = catalog.stock.find(function(x) { return x.purchase_id === id; });
                    if (!src) return;
                    const qtyInput = row.querySelector('.pos-qty');
                    const priceInput = row.querySelector('.pos-price');
                    let qty = parseInt(qtyInput?.value || '1', 10) || 1;
                    const max = src.remaining_quantity;
                    if (qty > max) qty = max;
                    const price = parseFloat(priceInput?.value || src.suggested_unit_price) || 0;
                    addToCart({
                        type: 'purchase',
                        purchase_id: src.purchase_id,
                        description: src.description,
                        quantity: qty,
                        unit_price: price,
                        max_qty: max,
                        locked: false
                    });
                }
            });
        });
    }

    function escapeHtml(s) {
        return String(s || '').replace(/[&<>"']/g, function(c) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
        });
    }

    function addToCart(item) {
        const key = cartKey(item);
        const existing = cart.find(function(c) { return cartKey(c) === key; });
        if (existing) {
            if (item.locked) {
                showAlert('Alokasi ini sudah di keranjang.', 'error');
                return;
            }
            const nextQty = existing.quantity + item.quantity;
            if (nextQty > existing.max_qty) {
                showAlert('Qty melebihi stok tersisa (' + existing.max_qty + ').', 'error');
                return;
            }
            existing.quantity = nextQty;
            existing.unit_price = item.unit_price;
        } else {
            cart.push(item);
        }
        renderCart();
    }

    function renderCart() {
        if (!cart.length) {
            elCart.innerHTML = '<p class="text-sm text-gray-500 text-center py-6">Keranjang kosong. Pilih barang di kiri.</p>';
        } else {
            let html = '';
            cart.forEach(function(item, idx) {
                const line = item.quantity * item.unit_price;
                html += '<div class="bg-white border border-gray-200 rounded-md p-2.5">';
                html += '<div class="flex justify-between gap-2"><div class="min-w-0"><div class="text-sm font-medium text-gray-900 truncate">' + escapeHtml(item.description) + '</div>';
                html += '<div class="text-xs text-gray-500">' + (item.locked ? 'Alokasi tetap' : 'Dari stok grosir') + '</div></div>';
                html += '<button type="button" class="pos-remove text-red-600 text-xs" data-idx="' + idx + '">Hapus</button></div>';
                if (!item.locked) {
                    html += '<div class="mt-2 flex gap-2 items-center">';
                    html += '<input type="number" min="1" max="' + item.max_qty + '" value="' + item.quantity + '" class="pos-cart-qty w-16 px-2 py-1 border rounded text-sm" data-idx="' + idx + '">';
                    html += '<span class="text-xs text-gray-500">×</span>';
                    html += '<input type="number" min="0" step="1" value="' + Math.round(item.unit_price) + '" class="pos-cart-price w-24 px-2 py-1 border rounded text-sm" data-idx="' + idx + '">';
                    html += '<span class="text-xs font-medium ml-auto">' + rp(line) + '</span></div>';
                } else {
                    html += '<div class="mt-1 text-xs text-gray-600">Qty ' + item.quantity + ' × ' + rp(item.unit_price) + ' = <strong>' + rp(line) + '</strong></div>';
                }
                html += '</div>';
            });
            elCart.innerHTML = html;

            elCart.querySelectorAll('.pos-remove').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    cart.splice(parseInt(btn.getAttribute('data-idx'), 10), 1);
                    renderCart();
                });
            });
            elCart.querySelectorAll('.pos-cart-qty').forEach(function(input) {
                input.addEventListener('change', function() {
                    const i = parseInt(input.getAttribute('data-idx'), 10);
                    let v = parseInt(input.value, 10) || 1;
                    if (v > cart[i].max_qty) v = cart[i].max_qty;
                    if (v < 1) v = 1;
                    cart[i].quantity = v;
                    renderCart();
                });
            });
            elCart.querySelectorAll('.pos-cart-price').forEach(function(input) {
                input.addEventListener('change', function() {
                    const i = parseInt(input.getAttribute('data-idx'), 10);
                    cart[i].unit_price = parseFloat(input.value) || 0;
                    renderCart();
                });
            });
        }
        updateTotals();
    }

    function updateTotals() {
        const subtotal = cart.reduce(function(s, i) { return s + (i.quantity * i.unit_price); }, 0);
        const taxOpt = document.getElementById('pos-tax').selectedOptions[0];
        const rate = parseFloat(taxOpt?.dataset.rate || 0) || 0;
        const calc = taxOpt?.dataset.calculation || 'addition';
        const taxAmount = Math.round(subtotal * rate / 100);
        const total = calc === 'deduction' ? Math.max(subtotal - taxAmount, 0) : (subtotal + taxAmount);

        document.getElementById('pos-subtotal').textContent = rp(subtotal);
        document.getElementById('pos-tax-amount').textContent = rp(taxAmount);
        document.getElementById('pos-total').textContent = rp(total);
        elCheckout.disabled = cart.length === 0 || !document.getElementById('pos-customer').value;
    }

    function loadCatalog() {
        elCatalog.innerHTML = '<p class="p-6 text-sm text-gray-500 text-center">Memuat stok...</p>';
        fetch(CATALOG_URL, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                catalog = data;
                renderCatalog();
            })
            .catch(function() {
                elCatalog.innerHTML = '<p class="p-6 text-sm text-red-600 text-center">Gagal memuat stok.</p>';
            });
    }

    document.querySelectorAll('.pos-tab').forEach(function(btn) {
        btn.addEventListener('click', function() {
            activeTab = btn.getAttribute('data-tab');
            document.querySelectorAll('.pos-tab').forEach(function(b) {
                b.className = 'pos-tab px-3 py-1.5 rounded-full text-xs font-medium ' +
                    (b.getAttribute('data-tab') === activeTab ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700');
            });
            renderCatalog();
        });
    });

    elSearch.addEventListener('input', renderCatalog);
    document.getElementById('pos-refresh').addEventListener('click', loadCatalog);
    document.getElementById('pos-customer').addEventListener('change', updateTotals);
    document.getElementById('pos-tax').addEventListener('change', updateTotals);
    document.getElementById('pos-clear').addEventListener('click', function() {
        cart = [];
        renderCart();
    });

    elCheckout.addEventListener('click', function() {
        const customerId = document.getElementById('pos-customer').value;
        const invoiceNumber = document.getElementById('pos-invoice').value.trim();
        const saleDate = document.getElementById('pos-date').value;
        if (!customerId) { showAlert('Pilih customer terlebih dahulu.', 'error'); return; }
        if (!invoiceNumber) { showAlert('No. faktur wajib diisi.', 'error'); return; }
        if (!cart.length) { showAlert('Keranjang masih kosong.', 'error'); return; }

        elCheckout.disabled = true;
        elCheckout.textContent = 'Menyimpan...';

        const payload = {
            customer_id: parseInt(customerId, 10),
            tax_id: document.getElementById('pos-tax').value || null,
            invoice_number: invoiceNumber,
            sale_date: saleDate,
            notes: document.getElementById('pos-notes').value || null,
            show_in_public: document.getElementById('pos-show-public')?.checked ? 1 : 0,
            cart: cart.map(function(item) {
                if (item.type === 'sale_transaction') {
                    return { type: 'sale_transaction', sale_transaction_id: item.sale_transaction_id };
                }
                return {
                    type: 'purchase',
                    purchase_id: item.purchase_id,
                    quantity: item.quantity,
                    unit_price: item.unit_price
                };
            })
        };

        fetch(CHECKOUT_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
            .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, status: r.status, data: d }; }); })
            .then(function(res) {
                if (!res.ok) {
                    const msg = res.data.message
                        || (res.data.errors && Object.values(res.data.errors).flat().join(' '))
                        || 'Gagal membuat invoice.';
                    showAlert(msg, 'error');
                    elCheckout.disabled = false;
                    elCheckout.textContent = 'Buat Invoice';
                    updateTotals();
                    return;
                }
                showAlert('Invoice ' + res.data.invoice_number + ' berhasil dibuat.', 'success');
                setTimeout(function() { window.location.reload(); }, 700);
            })
            .catch(function() {
                showAlert('Gagal menghubungi server.', 'error');
                elCheckout.disabled = false;
                elCheckout.textContent = 'Buat Invoice';
                updateTotals();
            });
    });

    loadCatalog();
    renderCart();
});
</script>
@endpush
