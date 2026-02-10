@extends('admin.keuangan.layout')

@section('title', 'PPN - Keuangan')

@section('keuangan_content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">PPN (Pajak Pertambahan Nilai)</h2>
    <p class="text-gray-600 mt-1">Rekap PPN keluaran & masukan. Laporan SPT Masa PPN (jika omzet > Rp 4,8 miliar)</p>
</div>

<!-- Ringkasan PPN -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
        <div class="text-sm text-gray-600 mb-1">PPN Keluaran</div>
        <div class="text-2xl font-bold text-red-600">Rp {{ number_format($ppnKeluaran, 0, ',', '.') }}</div>
        <div class="text-xs text-gray-500 mt-1">Dari faktur penjualan</div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
        <div class="text-sm text-gray-600 mb-1">PPN Masukan</div>
        <div class="text-2xl font-bold text-blue-600">Rp {{ number_format($ppnMasukan, 0, ',', '.') }}</div>
        <div class="text-xs text-gray-500 mt-1">Dari faktur pembelian</div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
        <div class="text-sm text-gray-600 mb-1">PPN Terutang</div>
        <div class="text-2xl font-bold {{ $ppnTerutang >= 0 ? 'text-green-600' : 'text-orange-600' }}">
            Rp {{ number_format($ppnTerutang, 0, ',', '.') }}
        </div>
        <div class="text-xs text-gray-500 mt-1">
            {{ $ppnTerutang >= 0 ? 'Harus dibayar' : 'Lebih bayar (dikreditkan)' }}
        </div>
    </div>
</div>

<!-- Rekap Bulanan -->
<div class="bg-white rounded-lg shadow border border-gray-200 mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Rekap PPN Bulanan {{ date('Y') }}</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bulan</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">PPN Keluaran</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">PPN Masukan</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">PPN Terutang</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($ppnBulanan as $month => $data)
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ \Carbon\Carbon::create(null, $month, 1)->locale('id')->translatedFormat('F Y') }}</td>
                    <td class="px-4 py-3 text-sm text-right text-red-600">Rp {{ number_format($data['keluaran'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-right text-blue-600">Rp {{ number_format($data['masukan'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-right font-medium {{ $data['terutang'] >= 0 ? 'text-green-600' : 'text-orange-600' }}">
                        Rp {{ number_format($data['terutang'], 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Detail Faktur Penjualan (PPN Keluaran) -->
<div class="bg-white rounded-lg shadow border border-gray-200 mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Faktur Penjualan (PPN Keluaran)</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Faktur</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">PPN</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($sales->where('ppn_amount', '>', 0) as $sale)
                <tr>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $sale->invoice_number }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $sale->sale_date?->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $sale->customer?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-right text-gray-600">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-right text-red-600 font-medium">Rp {{ number_format($sale->ppn_amount, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-right text-gray-900">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada faktur penjualan dengan PPN.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Detail Faktur Pembelian (PPN Masukan) -->
<div class="bg-white rounded-lg shadow border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Faktur Pembelian (PPN Masukan)</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Faktur</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">PPN</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($purchases->where('ppn_amount', '>', 0) as $purchase)
                <tr>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $purchase->invoice_number }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $purchase->purchase_date?->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $purchase->supplier?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-right text-gray-600">Rp {{ number_format($purchase->subtotal, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-right text-blue-600 font-medium">Rp {{ number_format($purchase->ppn_amount, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-right text-gray-900">Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada faktur pembelian dengan PPN.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
