@extends('admin.keuangan.layout')

@section('title', 'Arus Kas - Keuangan')

@section('keuangan_content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Arus Kas</h2>
    <p class="text-gray-600 mt-1">Seluruh transaksi kas/bank — cari, filter tanggal, dan lihat arus masuk/keluar.</p>
</div>

{{-- Ringkasan sesuai filter --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
        <div class="text-sm text-gray-500 mb-1">Jumlah Transaksi</div>
        <div class="text-xl font-bold text-gray-800">{{ number_format($jumlahTransaksi, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
        <div class="text-sm text-gray-500 mb-1">Kas Masuk</div>
        <div class="text-xl font-bold text-green-600">Rp {{ number_format($kasMasuk, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
        <div class="text-sm text-gray-500 mb-1">Kas Keluar</div>
        <div class="text-xl font-bold text-red-600">Rp {{ number_format($kasKeluar, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
        <div class="text-sm text-gray-500 mb-1">Arus Kas Bersih</div>
        <div class="text-xl font-bold {{ $arusKasBersih >= 0 ? 'text-green-700' : 'text-red-700' }}">
            Rp {{ number_format($arusKasBersih, 0, ',', '.') }}
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="bg-white rounded-lg shadow border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('admin.keuangan.laporan.arus-kas') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
        <div class="md:col-span-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Keterangan, referensi, akun, no. faktur, project…"
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
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis</label>
            <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">
                <option value="">Semua</option>
                <option value="debit" {{ $type === 'debit' ? 'selected' : '' }}>Penerimaan</option>
                <option value="credit" {{ $type === 'credit' ? 'selected' : '' }}>Pengeluaran</option>
            </select>
        </div>
        <div class="md:col-span-2 flex gap-2">
            <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition text-sm font-semibold">
                Terapkan
            </button>
            <a href="{{ route('admin.keuangan.laporan.arus-kas') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition text-sm font-medium">
                Reset
            </a>
        </div>
    </form>
    @if($startDate || $endDate || $search || $type)
    <p class="text-xs text-gray-500 mt-3">
        Filter aktif:
        @if($startDate || $endDate)
            periode
            {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : '…' }}
            –
            {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : '…' }}
        @endif
        @if($search) · pencarian “{{ $search }}” @endif
        @if($type) · {{ $type === 'debit' ? 'penerimaan' : 'pengeluaran' }} @endif
    </p>
    @else
    <p class="text-xs text-gray-500 mt-3">Menampilkan seluruh transaksi kas/bank (tanpa batas periode).</p>
    @endif
</div>

{{-- Datatable --}}
<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
        <h3 class="font-semibold text-gray-800">Daftar Transaksi</h3>
        <span class="text-xs text-gray-500">
            Menampilkan {{ $transactions->firstItem() ?? 0 }}–{{ $transactions->lastItem() ?? 0 }}
            dari {{ number_format($transactions->total(), 0, ',', '.') }} baris
        </span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="arusKasTable">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Akun</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referensi</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sumber</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($transactions as $trans)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                        {{ $trans->transaction_date?->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($trans->transaction_type === 'debit')
                        <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">Penerimaan</span>
                        @else
                        <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800">Pengeluaran</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        @if($trans->chartOfAccount)
                        <span class="font-medium text-gray-800">{{ $trans->chartOfAccount->code }}</span>
                        — {{ $trans->chartOfAccount->name }}
                        @else
                        —
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-right font-semibold whitespace-nowrap {{ $trans->transaction_type === 'debit' ? 'text-green-600' : 'text-red-600' }}">
                        {{ $trans->transaction_type === 'debit' ? '+' : '-' }}Rp {{ number_format($trans->amount, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 max-w-xs">
                        {{ $trans->description ?: '—' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">
                        {{ $trans->reference ?: '—' }}
                    </td>
                    <td class="px-4 py-3 text-sm">
                        @if($trans->sale)
                        <a href="{{ route('admin.keuangan.transaksi.penjualan') }}" class="text-purple-600 hover:text-purple-800">
                            Penjualan: {{ $trans->sale->invoice_number }}
                        </a>
                        @elseif($trans->purchase)
                        <a href="{{ route('admin.keuangan.transaksi.pembelian') }}" class="text-blue-600 hover:text-blue-800">
                            Grosir: {{ $trans->purchase->invoice_number }}
                        </a>
                        @elseif($trans->project)
                        <a href="{{ route('admin.projects.show', $trans->project_id) }}" class="text-indigo-600 hover:text-indigo-800">
                            Project: {{ $trans->project->code }}
                        </a>
                        @else
                        <span class="text-gray-400">Manual</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-500 text-sm">
                        Tidak ada transaksi yang cocok dengan filter.
                        <a href="{{ route('admin.keuangan.laporan.arus-kas') }}" class="text-purple-600 hover:text-purple-800 font-medium ml-1">Reset filter</a>
                        atau
                        <a href="{{ route('admin.keuangan.transaksi.kas-bank') }}" class="text-purple-600 hover:text-purple-800 font-medium">buka Kas/Bank</a>.
                    </td>
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
