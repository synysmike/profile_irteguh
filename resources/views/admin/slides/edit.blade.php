@extends('admin.layout')

@section('title', 'Edit Slide - Admin')

@section('content')
        <div class="mb-8">
            <a href="{{ route('admin.slides.index') }}" class="text-purple-600 hover:text-purple-800 mb-4 inline-block">← Kembali</a>
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Edit Slide</h2>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <form action="{{ route('admin.slides.update', $slide->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Title -->
                    <div class="md:col-span-2">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul *</label>
                        <input type="text" id="title" name="title" value="{{ old('title', $slide->title) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        @error('title')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Image URL -->
                    <div class="md:col-span-2">
                        <label for="image_url" class="block text-sm font-medium text-gray-700 mb-2">URL Gambar *</label>
                        <input type="url" id="image_url" name="image_url" value="{{ old('image_url', $slide->image_url) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        @error('image_url')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea id="description" name="description" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('description', $slide->description) }}</textarea>
                    </div>

                    <!-- Link URL -->
                    <div>
                        <label for="link_url" class="block text-sm font-medium text-gray-700 mb-2">URL Link</label>
                        <input type="url" id="link_url" name="link_url" value="{{ old('link_url', $slide->link_url) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <!-- Link Text -->
                    <div>
                        <label for="link_text" class="block text-sm font-medium text-gray-700 mb-2">Teks Link</label>
                        <input type="text" id="link_text" name="link_text" value="{{ old('link_text', $slide->link_text) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <!-- Order -->
                    <div>
                        <label for="order" class="block text-sm font-medium text-gray-700 mb-2">Urutan</label>
                        <input type="number" id="order" name="order" value="{{ old('order', $slide->order) }}" min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <!-- Is Active -->
                    <div>
                        <label class="flex items-center mt-8">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $slide->is_active) ? 'checked' : '' }}
                                   class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            <span class="ml-2 text-sm text-gray-700">Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold">
                        Update
                    </button>
                    <a href="{{ route('admin.slides.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
@endsection
