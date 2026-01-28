<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Name -->
    <div class="md:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama *</label>
        <input type="text" id="name" name="name" value="{{ old('name', isset($contributor) && $contributor ? $contributor->name : '') }}" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Role -->
    <div class="md:col-span-2">
        <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Peran/Jabatan *</label>
        <input type="text" id="role" name="role" value="{{ old('role', isset($contributor) && $contributor ? $contributor->role : '') }}" required
               placeholder="Contoh: Founder & CEO"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Image URL -->
    <div class="md:col-span-2">
        <label for="image_url" class="block text-sm font-medium text-gray-700 mb-2">URL Foto Profil *</label>
        <input type="url" id="image_url" name="image_url" value="{{ old('image_url', isset($contributor) && $contributor ? $contributor->image_url : '') }}" required
               placeholder="https://example.com/photo.jpg"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Order -->
    <div>
        <label for="order" class="block text-sm font-medium text-gray-700 mb-2">Urutan</label>
        <input type="number" id="order" name="order" value="{{ old('order', isset($contributor) && $contributor ? $contributor->order : 0) }}" min="0"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Is Active -->
    <div>
        <label class="flex items-center mt-8">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', isset($contributor) && $contributor ? $contributor->is_active : true) ? 'checked' : '' }}
                   class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
            <span class="ml-2 text-sm text-gray-700">Aktif</span>
        </label>
    </div>
</div>
