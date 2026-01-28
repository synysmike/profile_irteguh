@extends('admin.layout')

@section('title', 'Tambah Studi Kasus - Admin')

@section('content')
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('admin.case-studies.index') }}" class="text-purple-600 hover:text-purple-800 mb-4 inline-block">← Kembali</a>
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Tambah Studi Kasus</h2>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <form action="{{ route('admin.case-studies.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Title -->
                    <div class="md:col-span-2">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul *</label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        @error('title')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
                        <select id="category" name="category" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                        @error('category')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Year -->
                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Tahun *</label>
                        <input type="number" id="year" name="year" value="{{ old('year', date('Y')) }}" min="2000" max="{{ date('Y') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        @error('year')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Order -->
                    <div>
                        <label for="order" class="block text-sm font-medium text-gray-700 mb-2">Urutan</label>
                        <input type="number" id="order" name="order" value="{{ old('order', 0) }}" min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <!-- Featured -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="featured" value="1" {{ old('featured') ? 'checked' : '' }}
                                   class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            <span class="ml-2 text-sm text-gray-700">Tandai sebagai Unggulan</span>
                        </label>
                    </div>
                </div>

                <!-- Client Context -->
                <div>
                    <label for="client_context" class="block text-sm font-medium text-gray-700 mb-2">Klien/Konteks</label>
                    <textarea id="client_context" name="client_context" rows="2"
                              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('client_context') }}</textarea>
                </div>

                <!-- Challenge -->
                <div>
                    <label for="challenge" class="block text-sm font-medium text-gray-700 mb-2">Tantangan *</label>
                    <textarea id="challenge" name="challenge" rows="4" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('challenge') }}</textarea>
                    @error('challenge')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Solution -->
                <div>
                    <label for="solution" class="block text-sm font-medium text-gray-700 mb-2">Solusi *</label>
                    <textarea id="solution" name="solution" rows="4" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('solution') }}</textarea>
                    @error('solution')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Outcome -->
                <div>
                    <label for="outcome" class="block text-sm font-medium text-gray-700 mb-2">Hasil *</label>
                    <textarea id="outcome" name="outcome" rows="4" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('outcome') }}</textarea>
                    @error('outcome')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Excerpt -->
                <div>
                    <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-2">Ringkasan</label>
                    <textarea id="excerpt" name="excerpt" rows="2"
                              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('excerpt') }}</textarea>
                </div>

                <!-- Tags -->
                <div>
                    <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">Tags (pisahkan dengan koma)</label>
                    <input type="text" id="tags" name="tags" value="{{ old('tags') }}"
                           placeholder="Contoh: Docker, Nginx, HTTPS"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <!-- Visuals -->
                <div>
                    <label for="visuals" class="block text-sm font-medium text-gray-700 mb-2">URL Gambar (pisahkan dengan koma)</label>
                    <input type="text" id="visuals" name="visuals" value="{{ old('visuals') }}"
                           placeholder="https://example.com/image1.jpg, https://example.com/image2.jpg"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <!-- Submit Buttons -->
                <div class="flex gap-4">
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold">
                        Simpan
                    </button>
                    <a href="{{ route('admin.case-studies.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
@endsection
