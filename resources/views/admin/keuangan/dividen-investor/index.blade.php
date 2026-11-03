@extends('admin.keuangan.layout')

@section('title', 'Dividen Investor - Keuangan')

@section('keuangan_content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Manajemen Dividen Investor</h2>
    <p class="text-gray-600 mt-1">Hitung alokasi periode, antrean pembayaran, dan pencatatan payout ke kas keluar.</p>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6"><p class="text-green-700">{{ session('success') }}</p></div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6"><p class="text-red-700">{{ session('error') }}</p></div>
@endif
@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
    <ul class="list-disc list-inside text-red-700 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg border border-amber-200 p-4 shadow-sm">
        <div class="text-xs uppercase tracking-wide text-amber-700 font-semibold">Menunggu Bayar</div>
        <div class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($totalPending, 0, ',', '.') }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ $pendingQueue->count() }} antrean aktif</div>
    </div>
    <div class="bg-white rounded-lg border border-green-200 p-4 shadow-sm">
        <div class="text-xs uppercase tracking-wide text-green-700 font-semibold">Dibayar Bulan Ini</div>
        <div class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($paidThisMonth, 0, ',', '.') }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ now()->translatedFormat('F Y') }}</div>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
        <div class="text-xs uppercase tracking-wide text-gray-600 font-semibold">Total Sudah Dibayar</div>
        <div class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
        <div class="text-xs text-gray-500 mt-1">Semua periode</div>
    </div>
</div>

<div class="bg-white rounded-lg shadow border p-6 mb-6">
    <h3 class="font-semibold text-gray-800 mb-4">Tambah Periode Dividen</h3>
    <form method="POST" action="{{ route('admin.keuangan.dividen-investor.store') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
        @csrf
        <input type="text" name="period_label" value="{{ old('period_label') }}" placeholder="Label (mis. Q1 2026)" required class="px-3 py-2 border rounded-md">
        <input type="date" name="start_date" value="{{ old('start_date') }}" required class="px-3 py-2 border rounded-md">
        <input type="date" name="end_date" value="{{ old('end_date') }}" required class="px-3 py-2 border rounded-md">
        <input type="number" step="1" name="profit_base_amount" value="{{ old('profit_base_amount') }}" placeholder="Dasar laba (Rp)" required class="px-3 py-2 border rounded-md">
        <button class="px-4 py-2 bg-purple-600 text-white rounded-md font-semibold">Simpan Periode</button>
        <textarea name="notes" rows="2" placeholder="Catatan" class="md:col-span-5 px-3 py-2 border rounded-md">{{ old('notes') }}</textarea>
    </form>
</div>

<div class="flex gap-2 mb-4">
    <a href="{{ route('admin.keuangan.dividen-investor.index', ['tab' => 'periods']) }}"
       class="px-3 py-1.5 rounded-md text-sm font-medium {{ $filter !== 'queue' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700' }}">Periode</a>
    <a href="{{ route('admin.keuangan.dividen-investor.index', ['tab' => 'queue']) }}"
       class="px-3 py-1.5 rounded-md text-sm font-medium {{ $filter === 'queue' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700' }}">
        Antrean Pembayaran
        @if($pendingQueue->count())
        <span class="ml-1 px-1.5 py-0.5 text-xs rounded-full bg-amber-100 text-amber-800">{{ $pendingQueue->count() }}</span>
        @endif
    </a>
</div>

@if($filter === 'queue')
<div class="bg-white rounded-lg shadow border overflow-hidden">
    <div class="px-4 py-3 border-b font-semibold text-gray-800">Antrean Pembayaran Dividen</div>
    <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Investor</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rekening Tujuan</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Nominal</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Bayar</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($pendingQueue as $alloc)
            <tr>
                <td class="px-4 py-3 text-sm">
                    <a href="{{ route('admin.keuangan.dividen-investor.show', $alloc->period) }}" class="text-purple-700 font-medium">{{ $alloc->period?->period_label }}</a>
                </td>
                <td class="px-4 py-3 text-sm">
                    <div class="font-medium">{{ $alloc->investor?->name }}</div>
                    <div class="text-xs text-gray-500">{{ $alloc->capitalInjection?->invoice_number }}</div>
                </td>
                <td class="px-4 py-3 text-sm">
                    @if($alloc->investor?->hasBankAccount())
                    <div class="font-medium">{{ $alloc->investor->bank_account }}</div>
                    <div class="text-xs text-gray-500">{{ $alloc->investor->bank_name }}@if($alloc->investor->bank_account_name) · a/n {{ $alloc->investor->bank_account_name }}@endif</div>
                    @else
                    <span class="text-amber-600 text-xs">Rekening belum diisi</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-right font-medium">Rp {{ number_format($alloc->amount, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-right text-sm">
                    <form method="POST" action="{{ route('admin.keuangan.dividen-investor.allocations.pay', $alloc) }}" class="inline-flex flex-wrap gap-1 justify-end items-center" onsubmit="return confirm('Catat pembayaran dividen ini?')">
                        @csrf
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="px-2 py-1 border rounded text-xs">
                        <input type="text" name="payment_reference" placeholder="Ref transfer" class="px-2 py-1 border rounded text-xs w-28">
                        <button class="px-2 py-1 bg-green-600 text-white rounded text-xs font-semibold">Bayar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Tidak ada pembayaran pending.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@else
<div class="bg-white rounded-lg shadow border overflow-hidden">
    <div class="px-4 py-3 border-b font-semibold text-gray-800">Daftar Periode</div>
    <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rentang</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Dasar Laba</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pending</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Dibayar</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($periods as $p)
            <tr>
                <td class="px-4 py-3 text-sm font-medium">{{ $p->period_label }}</td>
                <td class="px-4 py-3 text-sm">{{ $p->start_date?->format('d/m/Y') }} – {{ $p->end_date?->format('d/m/Y') }}</td>
                <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($p->profit_base_amount, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-sm text-right">
                    <div>Rp {{ number_format($p->pending_amount ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500">{{ $p->pending_count }} item</div>
                </td>
                <td class="px-4 py-3 text-sm text-right">
                    <div>Rp {{ number_format($p->paid_amount ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500">{{ $p->paid_count }} item</div>
                </td>
                <td class="px-4 py-3 text-sm">
                    @php
                        $badge = match($p->status) {
                            'paid' => 'bg-green-100 text-green-800',
                            'calculated' => 'bg-blue-100 text-blue-800',
                            'draft' => 'bg-gray-100 text-gray-700',
                            default => 'bg-gray-100 text-gray-700',
                        };
                    @endphp
                    <span class="px-2 py-0.5 text-xs rounded-full {{ $badge }}">{{ $p->statusLabel() }}</span>
                </td>
                <td class="px-4 py-3 text-right text-sm">
                    <a href="{{ route('admin.keuangan.dividen-investor.show', $p) }}" class="text-purple-600 font-medium">Kelola Bayar</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada periode dividen.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endif
@endsection
