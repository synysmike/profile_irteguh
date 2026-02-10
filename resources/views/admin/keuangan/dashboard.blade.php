@extends('admin.keuangan.layout')

@section('title', 'Dashboard Keuangan - Admin')

@section('keuangan_content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Dashboard Keuangan</h2>
    <p class="text-gray-600 mt-1">Ringkasan kas, omzet bulanan, dan status pajak</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="text-sm text-gray-500 mb-1">Kas Masuk (Bulan Ini)</div>
        <div class="text-2xl font-bold text-green-600">Rp {{ number_format($stats['kas_masuk_bulan'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="text-sm text-gray-500 mb-1">Kas Keluar (Bulan Ini)</div>
        <div class="text-2xl font-bold text-red-600">Rp {{ number_format($stats['kas_keluar_bulan'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="text-sm text-gray-500 mb-1">Omzet Bulanan</div>
        <div class="text-2xl font-bold text-gray-800">Rp {{ number_format($stats['omzet_bulan'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="text-sm text-gray-500 mb-1">Status Pajak</div>
        <div class="text-sm text-gray-600 mt-1">PPh: {{ $stats['pph_status'] ?? 'Belum dihitung' }}</div>
        <div class="text-sm text-gray-600">PPN: {{ $stats['ppn_status'] ?? 'Belum dihitung' }}</div>
    </div>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-6">
    <h3 class="font-semibold text-gray-800 mb-4">Grafik Omzet & Arus Kas 6 Bulan Terakhir</h3>
    @if(count($grafikBulanan) > 0 && (collect($grafikBulanan)->sum('omzet') > 0 || collect($grafikBulanan)->sum('arus_kas_bersih') != 0))
    @php
        $maxOmzet = collect($grafikBulanan)->max('omzet');
        $maxArusKas = max(abs(collect($grafikBulanan)->min('arus_kas_bersih')), abs(collect($grafikBulanan)->max('arus_kas_bersih')));
        $maxValue = max($maxOmzet, $maxArusKas);
        $maxValue = $maxValue > 0 ? $maxValue : 1;
    @endphp
    <div class="h-80 flex items-end justify-between gap-2 relative border-l-2 border-b-2 border-gray-300 pl-4 pb-4">
        @foreach($grafikBulanan as $data)
        @php
            $omzetPercent = $maxValue > 0 ? ($data['omzet'] / $maxValue) * 100 : 0;
            $arusKasPercent = $maxValue > 0 ? (abs($data['arus_kas_bersih']) / $maxValue) * 100 : 0;
        @endphp
        <div class="flex-1 flex flex-col items-center gap-1 h-full relative group">
            <!-- Container untuk kedua bar -->
            <div class="w-full flex items-end gap-1 justify-center" style="height: 100%;">
                <!-- Omzet bar (kiri) -->
                <div class="flex-1 relative max-w-[48%]" style="height: {{ $omzetPercent }}%">
                    <div class="w-full bg-purple-500 rounded-t relative h-full hover:bg-purple-600 transition-colors">
                        <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 text-xs text-gray-700 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity bg-white px-2 py-1 rounded shadow-lg z-20 border border-gray-200 pointer-events-none">
                            <div class="font-semibold text-purple-600">Omzet</div>
                            <div>Rp {{ number_format($data['omzet'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Arus Kas bar (kanan) -->
                <div class="flex-1 relative max-w-[48%]" style="height: {{ $arusKasPercent }}%">
                    @if($data['arus_kas_bersih'] >= 0)
                    <div class="w-full bg-green-500 rounded-t relative h-full hover:bg-green-600 transition-colors">
                        <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 text-xs text-gray-700 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity bg-white px-2 py-1 rounded shadow-lg z-20 border border-gray-200 pointer-events-none">
                            <div class="font-semibold text-green-600">Arus Kas</div>
                            <div class="text-green-600">+Rp {{ number_format($data['arus_kas_bersih'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                    @else
                    <div class="w-full bg-red-500 rounded-t relative h-full hover:bg-red-600 transition-colors">
                        <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 text-xs text-gray-700 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity bg-white px-2 py-1 rounded shadow-lg z-20 border border-gray-200 pointer-events-none">
                            <div class="font-semibold text-red-600">Arus Kas</div>
                            <div class="text-red-600">-Rp {{ number_format(abs($data['arus_kas_bersih']), 0, ',', '.') }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Month label -->
            <div class="mt-2 text-xs text-gray-600 text-center font-medium">{{ $data['bulan'] }}</div>
        </div>
        @endforeach
    </div>
    
    <!-- Legend -->
    <div class="mt-4 flex items-center justify-center gap-6 text-sm">
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-purple-500 rounded"></div>
            <span class="text-gray-700">Omzet Penjualan</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-green-500 rounded"></div>
            <span class="text-gray-700">Arus Kas Positif</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-red-500 rounded"></div>
            <span class="text-gray-700">Arus Kas Negatif</span>
        </div>
    </div>
    @else
    <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg text-gray-400 text-sm">
        Grafik akan tampil setelah ada data transaksi penjualan atau kas.
    </div>
    @endif
</div>

<div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
    <strong>Alur:</strong> Input transaksi (penjualan, pembelian, kas) lalu posting ke Jurnal. Laporan Keuangan terbentuk. Menu Pajak menarik data dari laporan. Hasil untuk setor dan lapor pajak CV.
</div>
@endsection
