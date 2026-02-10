@extends('admin.keuangan.layout')

@section('title', 'Laporan Laba Rugi - Keuangan')

@section('keuangan_content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Laporan Laba Rugi</h2>
    <p class="text-gray-600 mt-1">Pendapatan dan beban dalam periode</p>
</div>

<!-- Filter Periode -->
<div class="bg-white rounded-lg shadow border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('admin.keuangan.laporan.laba-rugi') }}" class="flex flex-wrap items-end gap-4">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>
        <div>
            <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
                Filter
            </button>
        </div>
        <div>
            <a href="{{ route('admin.keuangan.laporan.laba-rugi') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Ringkasan Laba Rugi -->
<div class="bg-white rounded-lg shadow border border-gray-200 mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Laporan Laba Rugi</h3>
        <p class="text-sm text-gray-600 mt-1">Periode: {{ \Carbon\Carbon::parse($startDate)->locale('id')->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->locale('id')->translatedFormat('d F Y') }}</p>
    </div>
    <div class="p-6">
        <table class="w-full">
            <tbody class="space-y-2">
                <tr>
                    <td class="py-2 text-gray-700 font-medium">Pendapatan Penjualan</td>
                    <td class="py-2 text-right text-gray-900 font-semibold">Rp {{ number_format($pendapatanPenjualan, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-t border-gray-200">
                    <td class="py-2 text-gray-700">Beban Pembelian</td>
                    <td class="py-2 text-right text-red-600">(Rp {{ number_format($bebanPembelian, 0, ',', '.') }})</td>
                </tr>
                @if($bebanLainnya > 0)
                <tr>
                    <td class="py-2 text-gray-700">Beban Lainnya</td>
                    <td class="py-2 text-right text-red-600">(Rp {{ number_format($bebanLainnya, 0, ',', '.') }})</td>
                </tr>
                @endif
                <tr class="border-t-2 border-gray-300">
                    <td class="py-2 text-gray-700 font-semibold">Total Beban</td>
                    <td class="py-2 text-right text-red-600 font-semibold">(Rp {{ number_format($totalBeban, 0, ',', '.') }})</td>
                </tr>
                <tr class="border-t-2 border-gray-400">
                    <td class="py-3 text-lg font-bold text-gray-900">Laba Kotor</td>
                    <td class="py-3 text-right text-lg font-bold {{ $labaKotor >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($labaKotor, 0, ',', '.') }}
                    </td>
                </tr>
                <tr class="border-t border-gray-200">
                    <td class="py-2 text-sm text-gray-600">PPN Keluaran</td>
                    <td class="py-2 text-right text-sm text-gray-600">Rp {{ number_format($ppnKeluaran, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="py-2 text-sm text-gray-600">PPN Masukan</td>
                    <td class="py-2 text-right text-sm text-gray-600">(Rp {{ number_format($ppnMasukan, 0, ',', '.') }})</td>
                </tr>
                <tr class="border-t border-gray-200">
                    <td class="py-2 text-sm text-gray-600">PPN Terutang</td>
                    <td class="py-2 text-right text-sm {{ $ppnTerutang >= 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ $ppnTerutang >= 0 ? 'Rp ' : '(Rp ' }}{{ number_format(abs($ppnTerutang), 0, ',', '.') }}{{ $ppnTerutang >= 0 ? '' : ')' }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Detail Penjualan Bulanan -->
@if(count($penjualanBulanan) > 0)
<div class="bg-white rounded-lg shadow border border-gray-200 mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Detail Penjualan Bulanan</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bulan</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah Faktur</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total (dengan PPN)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($penjualanBulanan as $data)
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $data['bulan'] }}</td>
                    <td class="px-4 py-3 text-sm text-right text-gray-600">{{ $data['jumlah'] }}</td>
                    <td class="px-4 py-3 text-sm text-right text-gray-900">Rp {{ number_format($data['subtotal'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">Rp {{ number_format($data['total'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">Total</td>
                    <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">{{ collect($penjualanBulanan)->sum('jumlah') }}</td>
                    <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">Rp {{ number_format(collect($penjualanBulanan)->sum('subtotal'), 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">Rp {{ number_format(collect($penjualanBulanan)->sum('total'), 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif

<!-- Detail Pembelian Bulanan -->
@if(count($pembelianBulanan) > 0)
<div class="bg-white rounded-lg shadow border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Detail Pembelian Bulanan</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bulan</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah Faktur</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total (dengan PPN)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($pembelianBulanan as $data)
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $data['bulan'] }}</td>
                    <td class="px-4 py-3 text-sm text-right text-gray-600">{{ $data['jumlah'] }}</td>
                    <td class="px-4 py-3 text-sm text-right text-red-600">Rp {{ number_format($data['subtotal'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-right font-medium text-red-600">Rp {{ number_format($data['total'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">Total</td>
                    <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">{{ collect($pembelianBulanan)->sum('jumlah') }}</td>
                    <td class="px-4 py-3 text-sm text-right font-semibold text-red-600">Rp {{ number_format(collect($pembelianBulanan)->sum('subtotal'), 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-right font-semibold text-red-600">Rp {{ number_format(collect($pembelianBulanan)->sum('total'), 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif
@endsection
