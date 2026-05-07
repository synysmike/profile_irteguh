@extends('admin.keuangan.layout')

@section('title', 'Detail Akun: ' . $account->code . ' - Keuangan')

@section('keuangan_content')
<div class="mb-6">
    <a href="{{ route('admin.keuangan.chart-of-accounts.index') }}" class="text-purple-600 hover:text-purple-800 text-sm font-medium">&larr; Daftar Akun</a>
    <h2 class="text-2xl font-bold text-gray-800 mt-2">{{ $account->code }} - {{ $account->name }}</h2>
    <p class="text-gray-600 mt-1">Detail akun perkiraan</p>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <dl class="divide-y divide-gray-200">
        <div class="px-4 py-3 flex justify-between">
            <dt class="text-sm font-medium text-gray-500">Kode</dt>
            <dd class="text-sm text-gray-900 font-mono">{{ $account->code }}</dd>
        </div>
        <div class="px-4 py-3 flex justify-between">
            <dt class="text-sm font-medium text-gray-500">Nama</dt>
            <dd class="text-sm text-gray-900">{{ $account->name }}</dd>
        </div>
        <div class="px-4 py-3 flex justify-between">
            <dt class="text-sm font-medium text-gray-500">Tipe</dt>
            <dd class="text-sm text-gray-900">{{ \App\Models\ChartOfAccount::types()[$account->type] ?? $account->type }}</dd>
        </div>
        <div class="px-4 py-3 flex justify-between">
            <dt class="text-sm font-medium text-gray-500">Induk</dt>
            <dd class="text-sm text-gray-900">{{ $account->parent ? $account->parent->code . ' - ' . $account->parent->name : '—' }}</dd>
        </div>
        <div class="px-4 py-3 flex justify-between">
            <dt class="text-sm font-medium text-gray-500">Urutan</dt>
            <dd class="text-sm text-gray-900">{{ $account->order }}</dd>
        </div>
        <div class="px-4 py-3 flex justify-between">
            <dt class="text-sm font-medium text-gray-500">Status</dt>
            <dd>
                @if($account->is_active)
                <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>
                @else
                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">Nonaktif</span>
                @endif
            </dd>
        </div>
    </dl>
    @if($account->children->isNotEmpty())
    <div class="px-4 py-3 border-t border-gray-200">
        <dt class="text-sm font-medium text-gray-500 mb-2">Sub-akun</dt>
        <ul class="text-sm text-gray-700 space-y-1">
            @foreach($account->children as $child)
            <li><a href="{{ route('admin.keuangan.chart-of-accounts.show', $child) }}" class="text-purple-600 hover:text-purple-800">{{ $child->code }} - {{ $child->name }}</a></li>
            @endforeach
        </ul>
    </div>
    @endif
</div>

<div class="mt-4 flex gap-3">
    <a href="{{ route('admin.keuangan.chart-of-accounts.edit', $account) }}" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 font-medium">Edit</a>
    <form action="{{ route('admin.keuangan.chart-of-accounts.destroy', $account) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus akun ini?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="px-4 py-2 border border-red-300 text-red-600 rounded-md hover:bg-red-50">Hapus</button>
    </form>
</div>
@endsection
