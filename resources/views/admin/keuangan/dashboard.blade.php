@extends('admin.keuangan.layout')

@section('title', 'Dashboard Keuangan - Admin')

@section('keuangan_content')
<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Dashboard Keuangan</h2>
        <p class="text-gray-600 mt-1">
            Ringkasan diselaraskan dengan daftar faktur penjualan, grosir, dan kas/bank.
            Periode aktif: <span class="font-medium text-gray-800">{{ $stats['periode_label'] }}</span>
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.keuangan.transaksi.penjualan') }}" class="px-3 py-2 bg-purple-600 text-white rounded-md text-sm font-semibold hover:bg-purple-700">Kasir / Faktur</a>
        <a href="{{ route('admin.keuangan.transaksi.pembelian') }}" class="px-3 py-2 bg-white border border-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-50">Grosir</a>
        <a href="{{ route('admin.keuangan.transaksi.kas-bank') }}" class="px-3 py-2 bg-white border border-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-50">Kas/Bank</a>
    </div>
</div>

{{-- Bulan ini --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="text-sm text-gray-500 mb-1">Omzet DPP (bulan ini)</div>
        <div class="text-2xl font-bold text-gray-800">Rp {{ number_format($stats['omzet_bulan'], 0, ',', '.') }}</div>
        <div class="text-xs text-gray-500 mt-1">Σ subtotal faktur penjualan</div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="text-sm text-gray-500 mb-1">Total Faktur (bulan ini)</div>
        <div class="text-2xl font-bold text-purple-700">Rp {{ number_format($stats['total_faktur_bulan'], 0, ',', '.') }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ $stats['jumlah_faktur_bulan'] }} faktur · {{ $stats['faktur_terkait_kas_bulan'] }} terkait kas</div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="text-sm text-gray-500 mb-1">Kas Masuk (bulan ini)</div>
        <div class="text-2xl font-bold text-green-600">Rp {{ number_format($stats['kas_masuk_bulan'], 0, ',', '.') }}</div>
        <div class="text-xs text-gray-500 mt-1">Dari transaksi kas/bank</div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="text-sm text-gray-500 mb-1">Kas Keluar (bulan ini)</div>
        <div class="text-2xl font-bold text-red-600">Rp {{ number_format($stats['kas_keluar_bulan'], 0, ',', '.') }}</div>
        <div class="text-xs text-gray-500 mt-1">
            Arus bersih:
            <span class="{{ $stats['arus_kas_bersih_bulan'] >= 0 ? 'text-green-700' : 'text-red-700' }} font-medium">
                Rp {{ number_format($stats['arus_kas_bersih_bulan'], 0, ',', '.') }}
            </span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="text-sm font-semibold text-gray-800 mb-3">Grosir (bulan ini)</div>
        <div class="space-y-1 text-sm">
            <div class="flex justify-between"><span class="text-gray-600">Jumlah PO</span><span class="font-medium">{{ $stats['jumlah_grosir_bulan'] }}</span></div>
            <div class="flex justify-between"><span class="text-gray-600">DPP</span><span>Rp {{ number_format($stats['grosir_bulan_subtotal'], 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span class="text-gray-600">Total</span><span class="font-semibold">Rp {{ number_format($stats['grosir_bulan_total'], 0, ',', '.') }}</span></div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="text-sm font-semibold text-gray-800 mb-3">Pajak dari Faktur (bulan ini)</div>
        <div class="space-y-1 text-sm">
            <div class="flex justify-between"><span class="text-gray-600">Tambahan (PPN-like)</span><span>Rp {{ number_format($stats['pajak_tambahan_bulan'], 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span class="text-gray-600">Potongan (PPh-like)</span><span>Rp {{ number_format($stats['pajak_potongan_bulan'], 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span class="text-gray-600">Pajak masukan grosir</span><span>Rp {{ number_format($stats['pajak_masukan_bulan'], 0, ',', '.') }}</span></div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="text-sm font-semibold text-gray-800 mb-3">Seluruh Periode</div>
        <div class="space-y-1 text-sm">
            <div class="flex justify-between"><span class="text-gray-600">Faktur penjualan</span><span class="font-medium">{{ $stats['jumlah_faktur_semua'] }}</span></div>
            <div class="flex justify-between"><span class="text-gray-600">Omzet DPP</span><span>Rp {{ number_format($stats['omzet_semua'], 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span class="text-gray-600">Total faktur</span><span class="font-semibold">Rp {{ number_format($stats['total_faktur_semua'], 0, ',', '.') }}</span></div>
            <div class="flex justify-between border-t pt-1 mt-1"><span class="text-gray-600">Kas bersih</span>
                <span class="font-semibold {{ $stats['arus_kas_bersih_semua'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                    Rp {{ number_format($stats['arus_kas_bersih_semua'], 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- Grafik --}}
<div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-6">
    <h3 class="font-semibold text-gray-800 mb-4">Grafik Omzet & Arus Kas 6 Bulan Terakhir</h3>
    @if(count($grafikBulanan) > 0 && (collect($grafikBulanan)->sum('omzet') > 0 || collect($grafikBulanan)->sum('arus_kas_bersih') != 0))
    @php
        $maxOmzet = collect($grafikBulanan)->max('omzet');
        $maxArusKas = max(abs(collect($grafikBulanan)->min('arus_kas_bersih')), abs(collect($grafikBulanan)->max('arus_kas_bersih')));
        $maxValue = max($maxOmzet, $maxArusKas, 1);
    @endphp
    <div class="h-80 flex items-end justify-between gap-2 relative border-l-2 border-b-2 border-gray-300 pl-4 pb-4">
        @foreach($grafikBulanan as $data)
        @php
            $omzetPercent = ($data['omzet'] / $maxValue) * 100;
            $arusKasPercent = (abs($data['arus_kas_bersih']) / $maxValue) * 100;
        @endphp
        <div class="flex-1 flex flex-col items-center gap-1 h-full relative group">
            <div class="w-full flex items-end gap-1 justify-center" style="height: 100%;">
                <div class="flex-1 relative max-w-[48%]" style="height: {{ max($omzetPercent, 0) }}%">
                    <div class="w-full bg-purple-500 rounded-t relative h-full hover:bg-purple-600 transition-colors min-h-[2px]">
                        <div class="absolute -top-12 left-1/2 transform -translate-x-1/2 text-xs text-gray-700 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity bg-white px-2 py-1 rounded shadow-lg z-20 border border-gray-200 pointer-events-none">
                            <div class="font-semibold text-purple-600">Omzet DPP</div>
                            <div>Rp {{ number_format($data['omzet'], 0, ',', '.') }}</div>
                            <div class="text-gray-500">{{ $data['jumlah_faktur'] }} faktur</div>
                        </div>
                    </div>
                </div>
                <div class="flex-1 relative max-w-[48%]" style="height: {{ max($arusKasPercent, 0) }}%">
                    @if($data['arus_kas_bersih'] >= 0)
                    <div class="w-full bg-green-500 rounded-t relative h-full hover:bg-green-600 transition-colors min-h-[2px]">
                        <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 text-xs text-gray-700 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity bg-white px-2 py-1 rounded shadow-lg z-20 border border-gray-200 pointer-events-none">
                            <div class="font-semibold text-green-600">Arus Kas</div>
                            <div class="text-green-600">+Rp {{ number_format($data['arus_kas_bersih'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                    @else
                    <div class="w-full bg-red-500 rounded-t relative h-full hover:bg-red-600 transition-colors min-h-[2px]">
                        <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 text-xs text-gray-700 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity bg-white px-2 py-1 rounded shadow-lg z-20 border border-gray-200 pointer-events-none">
                            <div class="font-semibold text-red-600">Arus Kas</div>
                            <div class="text-red-600">-Rp {{ number_format(abs($data['arus_kas_bersih']), 0, ',', '.') }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="mt-2 text-xs text-gray-600 text-center font-medium">{{ $data['bulan'] }}</div>
        </div>
        @endforeach
    </div>
    <div class="mt-4 flex items-center justify-center gap-6 text-sm">
        <div class="flex items-center gap-2"><div class="w-4 h-4 bg-purple-500 rounded"></div><span class="text-gray-700">Omzet DPP</span></div>
        <div class="flex items-center gap-2"><div class="w-4 h-4 bg-green-500 rounded"></div><span class="text-gray-700">Arus Kas +</span></div>
        <div class="flex items-center gap-2"><div class="w-4 h-4 bg-red-500 rounded"></div><span class="text-gray-700">Arus Kas −</span></div>
    </div>
    @else
    <div class="h-48 flex items-center justify-center bg-gray-50 rounded-lg text-gray-400 text-sm">
        Grafik akan tampil setelah ada faktur penjualan atau transaksi kas.
    </div>
    @endif
</div>

{{-- Faktur terbaru (sinkron daftar penjualan) --}}
<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
        <div>
            <h3 class="font-semibold text-gray-800">Faktur Penjualan Terbaru</h3>
            <p class="text-xs text-gray-500 mt-0.5">Sumber sama dengan Daftar Faktur di Kasir POS</p>
        </div>
        <a href="{{ route('admin.keuangan.transaksi.penjualan') }}#faktur-list" class="text-sm text-purple-700 hover:text-purple-900 font-medium">Lihat semua →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Faktur</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($recentSales as $sale)
                <tr>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $sale->invoice_number }}</td>
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
                    <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm">
                        @if($sale->cashTransaction)
                        <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">Terkait Kas</span>
                        @else
                        <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">Belum Posting</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                        <a href="{{ $sale->publicInvoiceUrl() }}" target="_blank" class="text-blue-600 hover:text-blue-800">Cetak</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500 text-sm">
                        Belum ada faktur penjualan.
                        <a href="{{ route('admin.keuangan.transaksi.penjualan') }}" class="text-purple-600 hover:text-purple-800 font-medium">Buka Kasir POS</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Grosir Terbaru</h3>
            <a href="{{ route('admin.keuangan.transaksi.pembelian') }}" class="text-sm text-purple-700 hover:text-purple-900 font-medium">Semua →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Faktur</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentPurchases as $purchase)
                    <tr>
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $purchase->invoice_number }}</td>
                        <td class="px-4 py-2 text-gray-600">{{ $purchase->purchase_date?->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 text-right">Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">Belum ada transaksi grosir.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Kas/Bank Terbaru</h3>
            <a href="{{ route('admin.keuangan.transaksi.kas-bank') }}" class="text-sm text-purple-700 hover:text-purple-900 font-medium">Semua →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentKas as $kas)
                    <tr>
                        <td class="px-4 py-2 text-gray-600">{{ $kas->transaction_date?->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 text-gray-800">
                            {{ \Illuminate\Support\Str::limit($kas->description ?: ($kas->reference ?: '—'), 40) }}
                            <span class="block text-xs text-gray-400">{{ $kas->transaction_type === 'debit' ? 'Masuk' : 'Keluar' }}</span>
                        </td>
                        <td class="px-4 py-2 text-right font-medium {{ $kas->transaction_type === 'debit' ? 'text-green-700' : 'text-red-700' }}">
                            {{ $kas->transaction_type === 'debit' ? '+' : '-' }}Rp {{ number_format($kas->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">Belum ada transaksi kas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
    <strong>Sinkronisasi:</strong> Omzet & faktur diambil dari tabel penjualan yang sama dengan Kasir POS.
    Kas masuk/keluar dari transaksi kas/bank (termasuk auto-post dari faktur & grosir).
    Ringkasan “Seluruh Periode” mencakup semua faktur di journal, bukan hanya bulan berjalan.
</div>
@endsection
