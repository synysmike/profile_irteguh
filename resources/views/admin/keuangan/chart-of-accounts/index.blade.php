@extends('admin.keuangan.layout')

@section('title', 'Akun Perkiraan (COA) - Keuangan')

@section('keuangan_content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Akun Perkiraan (Chart of Accounts)</h2>
        <p class="text-gray-600 mt-1">Kas, piutang, hutang, modal, pendapatan, beban</p>
    </div>
    <a href="{{ route('admin.keuangan.chart-of-accounts.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition text-sm font-semibold">
        + Tambah Akun
    </a>
</div>

@if(session('success'))
<div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">{{ session('error') }}</div>
@endif

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Induk</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Urutan</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($accounts as $account)
            <tr>
                <td class="px-4 py-3 text-sm font-mono">{{ $account->code }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ $account->name }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ \App\Models\ChartOfAccount::types()[$account->type] ?? $account->type }}</td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ $account->parent ? $account->parent->code . ' - ' . $account->parent->name : '—' }}</td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ $account->order }}</td>
                <td class="px-4 py-3">
                    @if($account->is_active)
                    <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>
                    @else
                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">Nonaktif</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.keuangan.chart-of-accounts.show', $account) }}" class="text-gray-500 hover:text-gray-700 mr-2">Lihat</a>
                    <a href="{{ route('admin.keuangan.chart-of-accounts.edit', $account) }}" class="text-purple-600 hover:text-purple-800 mr-2">Edit</a>
                    <form action="{{ route('admin.keuangan.chart-of-accounts.destroy', $account) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus akun ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                    Belum ada akun. <a href="{{ route('admin.keuangan.chart-of-accounts.create') }}" class="text-purple-600 hover:text-purple-800 font-medium">Tambah akun perkiraan</a> untuk memulai pembukuan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
