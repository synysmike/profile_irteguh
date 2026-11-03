@extends('admin.keuangan.layout')

@section('title', 'Pembayaran Dividen - Keuangan')

@section('keuangan_content')
<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">{{ $period->period_label }}</h2>
        <p class="text-gray-600 mt-1">
            {{ $period->start_date?->format('d/m/Y') }} – {{ $period->end_date?->format('d/m/Y') }}
            · Dasar laba Rp {{ number_format($period->profit_base_amount, 0, ',', '.') }}
            · {{ $period->statusLabel() }}
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.keuangan.dividen-investor.index') }}" class="px-3 py-2 border rounded-md text-sm">← Kembali</a>
        @if($period->status !== 'paid' && $period->status !== 'cancelled')
        <form method="POST" action="{{ route('admin.keuangan.dividen-investor.calculate', $period) }}">
            @csrf
            <button class="px-3 py-2 bg-purple-600 text-white rounded-md text-sm font-semibold">Hitung / Hitung Ulang Alokasi</button>
        </form>
        @endif
    </div>
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

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border rounded-lg p-4">
        <div class="text-xs text-gray-500 uppercase font-semibold">Total Alokasi</div>
        <div class="text-xl font-bold mt-1">Rp {{ number_format($summary['total'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-white border border-amber-200 rounded-lg p-4">
        <div class="text-xs text-amber-700 uppercase font-semibold">Menunggu Bayar</div>
        <div class="text-xl font-bold mt-1">Rp {{ number_format($summary['pending'], 0, ',', '.') }}</div>
        <div class="text-xs text-gray-500">{{ $summary['pending_count'] }} item</div>
    </div>
    <div class="bg-white border border-green-200 rounded-lg p-4">
        <div class="text-xs text-green-700 uppercase font-semibold">Sudah Dibayar</div>
        <div class="text-xl font-bold mt-1">Rp {{ number_format($summary['paid'], 0, ',', '.') }}</div>
        <div class="text-xs text-gray-500">{{ $summary['paid_count'] }} item</div>
    </div>
    <div class="bg-white border rounded-lg p-4">
        <div class="text-xs text-gray-500 uppercase font-semibold">Rekening Kosong</div>
        <div class="text-xl font-bold mt-1 {{ $summary['missing_bank'] ? 'text-amber-600' : 'text-gray-900' }}">{{ $summary['missing_bank'] }}</div>
        <div class="text-xs text-gray-500">investor pending tanpa no. rekening</div>
    </div>
</div>

@if($summary['pending_count'] > 0)
<div class="bg-white rounded-lg shadow border p-5 mb-6">
    <h3 class="font-semibold text-gray-800 mb-3">Bayar Semua Pending</h3>
    <form method="POST" action="{{ route('admin.keuangan.dividen-investor.pay-all', $period) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end" onsubmit="return confirm('Bayar semua alokasi pending di periode ini? Kas keluar akan dicatat.')">
        @csrf
        <div>
            <label class="block text-xs text-gray-500 mb-1">Tanggal Bayar</label>
            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border rounded-md">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Referensi (opsional)</label>
            <input type="text" name="payment_reference" placeholder="Batch / no. transfer" class="w-full px-3 py-2 border rounded-md">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Catatan</label>
            <input type="text" name="payment_note" placeholder="Catatan payout" class="w-full px-3 py-2 border rounded-md">
        </div>
        <button class="px-4 py-2 bg-green-600 text-white rounded-md font-semibold">Bayar Semua ({{ $summary['pending_count'] }})</button>
    </form>
</div>
@endif

<div class="flex gap-2 mb-4">
    <a href="{{ route('admin.keuangan.dividen-investor.show', [$period, 'tab' => 'all']) }}"
       class="px-3 py-1.5 rounded-md text-sm font-medium {{ $tab === 'all' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700' }}">Semua</a>
    <a href="{{ route('admin.keuangan.dividen-investor.show', [$period, 'tab' => 'pending']) }}"
       class="px-3 py-1.5 rounded-md text-sm font-medium {{ $tab === 'pending' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700' }}">Menunggu Bayar</a>
    <a href="{{ route('admin.keuangan.dividen-investor.show', [$period, 'tab' => 'paid']) }}"
       class="px-3 py-1.5 rounded-md text-sm font-medium {{ $tab === 'paid' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700' }}">Sudah Dibayar</a>
</div>

<div class="bg-white rounded-lg shadow border overflow-hidden">
    <div class="overflow-x-auto">
    <table class="min-w-full divide-y">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Investor</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rekening Tujuan</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Setoran</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">%</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Nominal</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pembayaran</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($allocations as $alloc)
            <tr>
                <td class="px-4 py-3 text-sm">
                    <div class="font-medium">{{ $alloc->investor?->name }}</div>
                    <div class="text-xs text-gray-500">{{ $alloc->investor?->phone }}</div>
                </td>
                <td class="px-4 py-3 text-sm">
                    @if($alloc->investor?->hasBankAccount())
                    <div class="font-medium">{{ $alloc->investor->bank_account }}</div>
                    <div class="text-xs text-gray-500">
                        {{ $alloc->investor->bank_name ?: '—' }}
                        @if($alloc->investor->bank_account_name) · a/n {{ $alloc->investor->bank_account_name }}@endif
                    </div>
                    @else
                    <span class="text-amber-600 text-xs">Belum ada no. rekening — lengkapi di Master Investor</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm">{{ $alloc->capitalInjection?->invoice_number ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-right">{{ number_format($alloc->share_percent_snapshot, 2, ',', '.') }}%</td>
                <td class="px-4 py-3 text-sm text-right">
                    @if($alloc->status === 'pending')
                    <form method="POST" action="{{ route('admin.keuangan.dividen-investor.allocations.update', $alloc) }}" class="inline-flex items-center gap-1">
                        @csrf @method('PUT')
                        <input type="number" name="amount" value="{{ (int) $alloc->amount }}" class="w-28 px-2 py-1 border rounded text-right">
                        <button class="text-xs text-purple-600">Update</button>
                    </form>
                    @else
                    Rp {{ number_format($alloc->amount, 0, ',', '.') }}
                    @endif
                </td>
                <td class="px-4 py-3 text-sm">
                    <span class="px-2 py-0.5 text-xs rounded-full {{ $alloc->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ $alloc->statusLabel() }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right text-sm">
                    @if($alloc->status === 'pending' && $alloc->amount > 0)
                    <form method="POST" action="{{ route('admin.keuangan.dividen-investor.allocations.pay', $alloc) }}" class="space-y-1" onsubmit="return confirm('Catat pembayaran ke rekening investor?')">
                        @csrf
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="w-full px-2 py-1 border rounded text-xs">
                        <input type="text" name="payment_reference" placeholder="No. ref transfer" class="w-full px-2 py-1 border rounded text-xs">
                        <input type="text" name="payment_note" placeholder="Catatan" class="w-full px-2 py-1 border rounded text-xs">
                        <button class="w-full px-2 py-1.5 bg-green-600 text-white rounded text-xs font-semibold">Bayar & Catat Kas</button>
                    </form>
                    @elseif($alloc->status === 'paid')
                    <div class="text-xs text-gray-600 text-right">
                        <div>{{ optional($alloc->payment_date ?? $alloc->paid_at)->format('d/m/Y') }}</div>
                        @if($alloc->payment_reference)<div>Ref: {{ $alloc->payment_reference }}</div>@endif
                        @if($alloc->payment_note)<div>{{ $alloc->payment_note }}</div>@endif
                        @if($alloc->paid_at)<div class="text-gray-400">{{ $alloc->paid_at->format('H:i') }}</div>@endif
                    </div>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada alokasi. Klik “Hitung Alokasi”.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
