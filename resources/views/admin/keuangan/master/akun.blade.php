@extends('admin.keuangan.layout')

@section('title', 'Akun Perkiraan (COA) - Keuangan')

@section('keuangan_content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Akun Perkiraan (Chart of Accounts)</h2>
        <p class="text-gray-600 mt-1">Kas, piutang, hutang, modal, pendapatan, beban</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($accounts as $account)
            <tr>
                <td class="px-4 py-3 text-sm font-mono">{{ $account->code }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ $account->name }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ isset(\App\Models\ChartOfAccount::types()[$account->type]) ? \App\Models\ChartOfAccount::types()[$account->type] : $account->type }}</td>
                <td class="px-4 py-3">
                    @if($account->is_active)
                    <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>
                    @else
                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">Nonaktif</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                    Belum ada akun. Tambah akun perkiraan (kas, piutang, hutang, modal, pendapatan, beban) untuk memulai pembukuan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
    <strong>Fitur CRUD Akun Perkiraan</strong> dapat ditambahkan untuk mengelola kode akun, nama, dan tipe (kas, piutang, hutang, modal, pendapatan, beban).
</div>
@endsection
