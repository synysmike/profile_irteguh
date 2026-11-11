@extends('admin.keuangan.layout')

@section('title', 'Alokasi Stok - Keuangan')

@section('keuangan_content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Alokasi Stok</h2>
        <p class="text-gray-600 mt-1">Opsional: alokasi stok grosir sebelum masuk kasir. Biasanya cukup jual langsung dari Kasir POS.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.keuangan.transaksi.penjualan') }}" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium">Ke Kasir POS</a>
        <button type="button" onclick="openResourceModal('saleTransactionModal', 'keuangan/sale-transactions', 'Alokasi')" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition text-sm font-semibold">
            + Tambah Alokasi
        </button>
    </div>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Grosir</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Harga Satuan</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Diskon</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Layanan Kami</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($transactions as $transaction)
            <tr id="sale-transactionsRow_{{ $transaction->id }}">
                <td class="px-4 py-3 text-sm text-gray-600">
                    @if($transaction->purchase)
                    <span class="font-medium text-gray-900">{{ $transaction->purchase->invoice_number }}</span>
                    <span class="block text-xs text-gray-500">{{ $transaction->purchase->supplier?->name ?? '—' }}</span>
                    @else
                    <span class="text-amber-600">Belum terhubung</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $transaction->code ?? '—' }}</td>
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $transaction->description }}</td>
                <td class="px-4 py-3 text-sm text-right text-gray-600">{{ number_format($transaction->quantity, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-sm text-right text-gray-600">Rp {{ number_format($transaction->unit_price, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-sm text-right text-red-700">
                    @if((float) ($transaction->discount_amount ?? 0) > 0)
                    - Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}
                    @else
                    —
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-sm">
                    @if($transaction->is_active)
                    <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>
                    @else
                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">Nonaktif</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm">
                    @if($transaction->isInvoiced() || $transaction->project_id)
                    <span class="text-xs text-gray-400" title="Sudah diinvoice / terikat project">—</span>
                    @elseif(! $transaction->purchase_id)
                    <span class="text-xs text-amber-700" title="Hubungkan ke grosir dulu agar stok/HPP disiplin">Perlu grosir</span>
                    @elseif($transaction->show_in_layanan)
                    <div class="space-y-1">
                        <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-800">Tampil</span>
                        <div class="text-[11px] text-gray-500">{{ $transaction->service?->title }}</div>
                        <form method="POST" action="{{ route('admin.keuangan.sale-transactions.update-layanan', $transaction) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="show_in_layanan" value="0">
                            <button type="submit" class="text-xs text-amber-700 hover:text-amber-900">Sembunyikan</button>
                        </form>
                    </div>
                    @else
                    <details class="text-left">
                        <summary class="cursor-pointer text-xs text-purple-600 hover:text-purple-800 font-medium">Tampilkan</summary>
                        <form method="POST" action="{{ route('admin.keuangan.sale-transactions.update-layanan', $transaction) }}" class="mt-2 space-y-2 min-w-[11rem]">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="show_in_layanan" value="1">
                            <select name="service_id" required class="w-full text-xs border border-gray-300 rounded px-2 py-1.5">
                                <option value="">Kategori *</option>
                                @foreach($services as $service)
                                <option value="{{ $service->id }}">{{ $service->title }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="w-full text-xs px-2 py-1.5 bg-purple-600 text-white rounded hover:bg-purple-700">Simpan</button>
                        </form>
                    </details>
                    @endif
                </td>
                <td class="px-4 py-3 text-right text-sm">
                    <button type="button" onclick="openResourceModal('saleTransactionModal', 'keuangan/sale-transactions', 'Transaksi', {{ $transaction->id }})" class="text-purple-600 hover:text-purple-800 mr-3">Edit</button>
                    <button type="button" onclick="deleteResource('keuangan/sale-transactions', {{ $transaction->id }}, 'Transaksi')" class="text-red-600 hover:text-red-800">Hapus</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                    Belum ada transaksi. Pastikan data grosir sudah diinput terlebih dahulu.
                    <button type="button" onclick="openResourceModal('saleTransactionModal', 'keuangan/sale-transactions', 'Transaksi')" class="text-purple-600 hover:text-purple-800 ml-1">Tambah yang pertama</button>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@include('admin.components.modal', ['modalId' => 'saleTransactionModal', 'title' => 'Tambah Transaksi'])
@endsection
