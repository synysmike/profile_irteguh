@extends('admin.keuangan.layout')

@section('title', 'Grosir - Keuangan')

@section('keuangan_content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Transaksi Grosir</h2>
        <p class="text-gray-600 mt-1">Input barang grosir dengan detail qty & harga beli. Data ini menjadi sumber transaksi penjualan.</p>
    </div>
    <button type="button" onclick="openResourceModal('purchaseModal', 'purchases', 'Grosir')" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition text-sm font-semibold">
        + Tambah Grosir
    </button>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Faktur</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stok Tersisa</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pajak</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($purchases as $purchase)
            <tr id="purchasesRow_{{ $purchase->id }}">
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $purchase->invoice_number }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $purchase->purchase_date?->format('d/m/Y') }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ $purchase->displayDescription() }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $purchase->supplier?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-right text-gray-600">{{ number_format($purchase->quantity ?? 0, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-sm text-right text-gray-600">{{ number_format($purchase->remainingQuantity(), 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-sm text-right text-gray-600">Rp {{ number_format($purchase->subtotal, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-sm text-right text-gray-600">
                    {{ $purchase->tax_name ? $purchase->tax_name . ': ' : '' }}Rp {{ number_format($purchase->ppn_amount, 0, ',', '.') }}
                </td>
                <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-sm">
                    @if($purchase->cashTransaction)
                    <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">Terkait Kas</span>
                    @else
                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">Belum Posting</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right text-sm">
                    <a href="{{ route('admin.purchases.invoice', $purchase->id) }}" target="_blank" class="text-blue-600 hover:text-blue-800 mr-3">Cetak Invoice</a>
                    <button type="button" onclick="openResourceModal('purchaseModal', 'purchases', 'Grosir', {{ $purchase->id }})" class="text-purple-600 hover:text-purple-800 mr-3">Edit</button>
                    <button type="button" onclick="deleteResource('purchases', {{ $purchase->id }}, 'Grosir')" class="text-red-600 hover:text-red-800">Hapus</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                    Belum ada transaksi grosir.
                    <button type="button" onclick="openResourceModal('purchaseModal', 'purchases', 'Grosir')" class="text-purple-600 hover:text-purple-800 ml-1">Tambah yang pertama</button>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@include('admin.components.modal', ['modalId' => 'purchaseModal', 'title' => 'Tambah Grosir'])
@endsection
