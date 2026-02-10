@extends('admin.layout')

@section('title', 'Teks Hero - Admin')

@section('content')
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Teks Hero Halaman Utama</h2>
                <p class="text-gray-600">Kelola teks yang ditampilkan dengan animasi typing di hero beranda</p>
            </div>
            <button onclick="openResourceModal('heroTextModal', 'hero-texts', 'Teks Hero')" class="px-6 py-3 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold">
                + Tambah Teks
            </button>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Urutan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teks</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($heroTexts as $heroText)
                    <tr id="heroTextRow_{{ $heroText->id }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $heroText->order }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $heroText->text }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($heroText->is_active)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                            @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button onclick="openResourceModal('heroTextModal', 'hero-texts', 'Teks Hero', {{ $heroText->id }})" class="text-purple-600 hover:text-purple-900 mr-4">Edit</button>
                            <button onclick="deleteResource('hero-texts', {{ $heroText->id }}, 'Teks Hero')" class="text-red-600 hover:text-red-900">Hapus</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            Belum ada teks hero. <button onclick="openResourceModal('heroTextModal', 'hero-texts', 'Teks Hero')" class="text-purple-600 hover:text-purple-800">Tambah yang pertama</button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        @include('admin.components.modal', [
            'modalId' => 'heroTextModal',
            'title' => 'Tambah Teks Hero'
        ])
@endsection
