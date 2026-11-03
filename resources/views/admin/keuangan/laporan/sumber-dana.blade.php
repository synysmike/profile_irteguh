@extends('admin.keuangan.layout')

@section('title', 'Laporan Sumber Dana - Keuangan')

@section('keuangan_content')
<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Laporan Sumber Dana</h2>
        <p class="text-gray-600 mt-1">Rekap penerimaan dan pemakaian dana per klasifikasi.</p>
    </div>
    <a href="{{ route('admin.keuangan.sumber-dana.index') }}" class="px-3 py-2 bg-white border border-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-50">
        Kelola Master →
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
        <div class="text-sm text-gray-500 mb-1">Total Masuk</div>
        <div class="text-xl font-bold text-green-600">Rp {{ number_format($kasMasuk, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
        <div class="text-sm text-gray-500 mb-1">Total Keluar</div>
        <div class="text-xl font-bold text-red-600">Rp {{ number_format($kasKeluar, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
        <div class="text-sm text-gray-500 mb-1">Netto</div>
        <div class="text-xl font-bold {{ $arusKasBersih >= 0 ? 'text-green-700' : 'text-red-700' }}">
            Rp {{ number_format($arusKasBersih, 0, ',', '.') }}
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('admin.keuangan.laporan.sumber-dana') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
        <div class="md:col-span-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Keterangan, kode/nama sumber…"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
            <input type="date" name="start_date" value="{{ $startDate }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
            <input type="date" name="end_date" value="{{ $endDate }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">
        </div>
        <div class="md:col-span-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Sumber Dana</label>
            <select name="sumber_dana_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">
                <option value="">Semua</option>
                @foreach($sumberDanas as $sd)
                <option value="{{ $sd->id }}" {{ (string) $sumberDanaId === (string) $sd->id ? 'selected' : '' }}>
                    {{ $sd->code }} — {{ $sd->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2 flex gap-2">
            <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition text-sm font-semibold">Terapkan</button>
            <a href="{{ route('admin.keuangan.laporan.sumber-dana') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition text-sm font-medium">Reset</a>
        </div>
    </form>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-200">
        <h3 class="font-semibold text-gray-800">Ringkasan per Klasifikasi</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sumber Dana</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah Tx</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Masuk</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Keluar</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Netto</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($ringkasan as $row)
                <tr>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['code'] }}</td>
                    <td class="px-4 py-3 text-sm text-gray-800">{{ $row['name'] }}</td>
                    <td class="px-4 py-3 text-sm text-right text-gray-600">{{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-right text-green-600">Rp {{ number_format($row['total_masuk'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-right text-red-600">Rp {{ number_format($row['total_keluar'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-right font-semibold {{ $row['netto'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                        Rp {{ number_format($row['netto'], 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500 text-sm">Tidak ada data untuk filter ini.</td>
                </tr>
                @endforelse
            </tbody>
            @if($ringkasan->isNotEmpty())
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="3" class="px-4 py-3 text-sm font-semibold text-gray-800 text-right">Total</td>
                    <td class="px-4 py-3 text-sm text-right font-semibold text-green-700">Rp {{ number_format($kasMasuk, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-right font-semibold text-red-700">Rp {{ number_format($kasKeluar, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-right font-semibold {{ $arusKasBersih >= 0 ? 'text-green-700' : 'text-red-700' }}">
                        Rp {{ number_format($arusKasBersih, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
        <h3 class="font-semibold text-gray-800">Detail Transaksi</h3>
        <span class="text-xs text-gray-500">
            Menampilkan {{ $transactions->firstItem() ?? 0 }}–{{ $transactions->lastItem() ?? 0 }}
            dari {{ number_format($transactions->total(), 0, ',', '.') }} baris
        </span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sumber Dana</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Relasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($transactions as $trans)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $trans->transaction_date?->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        @if($trans->sumberDana)
                        <div class="font-medium">{{ $trans->sumberDana->name }}</div>
                        <div class="text-xs text-gray-400">{{ $trans->sumberDana->code }}</div>
                        @else
                        <span class="text-gray-400">Belum diklasifikasi</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($trans->transaction_type === 'debit')
                        <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">Masuk</span>
                        @else
                        <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800">Keluar</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-right font-semibold whitespace-nowrap {{ $trans->transaction_type === 'debit' ? 'text-green-600' : 'text-red-600' }}">
                        {{ $trans->transaction_type === 'debit' ? '+' : '-' }}Rp {{ number_format($trans->amount, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $trans->description ?: '—' }}</td>
                    <td class="px-4 py-3 text-sm">
                        @if($trans->sale)
                        <span class="text-purple-700">Penjualan: {{ $trans->sale->invoice_number }}</span>
                        @elseif($trans->purchase)
                        <span class="text-blue-700">Grosir: {{ $trans->purchase->invoice_number }}</span>
                        @elseif($trans->project)
                        <span class="text-indigo-700">Project: {{ $trans->project->code }}</span>
                        @else
                        <span class="text-gray-400">Manual</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-500 text-sm">Tidak ada transaksi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
    <div class="px-5 py-4 border-t border-gray-200">
        {{ $transactions->links() }}
    </div>
    @endif
</div>
@endsection
