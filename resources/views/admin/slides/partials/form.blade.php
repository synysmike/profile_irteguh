<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Title -->
    <div class="md:col-span-2">
        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul *</label>
        <input type="text" id="title" name="title" value="{{ old('title', isset($slide) && $slide ? $slide->title : '') }}" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Image URL -->
    <div class="md:col-span-2">
        <label for="image_url" class="block text-sm font-medium text-gray-700 mb-2">URL Gambar *</label>
        <input type="url" id="image_url" name="image_url" value="{{ old('image_url', isset($slide) && $slide ? $slide->image_url : '') }}" required
               placeholder="https://example.com/image.jpg"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Description -->
    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
        <textarea id="description" name="description" rows="3"
                  class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('description', isset($slide) && $slide ? $slide->description : '') }}</textarea>
    </div>

    <!-- Link URL -->
    <div>
        <label for="link_url" class="block text-sm font-medium text-gray-700 mb-2">URL Link</label>
        <input type="url" id="link_url" name="link_url" value="{{ old('link_url', isset($slide) && $slide ? $slide->link_url : '') }}"
               placeholder="https://example.com"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Link Text -->
    <div>
        <label for="link_text" class="block text-sm font-medium text-gray-700 mb-2">Teks Link</label>
        <input type="text" id="link_text" name="link_text" value="{{ old('link_text', isset($slide) && $slide ? $slide->link_text : '') }}"
               placeholder="Klik di sini"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Order -->
    <div>
        <label for="order" class="block text-sm font-medium text-gray-700 mb-2">Urutan</label>
        <input type="number" id="order" name="order" value="{{ old('order', isset($slide) && $slide ? $slide->order : 0) }}" min="0"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Is Active -->
    <div>
        <label class="flex items-center mt-8">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', isset($slide) && $slide ? $slide->is_active : true) ? 'checked' : '' }}
                   class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
            <span class="ml-2 text-sm text-gray-700">Aktif</span>
        </label>
    </div>
</div>
