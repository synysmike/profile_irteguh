@extends('admin.keuangan.layout')

@section('title', 'Master Jasa - Keuangan')

@section('keuangan_content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Master Jasa</h2>
        <p class="text-gray-600 mt-1">Kelola katalog jasa dan biaya default untuk surat tugas / project.</p>
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
    <ul class="list-disc list-inside text-red-700 text-sm">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Tambah Jasa</h3>
    <form method="POST" action="{{ route('admin.keuangan.jasa.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
        @csrf
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama jasa (contoh: Instalasi jaringan)" required
               class="md:col-span-2 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        <input type="text" name="code" value="{{ old('code') }}" placeholder="Kode (opsional)"
               class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        <input type="number" name="unit_price" step="0.01" min="0" required placeholder="Biaya (Rp)" value="{{ old('unit_price') }}"
               class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        <label class="flex items-center px-2">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="mr-2">
            Aktif
        </label>
        <textarea name="description" rows="2" placeholder="Deskripsi (opsional)"
                  class="md:col-span-5 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('description') }}</textarea>
        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold">
            Simpan Jasa
        </button>
    </form>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Biaya</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Dipakai</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($jasas as $jasa)
            <tr>
                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                    {{ $jasa->name }}
                    @if($jasa->description)
                    <div class="text-xs text-gray-500 mt-0.5">{{ \Illuminate\Support\Str::limit($jasa->description, 80) }}</div>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $jasa->code ?: '—' }}</td>
                <td class="px-4 py-3 text-sm text-right text-gray-900">Rp {{ number_format((float) $jasa->unit_price, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-sm text-right text-gray-600">{{ $jasa->assignment_letter_items_count ?? 0 }}</td>
                <td class="px-4 py-3 text-sm">
                    <span class="px-2 py-1 text-xs rounded-full {{ $jasa->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                        {{ $jasa->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right text-sm">
                    <details class="inline-block text-left">
                        <summary class="cursor-pointer text-purple-600 hover:text-purple-800">Edit</summary>
                        <form method="POST" action="{{ route('admin.keuangan.jasa.update', $jasa) }}" class="mt-2 p-3 border rounded-md bg-gray-50 w-80">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $jasa->name }}" required class="mb-2 w-full px-3 py-2 border rounded-md">
                            <input type="text" name="code" value="{{ $jasa->code }}" placeholder="Kode" class="mb-2 w-full px-3 py-2 border rounded-md">
                            <input type="number" name="unit_price" step="0.01" min="0" value="{{ $jasa->unit_price }}" required class="mb-2 w-full px-3 py-2 border rounded-md">
                            <textarea name="description" rows="2" class="mb-2 w-full px-3 py-2 border rounded-md">{{ $jasa->description }}</textarea>
                            <label class="flex items-center text-xs mb-2"><input type="checkbox" name="is_active" value="1" {{ $jasa->is_active ? 'checked' : '' }} class="mr-2">Aktif</label>
                            <button type="submit" class="w-full px-3 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">Update</button>
                        </form>
                    </details>
                    <form method="POST" action="{{ route('admin.keuangan.jasa.destroy', $jasa) }}" class="inline-block ml-2" onsubmit="return confirm('Hapus jasa ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada data jasa.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
