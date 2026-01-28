@extends('admin.layout')

@section('title', 'Kelola Tim - Admin')

@section('content')
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Kelola Tim</h2>
                <p class="text-gray-600">Tambah, edit, atau hapus anggota tim</p>
            </div>
            <button onclick="openResourceModal('contributorModal', 'contributors', 'Kontributor')" class="px-6 py-3 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold">
                + Tambah Anggota
            </button>
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($contributors as $contributor)
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6" id="contributorRow_{{ $contributor->id }}">
                <div class="text-center mb-4">
                    <img src="{{ $contributor->image_url }}" alt="{{ $contributor->name }}" 
                         class="w-24 h-24 rounded-full mx-auto mb-3 object-cover">
                    <h3 class="font-semibold text-gray-800">{{ $contributor->name }}</h3>
                    <p class="text-sm text-gray-600">{{ $contributor->role }}</p>
                </div>
                <div class="flex items-center justify-between text-sm mb-4">
                    <span class="text-gray-500">Urutan: {{ $contributor->order }}</span>
                    @if($contributor->is_active)
                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Aktif</span>
                    @else
                    <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">Tidak Aktif</span>
                    @endif
                </div>
                <div class="flex gap-2">
                    <button onclick="openResourceModal('contributorModal', 'contributors', 'Kontributor', {{ $contributor->id }})" 
                       class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition text-sm text-center">
                        Edit
                    </button>
                    <button onclick="deleteResource('contributors', {{ $contributor->id }}, 'Kontributor')" 
                       class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition text-sm">
                        Hapus
                    </button>
                </div>
            </div>
            @empty
            <div class="col-span-full bg-white rounded-lg shadow border border-gray-200 p-12 text-center">
                <p class="text-gray-500 mb-4">Belum ada anggota tim.</p>
                <button onclick="openResourceModal('contributorModal', 'contributors', 'Kontributor')" class="text-purple-600 hover:text-purple-800 font-semibold">
                    Tambah yang pertama
                </button>
            </div>
            @endforelse
        </div>

        <!-- Modal -->
        @include('admin.components.modal', [
            'modalId' => 'contributorModal',
            'title' => 'Tambah Kontributor'
        ])
@endsection
