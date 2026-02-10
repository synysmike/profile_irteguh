@extends('admin.keuangan.layout')

@section('title', 'Laporan Arus Kas - Keuangan')

@section('keuangan_content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Laporan Arus Kas</h2>
    <p class="text-gray-600 mt-1">Arus kas operasi, investasi, pendanaan</p>
</div>

<!-- Filter Periode -->
<div class="bg-white rounded-lg shadow border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('admin.keuangan.laporan.arus-kas') }}" class="flex flex-wrap items-end gap-4">
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
            <a href="{{ route('admin.keuangan.laporan.arus-kas') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Ringkasan Arus Kas -->
<div class="bg-white rounded-lg shadow border border-gray-200 mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Laporan Arus Kas</h3>
        <p class="text-sm text-gray-600 mt-1">Periode: {{ \Carbon\Carbon::parse($startDate)->locale('id')->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->locale('id')->translatedFormat('d F Y') }}</p>
    </div>
    <div class="p-6">
        <table class="w-full">
            <tbody class="space-y-2">
                <tr>
                    <td class="py-2 text-gray-700 font-semibold">Arus Kas dari Aktivitas Operasi</td>
                    <td class="py-2 text-right"></td>
                </tr>
                <tr>
                    <td class="py-2 pl-4 text-gray-700">Kas Masuk dari Penjualan</td>
                    <td class="py-2 text-right text-green-600 font-medium">Rp {{ number_format($kasDariPenjualan, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="py-2 pl-4 text-gray-700">Kas Keluar untuk Pembelian</td>
                    <td class="py-2 text-right text-red-600">(Rp {{ number_format($kasUntukPembelian, 0, ',', '.') }})</td>
                </tr>
                @if($kasMasukManual > 0 || $kasKeluarManual > 0)
                <tr>
                    <td class="py-2 pl-4 text-gray-700">Kas Masuk Manual</td>
                    <td class="py-2 text-right text-green-600">Rp {{ number_format($kasMasukManual, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="py-2 pl-4 text-gray-700">Kas Keluar Manual</td>
                    <td class="py-2 text-right text-red-600">(Rp {{ number_format($kasKeluarManual, 0, ',', '.') }})</td>
                </tr>
                @endif
                <tr class="border-t-2 border-gray-400">
                    <td class="py-3 text-lg font-bold text-gray-900">Arus Kas Bersih dari Operasi</td>
                    <td class="py-3 text-right text-lg font-bold {{ $arusKasBersih >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($arusKasBersih, 0, ',', '.') }}
                    </td>
                </tr>
                <tr class="border-t border-gray-200">
                    <td class="py-2 text-gray-700">Total Kas Masuk</td>
                    <td class="py-2 text-right text-green-600 font-medium">Rp {{ number_format($kasMasukOperasi, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="py-2 text-gray-700">Total Kas Keluar</td>
                    <td class="py-2 text-right text-red-600 font-medium">Rp {{ number_format($kasKeluarOperasi, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
