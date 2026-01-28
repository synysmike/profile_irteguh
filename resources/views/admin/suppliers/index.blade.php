@extends('admin.layout')

@section('title', 'Kelola Tempat Grosir - Admin')

@section('content')
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Kelola Tempat Grosir</h2>
                <p class="text-gray-600">Tambah, edit, atau hapus daftar tempat grosir</p>
            </div>
            <button onclick="openResourceModal('supplierModal', 'suppliers', 'Tempat Grosir')" class="px-6 py-3 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold">
                + Tambah Tempat Grosir
            </button>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kota</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($suppliers as $supplier)
                    <tr id="supplierRow_{{ $supplier->id }}">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $supplier->name }}</div>
                            @if($supplier->contact_person)
                            <div class="text-sm text-gray-500">CP: {{ $supplier->contact_person }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($supplier->phone)
                            <div class="text-sm text-gray-900">{{ $supplier->phone }}</div>
                            @endif
                            @if($supplier->email)
                            <div class="text-sm text-gray-500">{{ $supplier->email }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $supplier->city ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($supplier->is_active)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                            @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button onclick="openResourceModal('supplierModal', 'suppliers', 'Tempat Grosir', {{ $supplier->id }})" class="text-purple-600 hover:text-purple-900 mr-4">Edit</button>
                            <button onclick="deleteResource('suppliers', {{ $supplier->id }}, 'Tempat Grosir')" class="text-red-600 hover:text-red-900">Hapus</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            Belum ada tempat grosir. <button onclick="openResourceModal('supplierModal', 'suppliers', 'Tempat Grosir')" class="text-purple-600 hover:text-purple-800">Tambah yang pertama</button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        @include('admin.components.modal', [
            'modalId' => 'supplierModal',
            'title' => 'Tambah Tempat Grosir'
        ])
@endsection
