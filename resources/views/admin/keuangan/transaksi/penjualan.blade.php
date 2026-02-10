@extends('admin.keuangan.layout')

@section('title', 'Penjualan - Keuangan')

@section('keuangan_content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Transaksi Penjualan</h2>
        <p class="text-gray-600 mt-1">Faktur penjualan, PPN keluaran. Terkait Data Klien (Customer) & Akun Perkiraan.</p>
    </div>
    <div class="flex gap-2">
        <button type="button" id="btn-open-add-transaction" class="px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-800 transition text-sm font-semibold">
            + Tambah Transaksi
        </button>
        <button type="button" onclick="openResourceModal('saleModal', 'sales', 'Penjualan')" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition text-sm font-semibold">
            Buat Invoice
        </button>
    </div>
</div>

{{-- Daftar sementara: transaksi yang akan di-invoice --}}
<div id="pending-widget" class="mb-6 bg-amber-50 border border-amber-200 rounded-lg p-4">
    <div class="flex items-center justify-between mb-2">
        <h3 class="text-sm font-semibold text-amber-900">Daftar transaksi sementara</h3>
        <button type="button" id="btn-clear-pending" class="text-xs text-amber-700 hover:text-amber-900 font-medium">Kosongkan</button>
    </div>
    <div id="pending-widget-list" class="space-y-2 min-h-[60px]">
        <p class="text-sm text-amber-700 py-2">Belum ada transaksi. Klik &quot;Tambah Transaksi&quot; untuk menambah ke daftar.</p>
    </div>
    <div id="pending-widget-totals" class="hidden mt-3 pt-3 border-t border-amber-200 text-sm">
        <div class="flex justify-between"><span class="text-amber-800">Subtotal (DPP)</span><span id="pending-subtotal" class="font-medium">Rp 0</span></div>
        <div class="flex justify-between"><span class="text-amber-800">PPN (11%)</span><span id="pending-ppn">Rp 0</span></div>
        <div class="flex justify-between font-semibold text-amber-900"><span>Total</span><span id="pending-total">Rp 0</span></div>
    </div>
    <p class="text-xs text-amber-600 mt-2">Setelah daftar siap, klik &quot;Buat Invoice&quot; untuk membuat faktur penjualan.</p>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Faktur</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">PPN</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($sales as $sale)
            <tr id="salesRow_{{ $sale->id }}">
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $sale->invoice_number }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $sale->sale_date?->format('d/m/Y') }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $sale->customer?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-right text-gray-600">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-sm text-right text-gray-600">Rp {{ number_format($sale->ppn_amount, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-sm">
                    @if($sale->cashTransaction)
                    <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">Terkait Kas</span>
                    @else
                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">Belum Posting</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right text-sm">
                    <a href="{{ route('admin.sales.invoice', $sale->id) }}" target="_blank" class="text-blue-600 hover:text-blue-800 mr-3">Cetak Invoice</a>
                    <button type="button" onclick="openResourceModal('saleModal', 'sales', 'Penjualan', {{ $sale->id }})" class="text-purple-600 hover:text-purple-800 mr-3">Edit</button>
                    <button type="button" onclick="deleteResource('sales', {{ $sale->id }}, 'Penjualan')" class="text-red-600 hover:text-red-800">Hapus</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                    Belum ada transaksi penjualan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Modal: Tambah Transaksi (hanya menambah ke daftar sementara) --}}
<div id="addTransactionModal" class="fixed inset-0 bg-black bg-opacity-50 z-[60] hidden items-center justify-center" style="display: none;">
    <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-bold text-gray-800">Tambah Transaksi</h3>
            <button type="button" id="btn-close-add-transaction" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <div class="p-4 overflow-y-auto flex-1">
            <p class="text-sm text-gray-600 mb-3">Pilih satu atau lebih transaksi (Ctrl+klik untuk multi), lalu klik Tambah ke Daftar.</p>
            <select id="add-transaction-select" multiple size="10" class="w-full border border-gray-300 rounded-md p-2 text-sm">
                @foreach(\App\Models\SaleTransaction::active()->orderBy('description')->get() as $t)
                    <option value="{{ $t->id }}" data-subtotal="{{ $t->subtotal }}">
                        {{ $t->description }}{{ $t->code ? ' (' . $t->code . ')' : '' }} - Rp {{ number_format($t->subtotal, 0, ',', '.') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2 p-4 border-t">
            <button type="button" id="btn-add-to-pending" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 text-sm font-medium">Tambah ke Daftar</button>
            <button type="button" id="btn-cancel-add-transaction" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm">Batal</button>
        </div>
    </div>
</div>

@include('admin.components.modal', ['modalId' => 'saleModal', 'title' => 'Buat Invoice'])
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const PENDING_LIST_URL = '{{ route("admin.sales.pending-transactions.list") }}';
    const PENDING_ADD_URL = '{{ route("admin.sales.pending-transactions.add") }}';
    const PENDING_REMOVE_URL_BASE = '{{ url("admin/sales/pending-transactions") }}';
    const PENDING_CLEAR_URL = '{{ route("admin.sales.pending-transactions.clear") }}';

    const widgetList = document.getElementById('pending-widget-list');
    const widgetTotals = document.getElementById('pending-widget-totals');
    const pendingSubtotal = document.getElementById('pending-subtotal');
    const pendingPpn = document.getElementById('pending-ppn');
    const pendingTotal = document.getElementById('pending-total');

    if (!widgetList || !widgetTotals) return;

    function formatRupiah(n) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
    }

    function renderPendingWidget(data) {
        if (!data.items || data.items.length === 0) {
            widgetList.innerHTML = '<p class="text-sm text-amber-700 py-2">Belum ada transaksi. Klik &quot;Tambah Transaksi&quot; untuk menambah ke daftar.</p>';
            widgetTotals.classList.add('hidden');
        } else {
            let html = '';
            data.items.forEach(function(it) {
                html += '<div class="flex items-center justify-between bg-white rounded border border-amber-200 p-2">';
                html += '<div class="flex-1 text-sm"><span class="font-medium text-gray-800">' + (it.description || '') + '</span>';
                html += ' <span class="text-xs text-gray-500">Qty: ' + it.quantity + ' × Rp ' + new Intl.NumberFormat('id-ID').format(it.unit_price) + ' = Rp ' + new Intl.NumberFormat('id-ID').format(it.subtotal) + '</span></div>';
                html += '<button type="button" class="pending-remove text-red-600 hover:text-red-800 text-xs ml-2" data-id="' + it.id + '">Hapus</button>';
                html += '</div>';
            });
            widgetList.innerHTML = html;
            widgetList.querySelectorAll('.pending-remove').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    removePending(this.getAttribute('data-id'));
                });
            });
            if (pendingSubtotal) pendingSubtotal.textContent = formatRupiah(data.subtotal || 0);
            if (pendingPpn) pendingPpn.textContent = formatRupiah(data.ppn_amount || 0);
            if (pendingTotal) pendingTotal.textContent = formatRupiah(data.total || 0);
            widgetTotals.classList.remove('hidden');
        }
        if (window.refreshPendingInSaleModal) window.refreshPendingInSaleModal();
    }

    function loadPending() {
        fetch(PENDING_LIST_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(renderPendingWidget)
            .catch(function() { renderPendingWidget({ items: [], subtotal: 0, ppn_amount: 0, total: 0 }); });
    }

    function addPending(transactionIds) {
        const token = document.querySelector('meta[name="csrf-token"]');
        fetch(PENDING_ADD_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token ? token.content : '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ transaction_ids: transactionIds })
        })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                renderPendingWidget(data);
                document.getElementById('addTransactionModal').classList.add('hidden');
                document.getElementById('addTransactionModal').style.display = 'none';
            })
            .catch(function() { alert('Gagal menambah transaksi.'); });
    }

    function removePending(id) {
        const token = document.querySelector('meta[name="csrf-token"]');
        fetch(PENDING_REMOVE_URL_BASE + '/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token ? token.content : '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(function(r) { return r.json(); })
            .then(renderPendingWidget)
            .catch(function() { alert('Gagal menghapus.'); });
    }

    function clearPending() {
        if (!confirm('Kosongkan seluruh daftar transaksi sementara?')) return;
        const token = document.querySelector('meta[name="csrf-token"]');
        fetch(PENDING_CLEAR_URL, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token ? token.content : '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(function(r) { return r.json(); })
            .then(renderPendingWidget)
            .catch(function() {});
    }

    var btnOpen = document.getElementById('btn-open-add-transaction');
    var btnClose = document.getElementById('btn-close-add-transaction');
    var btnCancel = document.getElementById('btn-cancel-add-transaction');
    var btnAdd = document.getElementById('btn-add-to-pending');
    var btnClear = document.getElementById('btn-clear-pending');

    if (btnOpen) btnOpen.addEventListener('click', function() {
        var m = document.getElementById('addTransactionModal');
        if (m) { m.classList.remove('hidden'); m.style.display = 'flex'; }
    });
    if (btnClose) btnClose.addEventListener('click', function() {
        var m = document.getElementById('addTransactionModal');
        if (m) { m.classList.add('hidden'); m.style.display = 'none'; }
    });
    if (btnCancel) btnCancel.addEventListener('click', function() {
        var m = document.getElementById('addTransactionModal');
        if (m) { m.classList.add('hidden'); m.style.display = 'none'; }
    });
    if (btnAdd) btnAdd.addEventListener('click', function() {
        var sel = document.getElementById('add-transaction-select');
        if (!sel) return;
        var ids = [];
        for (var i = 0; i < sel.options.length; i++) {
            if (sel.options[i].selected && sel.options[i].value) ids.push(sel.options[i].value);
        }
        if (ids.length === 0) {
            alert('Pilih minimal 1 transaksi.');
            return;
        }
        addPending(ids);
    });
    if (btnClear) btnClear.addEventListener('click', clearPending);

    loadPending();
    window.refreshPendingWidget = loadPending;
});
</script>
@endpush
