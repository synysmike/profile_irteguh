@php
    $item = $newsItem ?? null;
@endphp

<div class="space-y-6">
    <div>
        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul *</label>
        <input type="text" id="title" name="title" required
               value="{{ old('title', $item->title ?? '') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        @error('title')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">Slug URL</label>
            <input type="text" id="slug" name="slug"
                   value="{{ old('slug', $item->slug ?? '') }}"
                   placeholder="otomatis dari judul jika dikosongkan"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            @error('slug')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="author_name" class="block text-sm font-medium text-gray-700 mb-2">Penulis</label>
            <input type="text" id="author_name" name="author_name"
                   value="{{ old('author_name', $item->author_name ?? 'Tim Editorial') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>
    </div>

    <div>
        <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-2">Ringkasan</label>
        <textarea id="excerpt" name="excerpt" rows="2" maxlength="500"
                  class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('excerpt', $item->excerpt ?? '') }}</textarea>
        <p class="text-xs text-gray-500 mt-1">Ditampilkan di daftar berita (maks. 500 karakter).</p>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Konten *</label>
        <div id="news-editor" class="bg-white rounded-lg border border-gray-300 overflow-hidden"></div>
        <input type="hidden" name="content" id="content" value="{{ old('content', $item->content ?? '') }}">
        @error('content')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        <p class="text-xs text-gray-500 mt-2">Gunakan toolbar untuk format teks, heading, daftar, quote, link, dan gambar.</p>
    </div>

    @php
        $existingCover = $item?->cover_image;
        $existingIsExternal = $existingCover && filter_var($existingCover, FILTER_VALIDATE_URL);
        $coverUrlValue = old('cover_image', $existingIsExternal ? $existingCover : '');
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="cover_image" class="block text-sm font-medium text-gray-700 mb-2">URL Cover</label>
            <input type="text" id="cover_image" name="cover_image"
                   value="{{ $coverUrlValue }}"
                   placeholder="https://... (opsional jika upload file)"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            <p class="text-xs text-gray-500 mt-1">Kosongkan jika memakai upload file. Cover yang sudah ada tidak akan terhapus.</p>
        </div>
        <div>
            <label for="cover_file" class="block text-sm font-medium text-gray-700 mb-2">Upload Cover</label>
            <input type="file" id="cover_file" name="cover_file" accept="image/*"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white">
            @if($item && $item->coverUrl())
            <div class="mt-3 flex items-start gap-3">
                <img src="{{ $item->coverUrl() }}" alt="Cover" class="h-24 rounded-md object-cover border border-gray-200">
                <label class="flex items-center gap-2 text-sm text-gray-600 mt-1">
                    <input type="checkbox" name="remove_cover" value="1" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    Hapus cover
                </label>
            </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
        <div>
            <label for="published_at" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Publikasi</label>
            <input type="datetime-local" id="published_at" name="published_at"
                   value="{{ old('published_at', isset($item) && $item->published_at ? $item->published_at->format('Y-m-d\TH:i') : '') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>
        <div>
            <label class="flex items-center gap-2 py-2">
                <input type="checkbox" name="is_published" value="1"
                       {{ old('is_published', $item->is_published ?? false) ? 'checked' : '' }}
                       class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                <span class="text-sm text-gray-700 font-medium">Publikasikan berita</span>
            </label>
        </div>
    </div>
</div>
