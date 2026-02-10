@extends('admin.layout')

@section('title', 'Layanan - Admin')

@section('content')
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Layanan Kami</h2>
                <p class="text-gray-600">Kelola daftar layanan yang ditampilkan di halaman Layanan</p>
            </div>
            <button onclick="openResourceModal('serviceModal', 'services', 'Layanan')" class="px-6 py-3 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold">
                + Tambah Layanan
            </button>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Urutan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Icon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($services as $service)
                    <tr id="servicesRow_{{ $service->id }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $service->order }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-2xl">
                            {{ $service->icon ?: '—' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $service->title }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($service->is_active)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                            @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button onclick="openResourceModal('serviceModal', 'services', 'Layanan', {{ $service->id }})" class="text-purple-600 hover:text-purple-900 mr-4">Edit</button>
                            <button onclick="deleteResource('services', {{ $service->id }}, 'Layanan')" class="text-red-600 hover:text-red-900">Hapus</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            Belum ada layanan. <button onclick="openResourceModal('serviceModal', 'services', 'Layanan')" class="text-purple-600 hover:text-purple-800">Tambah yang pertama</button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        @include('admin.components.modal', [
            'modalId' => 'serviceModal',
            'title' => 'Tambah Layanan'
        ])
@endsection
