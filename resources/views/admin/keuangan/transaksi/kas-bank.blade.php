@extends('admin.keuangan.layout')

@section('title', 'Kas/Bank - Keuangan')

@section('keuangan_content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Kas & Bank</h2>
        <p class="text-gray-600 mt-1">Penerimaan kas (debit) dan pengeluaran kas (credit). Terkait Akun Perkiraan (COA) tipe Kas.</p>
    </div>
    <button type="button" onclick="openResourceModal('cashTransactionModal', 'cash-transactions', 'Transaksi Kas')" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition text-sm font-semibold">
        + Tambah Transaksi
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
        <div class="text-sm text-gray-500 mb-1">Total Penerimaan</div>
        <div class="text-xl font-bold text-green-600">Rp {{ number_format($cashTransactions->where('transaction_type', 'debit')->sum('amount'), 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
        <div class="text-sm text-gray-500 mb-1">Total Pengeluaran</div>
        <div class="text-xl font-bold text-red-600">Rp {{ number_format($cashTransactions->where('transaction_type', 'credit')->sum('amount'), 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
        <div class="text-sm text-gray-500 mb-1">Saldo Bersih</div>
        <div class="text-xl font-bold text-gray-800">Rp {{ number_format($cashTransactions->where('transaction_type', 'debit')->sum('amount') - $cashTransactions->where('transaction_type', 'credit')->sum('amount'), 0, ',', '.') }}</div>
    </div>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Akun</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah (Rp)</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sumber</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dokumen</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($cashTransactions as $trans)
            <tr id="cash-transactionsRow_{{ $trans->id }}">
                <td class="px-4 py-3 text-sm text-gray-900">{{ $trans->transaction_date?->format('d/m/Y') }}</td>
                <td class="px-4 py-3">
                    @if($trans->transaction_type === 'debit')
                    <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">Penerimaan</span>
                    @else
                    <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800">Pengeluaran</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $trans->chartOfAccount?->code ?? '—' }} - {{ $trans->chartOfAccount?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-right font-medium {{ $trans->transaction_type === 'debit' ? 'text-green-600' : 'text-red-600' }}">
                    {{ $trans->transaction_type === 'debit' ? '+' : '-' }}Rp {{ number_format($trans->amount, 0, ',', '.') }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ Str::limit($trans->description ?? '—', 40) }}</td>
                <td class="px-4 py-3 text-sm">
                    @if($trans->sale)
                    <a href="{{ route('admin.keuangan.transaksi.penjualan') }}" class="text-purple-600 hover:text-purple-800 inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Penjualan: {{ $trans->sale->invoice_number }}
                    </a>
                    @elseif($trans->purchase)
                    <a href="{{ route('admin.keuangan.transaksi.pembelian') }}" class="text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Pembelian: {{ $trans->purchase->invoice_number }}
                    </a>
                    @elseif($trans->project)
                    <a href="{{ route('admin.projects.show', $trans->project_id) }}" class="text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1">
                        Project: {{ $trans->project->code }}
                    </a>
                    @else
                    <span class="text-gray-400">Manual</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm">
                    @if($trans->document_path)
                    <a href="{{ route('admin.cash-transactions.download', $trans->id) }}" target="_blank" class="text-purple-600 hover:text-purple-800 inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Lihat
                    </a>
                    @else
                    <span class="text-gray-400">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right text-sm">
                    <button type="button" onclick="openResourceModal('cashTransactionModal', 'cash-transactions', 'Transaksi Kas', {{ $trans->id }})" class="text-purple-600 hover:text-purple-800 mr-3">Edit</button>
                    <button type="button" onclick="deleteResource('cash-transactions', {{ $trans->id }}, 'Transaksi Kas')" class="text-red-600 hover:text-red-800">Hapus</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                    Belum ada transaksi kas.
                    <button type="button" onclick="openResourceModal('cashTransactionModal', 'cash-transactions', 'Transaksi Kas')" class="text-purple-600 hover:text-purple-800 ml-1">Tambah yang pertama</button>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@include('admin.components.modal', ['modalId' => 'cashTransactionModal', 'title' => 'Tambah Transaksi Kas'])
@endsection
