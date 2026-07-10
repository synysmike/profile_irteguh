@extends('admin.keuangan.layout')

@section('title', 'Master Pajak - Keuangan')

@section('keuangan_content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Master Pajak</h2>
        <p class="text-gray-600 mt-1">Kelola daftar pajak yang bisa dipilih saat membuat invoice.</p>
    </div>
</div>

@if(session('error'))
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
    <p class="text-red-700">{{ session('error') }}</p>
</div>
@endif

<div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Tambah Pajak</h3>
    <form method="POST" action="{{ route('admin.keuangan.pajak.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
        @csrf
        <input type="text" name="name" placeholder="Nama pajak (contoh: PPh 23)" required
               class="md:col-span-2 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        <input type="text" name="code" placeholder="Kode (opsional)"
               class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        <input type="number" name="rate" step="0.01" min="0" max="100" required placeholder="Rate %"
               class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        <select name="calculation_type" required class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            <option value="deduction">Potongan (-)</option>
            <option value="addition">Tambahan (+)</option>
        </select>
        <label class="flex items-center px-2">
            <input type="checkbox" name="is_active" value="1" checked class="mr-2">
            Aktif
        </label>
        <textarea name="description" rows="2" placeholder="Deskripsi (opsional)"
                  class="md:col-span-5 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold">
            Simpan Pajak
        </button>
    </form>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Rate</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe Hitung</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Dipakai</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($taxes as $tax)
            <tr>
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $tax->name }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $tax->code ?: '-' }}</td>
                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format($tax->rate, 2, ',', '.') }}%</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $tax->calculation_type === 'deduction' ? 'Potongan (-)' : 'Tambahan (+)' }}</td>
                <td class="px-4 py-3 text-sm text-right text-gray-600">{{ $tax->sales_count + $tax->purchases_count }}</td>
                <td class="px-4 py-3 text-sm">
                    <span class="px-2 py-1 text-xs rounded-full {{ $tax->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                        {{ $tax->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right text-sm">
                    <details class="inline-block text-left">
                        <summary class="cursor-pointer text-purple-600 hover:text-purple-800">Edit</summary>
                        <form method="POST" action="{{ route('admin.keuangan.pajak.update', $tax) }}" class="mt-2 p-3 border rounded-md bg-gray-50 w-80">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $tax->name }}" required class="mb-2 w-full px-3 py-2 border rounded-md">
                            <input type="text" name="code" value="{{ $tax->code }}" placeholder="Kode" class="mb-2 w-full px-3 py-2 border rounded-md">
                            <input type="number" name="rate" step="0.01" min="0" max="100" value="{{ $tax->rate }}" required class="mb-2 w-full px-3 py-2 border rounded-md">
                            <select name="calculation_type" class="mb-2 w-full px-3 py-2 border rounded-md">
                                <option value="deduction" {{ $tax->calculation_type === 'deduction' ? 'selected' : '' }}>Potongan (-)</option>
                                <option value="addition" {{ $tax->calculation_type === 'addition' ? 'selected' : '' }}>Tambahan (+)</option>
                            </select>
                            <textarea name="description" rows="2" class="mb-2 w-full px-3 py-2 border rounded-md">{{ $tax->description }}</textarea>
                            <label class="flex items-center text-xs mb-2"><input type="checkbox" name="is_active" value="1" {{ $tax->is_active ? 'checked' : '' }} class="mr-2">Aktif</label>
                            <button type="submit" class="w-full px-3 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">Update</button>
                        </form>
                    </details>
                    <form method="POST" action="{{ route('admin.keuangan.pajak.destroy', $tax) }}" class="inline-block ml-2" onsubmit="return confirm('Hapus pajak ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada data pajak.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
