@extends('admin.keuangan.layout')

@section('title', 'Jurnal Umum - Keuangan')

@section('keuangan_content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Jurnal Umum</h2>
        <p class="text-gray-600 mt-1">Input manual transaksi non-rutin. Tiap baris memakai Akun Perkiraan (COA). Total debit = total kredit.</p>
    </div>
    <button type="button" onclick="openResourceModal('journalEntryModal', 'journal-entries', 'Jurnal')" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition text-sm font-semibold">
        + Tambah Jurnal
    </button>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Referensi</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($journalEntries as $entry)
            <tr id="journal-entriesRow_{{ $entry->id }}">
                <td class="px-4 py-3 text-sm text-gray-900">{{ $entry->entry_date?->format('d/m/Y') }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $entry->reference ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ Str::limit($entry->description ?? '—', 50) }}</td>
                <td class="px-4 py-3 text-right text-sm">
                    <button type="button" onclick="openResourceModal('journalEntryModal', 'journal-entries', 'Jurnal', {{ $entry->id }})" class="text-purple-600 hover:text-purple-800 mr-3">Edit</button>
                    <button type="button" onclick="deleteResource('journal-entries', {{ $entry->id }}, 'Jurnal')" class="text-red-600 hover:text-red-800">Hapus</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                    Belum ada jurnal.
                    <button type="button" onclick="openResourceModal('journalEntryModal', 'journal-entries', 'Jurnal')" class="text-purple-600 hover:text-purple-800 ml-1">Tambah yang pertama</button>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@include('admin.components.modal', ['modalId' => 'journalEntryModal', 'title' => 'Tambah Jurnal Umum'])
@endsection
