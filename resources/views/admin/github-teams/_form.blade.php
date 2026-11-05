@php $team = $team ?? null; @endphp

<div>
    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Tim *</label>
    <input type="text" id="name" name="name" value="{{ old('name', $team?->name) }}" required
           placeholder="Contoh: Tim Backend"
           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
</div>

<div>
    <label for="github_url" class="block text-sm font-medium text-gray-700 mb-2">Link / Username GitHub *</label>
    <input type="text" id="github_url" name="github_url" value="{{ old('github_url', $team?->github_url) }}" required
           placeholder="https://github.com/nama-org atau nama-org"
           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    <p class="text-xs text-gray-500 mt-1">Repo publik dari akun/organisasi ini akan diambil otomatis untuk halaman Portfolio.</p>
    @error('github_url')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">Urutan</label>
        <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $team?->sort_order ?? 0) }}" min="0"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        @error('sort_order')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="flex items-center mt-8">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $team?->is_active ?? true) ? 'checked' : '' }}
                   class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
            <span class="ml-2 text-sm text-gray-700">Tampil di Portfolio</span>
        </label>
    </div>
</div>
